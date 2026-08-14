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
                            {--level= : Procesar solo un nivel (kinder|primaria|secundaria|bachillerato|universidad|otro)}
                            {--clean-images : Elimina todas las imágenes existentes (uniformes y logos) antes de importar}';

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
        $cleanImages = (bool) $this->option('clean-images');

        $this->info("Cargando archivo: $path");
        $this->info("Modo: " . ($useImages ? 'CON imágenes' : 'SAFE (sin imágenes)'));
        $this->info("Dry-run: " . ($dryRun ? 'SI' : 'NO'));
        $this->info("Duplicados: $mode");
        $this->info("Clean images: " . ($cleanImages ? 'SI' : 'NO'));
        $this->info("Ciudad default: $defaultCity | Tipo default: $defaultType");
        $this->newLine();

        if ($cleanImages && ! $dryRun) {
            $this->cleanExistingImages();
        }

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

            if ($this->hasCardAnchors($sheet)) {
                $rows = $this->parseCardLayout($sheet, $level, $defaultCity, $defaultType);
            } else {
                $rows = $this->parseListLayout($sheet, $level, $defaultCity, $defaultType);
            }

            $rowCount = count($rows);
            foreach ($rows as $idx => $row) {
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

                if ($useImages && $row['image_row'] !== null) {
                    $headerRow = (int) $row['image_row'];
                    $nextHeaderRow = isset($rows[$idx + 1]['image_row']) ? (int) $rows[$idx + 1]['image_row'] : null;
                    $cards = $this->splitCardsImages($imageIndex, $sheetName, $headerRow, $nextHeaderRow);

                    if ($cards['logo'] !== null) {
                        $this->attachSchoolLogo($school['model'], (int) $cards['logo']['top_row'], $imageIndex, $sheetName, $dryRun);
                    }

                    foreach ($row['uniforms'] as $uniformIndex => $uniform) {
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

                        if ($useImages && isset($cards['uniforms'][$uniformIndex])) {
                            $this->attachUniformPhotos($saved['model'], $cards['uniforms'][$uniformIndex], $dryRun);
                        }
                    }
                    continue;
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

    private function hasCardAnchors($sheet): bool
    {
        $highestRow = $sheet->getHighestRow();
        foreach ($sheet->getRowIterator(1, $highestRow) as $rowObj) {
            $a = (string) $sheet->getCell('A' . $rowObj->getRowIndex())->getValue();
            if (stripos($this->cleanCell($a), 'escuela') !== false) {
                return true;
            }
            $f = (string) $sheet->getCell('F' . $rowObj->getRowIndex())->getValue();
            if (stripos($this->cleanCell($f), 'lunes') !== false) {
                return true;
            }
        }
        return false;
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

        $headerRows = $this->findHeaderRows($sheet, $highestRow);

        foreach ($headerRows as $headerRow) {
            $name = $this->cleanCell($sheet->getCell('B' . $headerRow)->getValue());
            if ($name === '' || ! $this->looksLikeSchoolName($name)) {
                continue;
            }
            $location = $this->cleanCell($sheet->getCell('H' . $headerRow)->getValue());
            $colonia = $this->cleanCell($sheet->getCell('L' . $headerRow)->getValue());

            $labelRow = $this->findLabelRow($sheet, $headerRow, $highestRow);
            $descRow = $labelRow !== null ? $labelRow + 1 : null;

            $rows[] = [
                'name' => $name,
                'level' => $level,
                'location' => $location,
                'colonia' => $colonia,
                'city' => $defaultCity,
                'type' => $defaultType,
                'description' => null,
                'uniforms' => $labelRow !== null ? $this->buildUniformsFromCard($sheet, $labelRow, $descRow) : [],
                'images' => [],
                'image_row' => $headerRow,
                'uniform_images' => [],
            ];
        }
        return $rows;
    }

    private function findHeaderRows($sheet, int $highestRow): array
    {
        $rows = [];
        foreach ($sheet->getRowIterator(1, $highestRow) as $rowObj) {
            $r = $rowObj->getRowIndex();
            $a = $this->cleanCell($sheet->getCell('A' . $r)->getValue());
            if (stripos($a, 'escuela') !== false) {
                $name = $this->cleanCell($sheet->getCell('B' . $r)->getValue());
                if ($name !== '') {
                    $rows[] = $r;
                }
            }
        }
        return $rows;
    }

    private function findLabelRow($sheet, int $headerRow, int $highestRow): ?int
    {
        $limit = min($highestRow, $headerRow + 12);
        for ($r = $headerRow + 1; $r <= $limit; $r++) {
            $f = $this->cleanCell($sheet->getCell('F' . $r)->getValue());
            if (stripos($f, 'lunes') !== false) {
                return $r;
            }
        }
        return null;
    }

    private function buildUniformsFromCard($sheet, int $labelRow, ?int $descRow): array
    {
        $uniforms = [];
        $cells = [
            'F' => 'lunes_gala',
            'K' => 'diario',
            'Q' => 'edu_fisica',
        ];
        foreach ($cells as $col => $key) {
            $label = $this->cleanCell($sheet->getCell($col . $labelRow)->getValue());
            if (stripos($label, 'lunes') === false && stripos($label, 'diario') === false && stripos($label, 'educacion') === false && stripos($label, 'deportivo') === false) {
                continue;
            }
            $desc = $descRow !== null ? $this->cleanCell($sheet->getCell($col . $descRow)->getValue()) : '';
            $next = $descRow !== null ? $this->cleanCell($sheet->getCell($this->nextCol($col) . $descRow)->getValue()) : '';
            if ($next !== '') {
                $desc = trim($desc . "\n" . $next);
            }
            if ($desc === '') {
                continue;
            }
            $uniforms[] = [
                'name' => $this->uniformTypes[$key],
                'description' => $desc,
                'images' => [],
                'desc_row' => $descRow,
                'label_col' => $col,
                'image_row' => $descRow !== null ? $descRow + 1 : null,
            ];
        }
        return $uniforms;
    }

    private function nextCol(string $col): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $idx = strpos($alphabet, $col);
        return $idx !== false && $idx < 25 ? $alphabet[$idx + 1] : $col;
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
                'colonia' => $row['colonia'] ?? $existing->colonia,
                'city' => $row['city'] ?? $existing->city,
                'type' => $row['type'] ?? $existing->type,
            ]);
            $existing->save();
            return ['model' => $existing, 'action' => 'updated'];
        }

        $school = School::create([
            'name' => $row['name'],
            'location' => $row['location'] ?? '',
            'colonia' => $row['colonia'] ?? null,
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
        $drawingsBySheet = [];
        foreach ($spreadsheet->getSheetNames() as $name) {
            $sheet = $spreadsheet->getSheetByName($name);
            $drawings = $sheet->getDrawingCollection();
            $drawingsBySheet[$name] = [];
            $imageData[$name] = [];
            foreach ($drawings as $drawing) {
                if (! $drawing instanceof Drawing) {
                    continue;
                }
                $coords = $this->parseCoordinates($drawing->getCoordinates() ?? '');
                $anchor = $this->parseCoordinates($drawing->getCoordinates2() ?? $drawing->getCoordinates() ?? '');
                $entry = [
                    'drawing' => $drawing,
                    'sheet' => $name,
                    'top_row' => $coords['row'],
                    'top_col' => $coords['col'],
                    'bottom_row' => $anchor['row'],
                    'bottom_col' => $anchor['col'],
                ];
                $key = $this->drawingKey($drawing);
                $imageData[$name][$key] = $entry;
                $drawingsBySheet[$name][] = $entry;
            }
        }
        return ['drawings' => $drawingsBySheet, 'image_data' => $imageData];
    }

    private function parseCoordinates(string $coord): array
    {
        $coord = strtoupper(trim($coord));
        if ($coord === '') {
            return ['row' => null, 'col' => null];
        }
        preg_match('/^([A-Z]+)(\d+)$/', $coord, $m);
        if (! $m) {
            return ['row' => null, 'col' => null];
        }
        $col = 0;
        foreach (str_split($m[1]) as $ch) {
            $col = $col * 26 + (ord($ch) - 64);
        }
        return ['row' => (int) $m[2], 'col' => $col];
    }

    private function drawingKey(Drawing $drawing): string
    {
        return $drawing->getHashCode() ?: spl_object_hash($drawing);
    }

    private function cleanExistingImages(): void
    {
        $this->info('Limpiando imágenes existentes...');

        $deletedFiles = 0;
        foreach (UniformPhoto::all() as $photo) {
            $url = $photo->photo;
            if ($url && strpos($url, '/storage/') !== false) {
                $relative = substr($url, strpos($url, '/storage/') + strlen('/storage/'));
                if (Storage::disk('public')->exists($relative)) {
                    Storage::disk('public')->delete($relative);
                    $deletedFiles++;
                }
            }
        }
        UniformPhoto::query()->delete();
        $this->info("  UniformPhoto eliminadas: " . UniformPhoto::count() . " (archivos borrados: $deletedFiles)");

        $deletedFiles = 0;
        foreach (School::all() as $school) {
            if ($school->logo) {
                $url = $school->logo;
                if (strpos($url, '/storage/') !== false) {
                    $relative = substr($url, strpos($url, '/storage/') + strlen('/storage/'));
                    if (Storage::disk('public')->exists($relative)) {
                        Storage::disk('public')->delete($relative);
                        $deletedFiles++;
                    }
                }
            }
        }
        School::query()->update(['logo' => null]);
        $this->info("  Logos eliminados de schools (archivos borrados: $deletedFiles)");
    }

    private function attachSchoolLogo(School $school, int $imageRow, array $imageIndex, string $sheetName, bool $dryRun): void
    {
        $image = $this->findImageAtCell($imageIndex, $sheetName, $imageRow, 1);
        if ($image === null) {
            return;
        }
        if ($dryRun) {
            $this->stats['images_logo']++;
            return;
        }
        try {
            $sourceKey = $this->drawingSourceKey($image['drawing']);
            if ($sourceKey !== null && $school->logo) {
                $stored = $this->logoSourceKeys[$school->id] ?? $this->loadLogoSourceKey($school);
                if ($stored === $sourceKey) {
                    return;
                }
            }
            $url = $this->storeImage($image['drawing'], "school/" . $school->id, 'logo');
            $school->logo = $url;
            if ($sourceKey !== null) {
                $this->logoSourceKeys[$school->id] = $sourceKey;
            }
            $school->save();
            $this->stats['images_logo']++;
        } catch (\Throwable $e) {
            $this->stats['images_failed']++;
            $this->warn("Logo no guardado para {$school->name}: " . $e->getMessage());
        }
    }

    private function loadLogoSourceKey(School $school): ?string
    {
        $stored = $school->logo;
        if ($stored === null) {
            return null;
        }
        $file = basename(dirname($stored)) . '/' . basename($stored);
        return 'logo:' . $file;
    }

    private function attachUniformPhotos(Uniform $uniform, array $image, bool $dryRun): void
    {
        $sourceKey = $this->drawingSourceKey($image['drawing']);

        if ($dryRun) {
            $exists = $sourceKey !== null
                ? UniformPhoto::where('uniform_id', $uniform->id)->where('source_key', $sourceKey)->exists()
                : false;
            if (! $exists) {
                $this->stats['images_uniform']++;
            }
            return;
        }

        if ($sourceKey !== null) {
            $existing = UniformPhoto::where('uniform_id', $uniform->id)
                ->where('source_key', $sourceKey)
                ->first();
            if ($existing !== null) {
                return;
            }
        }

        try {
            $photo = new UniformPhoto();
            $photo->uniform_id = $uniform->id;
            $photo->order = ($uniform->photos()->max('order') ?? 0) + 1;
            $photo->status = UniformPhoto::STATUS_ACTIVE;
            if ($sourceKey !== null) {
                $photo->source_key = $sourceKey;
            }
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

    private function drawingSourceKey($drawing): ?string
    {
        if ($drawing === null) {
            return null;
        }
        if (method_exists($drawing, 'getHashCode') && $drawing->getHashCode()) {
            return 'hash:' . $drawing->getHashCode();
        }
        $path = $drawing->getPath() ?? '';
        if ($path !== '') {
            if (str_starts_with($path, 'zip://')) {
                $parts = explode('#', $path, 2);
                if (count($parts) === 2) {
                    return 'zip:' . $parts[1];
                }
            }
            return 'file:' . $path;
        }
        return null;
    }

    private function findImageAtCell(array $imageIndex, ?string $sheetName, int $row, int $colIndex): ?array
    {
        $imageData = $imageIndex['image_data'] ?? [];
        $candidates = [];
        if ($sheetName !== null && isset($imageData[$sheetName])) {
            $candidates = $imageData[$sheetName];
        } elseif ($sheetName === null) {
            foreach ($imageData as $items) {
                foreach ($items as $it) {
                    $candidates[] = $it;
                }
            }
        }
        foreach ($candidates as $entry) {
            $top = $entry['top_row'] ?? null;
            $bottom = $entry['bottom_row'] ?? $top;
            $topCol = $entry['top_col'] ?? null;
            $bottomCol = $entry['bottom_col'] ?? $topCol;
            if ($top === null || $topCol === null) {
                continue;
            }
            if ($top <= $row && $bottom >= $row && $topCol <= $colIndex && $bottomCol >= $colIndex) {
                return $entry;
            }
        }
        return null;
    }

    private function splitCardsImages(array $imageIndex, string $sheetName, int $headerRow, ?int $nextHeaderRow = null): array
    {
        $imageData = $imageIndex['image_data'][$sheetName] ?? [];
        $cardStart = $headerRow;
        $cardEnd = $nextHeaderRow !== null ? $nextHeaderRow - 1 : $headerRow + 6;

        $logo = null;
        $uniforms = [];
        foreach ($imageData as $entry) {
            $top = $entry['top_row'] ?? null;
            $bottom = $entry['bottom_row'] ?? $top;
            $topCol = $entry['top_col'] ?? null;
            if ($top === null || $topCol === null) {
                continue;
            }
            if (! ($top <= $cardEnd && $bottom >= $cardStart)) {
                continue;
            }
            if ($topCol === 1) {
                $logo = $entry;
            } else {
                $uniforms[] = $entry;
            }
        }

        usort($uniforms, function ($a, $b) {
            $rowDiff = ($a['top_row'] ?? 0) - ($b['top_row'] ?? 0);
            if ($rowDiff !== 0) {
                return $rowDiff;
            }
            return ($a['top_col'] ?? 0) - ($b['top_col'] ?? 0);
        });

        return ['logo' => $logo, 'uniforms' => $uniforms];
    }

    private function columnIndex(string $col): int
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $idx = strpos($alphabet, strtoupper($col));
        return $idx === false ? 0 : $idx + 1;
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
        if ($path && file_exists($path) && is_readable($path)) {
            return file_get_contents($path);
        }
        if (str_starts_with((string) $path, 'zip://')) {
            $inner = substr($path, strlen('zip://'));
            $parts = explode('#', $inner, 2);
            if (count($parts) === 2) {
                [$zipPath, $entry] = $parts;
                $zip = new \ZipArchive();
                if ($zip->open($zipPath) === true) {
                    $contents = $zip->getFromName($entry);
                    $zip->close();
                    if ($contents !== false) {
                        return $contents;
                    }
                }
            }
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
        throw new \RuntimeException('No se pudo leer el contenido de la imagen (path: ' . $path . ').');
    }
}
