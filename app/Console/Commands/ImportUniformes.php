<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\Uniform;
use App\Models\UniformPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ImportUniformes extends Command
{
    protected $signature = 'app:import-uniformes
                            {--path= : Ruta al .xlsm}
                            {--safe : No procesar imágenes (solo datos textuales)}
                            {--with-images : Procesar imágenes (logo + fotos de uniformes)}
                            {--dry-run : No insertar/actualizar; solo mostrar resumen}
                            {--default-city=Tierra Blanca : Ciudad por defecto}
                            {--default-type=publica : Tipo por defecto (publica/privada)}
                            {--mode=upsert : Política de duplicados (upsert|skip|create)}
                            {--level= : Procesar solo un nivel (kinder|primaria|secundaria|bachillerato|universidad|otro)}';

    protected $description = 'Importa escuelas y uniformes desde el archivo BASE DE DATOS DE UNIFORME.xlsm';

    private array $sheetToLevel = [
        'KINDER' => 'kinder',
        'PRIMARIA' => 'primaria',
        'SECUNDARIA' => 'secundaria',
        'BACHILLERATO' => 'bachillerato',
        'UNIVERSIDAD' => 'universidad',
        'OTROS' => 'otro',
    ];

    private array $uniformTypes = [
        'lunes_gala' => 'LUNES/GALA',
        'diario' => 'DIARIO',
        'edu_fisica' => 'EDUCACION FISICA/DEPORTIVO',
    ];

    private array $stats = [
        'schools_created' => 0,
        'schools_updated' => 0,
        'schools_skipped' => 0,
        'uniforms_created' => 0,
        'uniforms_updated' => 0,
        'images_logo' => 0,
        'images_uniform' => 0,
        'images_failed' => 0,
        'rows_total' => 0,
    ];

    public function handle(): int
    {
        $path = $this->option('path');
        if (! $path) {
            $this->error('Debes indicar la ruta al .xlsm con --path=/ruta/al/archivo.xlsm');
            return self::FAILURE;
        }

        if (! file_exists($path)) {
            $this->error("No existe el archivo: $path");
            return self::FAILURE;
        }

        $useImages = (bool) $this->option('with-images');
        $isSafe = (bool) $this->option('safe');
        if ($isSafe && $useImages) {
            $this->error('No puedes usar --safe y --with-images a la vez.');
            return self::FAILURE;
        }
        if (! $isSafe && ! $useImages) {
            $useImages = false;
        }

        $defaultCity = (string) $this->option('default-city');
        $defaultType = (string) $this->option('default-type');
        $mode = (string) $this->option('mode');
        if (! in_array($mode, ['upsert', 'skip', 'create'], true)) {
            $this->error('mode debe ser: upsert|skip|create');
            return self::FAILURE;
        }
        $levelFilter = $this->option('level');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Cargando archivo: $path");
        $this->info("Modo: " . ($useImages ? 'CON imágenes' : 'SAFE (sin imágenes)'));
        $this->info("Dry-run: " . ($dryRun ? 'SI' : 'NO'));
        $this->info("Duplicados: $mode");
        $this->info("Ciudad default: $defaultCity | Tipo default: $defaultType");
        $this->newLine();

        $spreadsheet = IOFactory::load($path);
        $sheetNames = $spreadsheet->getSheetNames();

        $imageIndex = $useImages ? $this->buildImageIndex($spreadsheet) : ['drawings' => [], 'image_data' => []];

        foreach ($sheetNames as $sheetName) {
            if (! isset($this->sheetToLevel[$sheetName])) {
                continue;
            }
            $level = $this->sheetToLevel[$sheetName];
            if ($levelFilter && $levelFilter !== $level) {
                continue;
            }

            $this->info("=== Hoja: {$sheetName} (nivel={$level}) ===");
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $sheetDrawings = $imageIndex['drawings'][$sheetName] ?? [];
            $sheetImages = $imageIndex['image_data'][$sheetName] ?? [];

            if ($this->isCardLayout($sheet)) {
                $rows = $this->parseCardLayout($sheet, $level, $defaultCity, $defaultType);
            } else {
                $rows = $this->parseListLayout($sheet, $level, $defaultCity, $defaultType);
            }

            foreach ($rows as $row) {
                $this->stats['rows_total']++;
                $school = $this->upsertSchool($row, $mode, $dryRun);
                if ($school === null) {
                    $this->stats['schools_skipped']++;
                    continue;
                }

                if ($school['action'] === 'created') {
                    $this->stats['schools_created']++;
                } else {
                    $this->stats['schools_updated']++;
                }

                if ($useImages && ! empty($row['images'])) {
                    $this->attachSchoolLogo($school['model'], $row['images'], $sheetImages, $dryRun);
                }

                foreach ($row['uniforms'] as $uniform) {
                    $uniform['school_id'] = $school['model']->id;
                    $saved = $this->upsertUniform($uniform, $mode, $dryRun);
                    if ($saved === null) {
                        continue;
                    }
                    if ($saved['action'] === 'created') {
                        $this->stats['uniforms_created']++;
                    } else {
                        $this->stats['uniforms_updated']++;
                    }

                    if ($useImages && ! empty($uniform['images'])) {
                        $this->attachUniformPhotos($saved['model'], $uniform['images'], $sheetImages, $dryRun);
                    }
                }
            }
        }

        $this->newLine();
        $this->info('=== RESUMEN ===');
        $this->table(['Clave', 'Valor'], collect($this->stats)->map(fn($v, $k) => [$k, $v])->values()->toArray());

        return self::SUCCESS;
    }

    private function isCardLayout($sheet): bool
    {
        $v = $sheet->getCell('A7')->getValue();
        return $v !== null && stripos((string) $v, 'escuela') !== false;
    }

    private function parseListLayout($sheet, string $level, string $defaultCity, string $defaultType): array
    {
        $rows = [];
        $highestRow = $sheet->getHighestRow();

        for ($r = 4; $r <= $highestRow; $r++) {
            $pairs = [
                ['B', $r],
                ['I', $r],
            ];
            foreach ($pairs as [$col, $row]) {
                $cell = $sheet->getCell($col . $row);
                $raw = $this->cleanCell($cell->getValue());
                if ($raw === '') {
                    continue;
                }
                if (! $this->looksLikeSchoolName($raw)) {
                    continue;
                }
                $rows[] = [
                    'name' => $raw,
                    'level' => $level,
                    'location' => null,
                    'city' => $defaultCity,
                    'type' => $defaultType,
                    'description' => null,
                    'uniforms' => [],
                    'images' => [],
                    'image_row' => $row,
                ];
            }
        }
        return $rows;
    }

    private function parseCardLayout($sheet, string $level, string $defaultCity, string $defaultType): array
    {
        $rows = [];
        $highestRow = $sheet->getHighestRow();

        for ($r = 7; $r <= $highestRow; $r += 9) {
            $name = $this->cleanCell($sheet->getCell('B' . $r)->getValue());
            if ($name === '' || ! $this->looksLikeSchoolName($name)) {
                continue;
            }
            $location = $this->cleanCell($sheet->getCell('H' . $r)->getValue());
            $colonia = $this->cleanCell($sheet->getCell('L' . $r)->getValue());
            $place = trim($location . ($colonia ? ' / ' . $colonia : ''));

            $imageRow = $r;
            $generatedImageNames = ["logo_{$r}"];
            $generatedUniformImages = [
                'lunes_gala' => $r + 1,
                'diario' => $r + 5,
                'edu_fisica' => $r + 7,
            ];

            $rows[] = [
                'name' => $name,
                'level' => $level,
                'location' => $place !== '' ? $place : $location,
                'city' => $defaultCity,
                'type' => $defaultType,
                'description' => null,
                'uniforms' => $this->buildUniformsFromCard($sheet, $r),
                'images' => $generatedImageNames,
                'image_row' => $imageRow,
                'uniform_images' => $generatedUniformImages,
            ];
        }
        return $rows;
    }

    private function buildUniformsFromCard($sheet, int $baseRow): array
    {
        $uniforms = [];
        $labelCells = ['F9' => 'lunes_gala', 'K9' => 'diario', 'Q9' => 'edu_fisica'];
        $isStart = ($baseRow === 7);
        foreach ($labelCells as $labelCell => $key) {
            $label = $this->cleanCell($sheet->getCell($labelCell)->getValue());
            if ($label === '' || stripos($label, 'LUNES') === false && stripos($label, 'DIARIO') === false && stripos($label, 'EDUCACION') === false && stripos($label, 'DEPORTIVO') === false) {
                continue;
            }
            $labelRow = (int) preg_replace('/[A-Z]+/', '', $labelCell);
            $descRow = $isStart ? ($labelRow + 1) : ($baseRow + $this->offsetRow($key));
            $descCol = substr($labelCell, 0, -1);
            $desc = $this->cleanCell($sheet->getCell($descCol . $descRow)->getValue());
            $uniforms[] = [
                'name' => $this->uniformTypes[$key],
                'description' => $desc,
                'images' => ["{$key}_" . $descRow],
            ];
        }
        return $uniforms;
    }

    private function offsetRow(string $key): int
    {
        return match ($key) {
            'lunes_gala' => 1,
            'diario' => 5,
            'edu_fisica' => 7,
            default => 1,
        };
    }

    private function looksLikeSchoolName(string $value): bool
    {
        $v = trim($value);
        if ($v === '') {
            return false;
        }
        if (strlen($v) < 5) {
            return false;
        }
        if (preg_match('/^\d+\.?\s*-?$/', $v)) {
            return false;
        }
        if (stripos($v, 'escuela') > 30 && stripos($v, 'lugar') > 30) {
            return false;
        }
        return true;
    }

    private function cleanCell($value): string
    {
        if ($value === null) {
            return '';
        }
        $value = (string) $value;
        $value = trim($value);
        return preg_replace('/\s+/', ' ', $value) ?? '';
    }

    private function upsertSchool(array $row, string $mode, bool $dryRun): ?array
    {
        $existing = School::query()
            ->where('name', $row['name'])
            ->where('nivel_educativo', $row['level'])
            ->first();

        if ($existing && $mode === 'skip') {
            return ['model' => $existing, 'action' => 'skipped'];
        }

        if ($dryRun) {
            return [
                'model' => $existing ?? new School(),
                'action' => $existing ? 'updated' : 'created',
            ];
        }

        if ($existing && $mode === 'upsert') {
            $existing->fill([
                'location' => $row['location'] ?? $existing->location,
                'city' => $row['city'] ?? $existing->city,
                'type' => $row['type'] ?? $existing->type,
            ]);
            $existing->save();
            return ['model' => $existing, 'action' => 'updated'];
        }

        $school = School::create([
            'name' => $row['name'],
            'location' => $row['location'] ?? '',
            'type' => $row['type'],
            'nivel_educativo' => $row['level'],
            'city' => $row['city'],
        ]);
        return ['model' => $school, 'action' => 'created'];
    }

    private function upsertUniform(array $data, string $mode, bool $dryRun): ?array
    {
        $existing = Uniform::query()
            ->where('name', $data['name'])
            ->where('school_id', $data['school_id'])
            ->first();

        if ($existing && $mode === 'skip') {
            return ['model' => $existing, 'action' => 'skipped'];
        }

        if ($dryRun) {
            return [
                'model' => $existing ?? new Uniform(),
                'action' => $existing ? 'updated' : 'created',
            ];
        }

        if ($existing && $mode === 'upsert') {
            $existing->description = $data['description'] ?? $existing->description;
            $existing->save();
            return ['model' => $existing, 'action' => 'updated'];
        }

        $uniform = Uniform::create([
            'name' => $data['name'],
            'school_id' => $data['school_id'],
            'description' => $data['description'] ?? null,
        ]);
        return ['model' => $uniform, 'action' => 'created'];
    }

    private function buildImageIndex($spreadsheet): array
    {
        $imageData = [];
        foreach ($spreadsheet->getSheetNames() as $name) {
            $sheet = $spreadsheet->getSheetByName($name);
            $drawings = $sheet->getDrawingCollection();
            foreach ($drawings as $drawing) {
                if (! $drawing instanceof Drawing) {
                    continue;
                }
                $key = $this->drawingKey($drawing);
                $imageData[$key] = [
                    'drawing' => $drawing,
                    'sheet' => $name,
                    'row' => $drawing->getRow(),
                    'col' => $drawing->getColumn(),
                ];
            }
        }

        $drawingsBySheet = [];
        foreach ($spreadsheet->getSheetNames() as $name) {
            $sheet = $spreadsheet->getSheetByName($name);
            $drawingsBySheet[$name] = [];
            foreach ($sheet->getDrawingCollection() as $drawing) {
                if (! $drawing instanceof Drawing) {
                    continue;
                }
                $drawingsBySheet[$name][] = [
                    'drawing' => $drawing,
                    'sheet' => $name,
                    'row' => $drawing->getRow(),
                    'col' => $drawing->getColumn(),
                ];
            }
        }

        return ['drawings' => $drawingsBySheet, 'image_data' => $imageData];
    }

    private function drawingKey(Drawing $drawing): string
    {
        return $drawing->getHashCode() ?: spl_object_hash($drawing);
    }

    private function attachSchoolLogo(School $school, array $imageTokens, array $imageIndex, bool $dryRun): void
    {
        $image = $this->findImageByToken($imageTokens, $imageIndex);
        if ($image === null) {
            return;
        }
        if ($dryRun) {
            $this->stats['images_logo']++;
            return;
        }
        try {
            $url = $this->storeImage($image['drawing'], "school/" . $school->id, 'logo');
            $school->logo = $url;
            $school->save();
            $this->stats['images_logo']++;
        } catch (\Throwable $e) {
            $this->stats['images_failed']++;
            $this->warn("Logo no guardado para {$school->name}: " . $e->getMessage());
        }
    }

    private function attachUniformPhotos(Uniform $uniform, array $imageTokens, array $imageIndex, bool $dryRun): void
    {
        foreach ($imageTokens as $token) {
            $image = $this->findImageByToken([$token], $imageIndex);
            if ($image === null) {
                continue;
            }
            if ($dryRun) {
                $this->stats['images_uniform']++;
                continue;
            }
            try {
                $photo = new UniformPhoto();
                $photo->uniform_id = $uniform->id;
                $photo->order = ($uniform->photos()->max('order') ?? 0) + 1;
                $photo->status = UniformPhoto::STATUS_ACTIVE;
                $photo->save();

                $url = $this->storeImage($image['drawing'], "uniform/" . $uniform->id, (string) $photo->id);
                $photo->photo = $url;
                $photo->save();
                $this->stats['images_uniform']++;
            } catch (\Throwable $e) {
                $this->stats['images_failed']++;
                $this->warn("Foto no guardada para uniforme {$uniform->id}: " . $e->getMessage());
            }
        }
    }

    private function findImageByToken(array $tokens, array $imageIndex): ?array
    {
        $imageData = $imageIndex['image_data'] ?? [];
        foreach ($tokens as $token) {
            if (isset($imageData[$token])) {
                return $imageData[$token];
            }
            $matches = array_filter($imageData, fn($img) => str_contains($img['sheet'], $token) || str_contains((string) $img['row'], $token));
            if (! empty($matches)) {
                return reset($matches) ?: null;
            }
        }
        return null;
    }

    private function storeImage(Drawing $drawing, string $folder, string $name): string
    {
        $extension = $this->normalizeExtension($drawing->getExtension() ?: 'png');
        $relPath = $folder . '/' . $name . '.' . $extension;
        $contents = $this->readDrawingContents($drawing);
        Storage::disk('public')->put($relPath, $contents);
        return Storage::disk('public')->url($relPath);
    }

    private function normalizeExtension(string $ext): string
    {
        $ext = strtolower($ext);
        return match ($ext) {
            'jpg', 'jpeg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            default => 'png',
        };
    }

    private function readDrawingContents(Drawing $drawing): string
    {
        $path = $drawing->getPath();
        if ($path && file_exists($path)) {
            return file_get_contents($path);
        }
        if (method_exists($drawing, 'getImageData')) {
            $data = $drawing->getImageData();
            if (is_resource($data)) {
                return stream_get_contents($data);
            }
            if (is_string($data)) {
                return $data;
            }
        }
        $contents = $drawing->getContents();
        if (is_string($contents)) {
            return $contents;
        }
        throw new \RuntimeException('No se pudo leer el contenido de la imagen.');
    }
}
