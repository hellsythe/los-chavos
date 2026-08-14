<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Uniform;
use App\Models\UniformPhoto;

class UniformCatalogController extends Controller
{
    public function index()
    {
        return view('kiosk.shell');
    }

    public function schools()
    {
        $schools = School::query()
            ->where('status', School::STATUS_ACTIVE)
            ->withCount(['uniforms' => function ($builder) {
                $builder->where('status', Uniform::STATUS_ACTIVE);
            }])
            ->orderBy('nivel_educativo')
            ->orderBy('name')
            ->get(['id', 'name', 'location', 'colonia', 'city', 'type', 'nivel_educativo', 'logo']);

        return response()->json([
            'data' => $schools->map(function ($school) {
                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'location' => $school->location,
                    'colonia' => $school->colonia,
                    'city' => $school->city,
                    'type' => $school->type,
                    'type_label' => $school->type === 'privada' ? 'Privada' : 'Pública',
                    'nivel_educativo' => $school->nivel_educativo,
                    'nivel_educativo_label' => $this->nivelLabel($school->nivel_educativo),
                    'logo' => $school->logo,
                    'uniforms_count' => (int) $school->uniforms_count,
                ];
            })->values(),
        ]);
    }

    public function school($id)
    {
        $school = School::query()
            ->where('status', School::STATUS_ACTIVE)
            ->findOrFail($id);

        $uniforms = $school->uniforms()
            ->where('status', Uniform::STATUS_ACTIVE)
            ->with(['photos'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => [
                'id' => $school->id,
                'name' => $school->name,
                'location' => $school->location,
                'colonia' => $school->colonia,
                'city' => $school->city,
                'type' => $school->type,
                'type_label' => $school->type === 'privada' ? 'Privada' : 'Pública',
                'nivel_educativo' => $school->nivel_educativo,
                'nivel_educativo_label' => $this->nivelLabel($school->nivel_educativo),
                'logo' => $school->logo,
                'uniforms' => $uniforms->map(function ($uniform) {
                    return $this->serializeUniform($uniform);
                })->values(),
            ],
        ]);
    }

    public function uniform($id)
    {
        $uniform = Uniform::query()
            ->where('status', Uniform::STATUS_ACTIVE)
            ->with(['school', 'photos'])
            ->findOrFail($id);

        return response()->json([
            'data' => $this->serializeUniform($uniform, true),
        ]);
    }

    private function serializeUniform(Uniform $uniform, bool $withSchool = false): array
    {
        $photos = $uniform->photos
            ->where('status', UniformPhoto::STATUS_ACTIVE)
            ->sortBy('order')
            ->values()
            ->map(fn ($photo) => [
                'id' => $photo->id,
                'url' => $photo->photo,
                'order' => (int) $photo->order,
            ])
            ->all();

        $data = [
            'id' => $uniform->id,
            'name' => $uniform->name,
            'description' => $uniform->description,
            'preview' => $uniform->preview,
            'photos' => $photos,
            'cover' => $uniform->preview
                ?: ($photos[0]['url'] ?? null),
        ];

        if ($withSchool && $uniform->school) {
            $data['school'] = [
                'id' => $uniform->school->id,
                'name' => $uniform->school->name,
                'location' => $uniform->school->location,
                'colonia' => $uniform->school->colonia,
                'city' => $uniform->school->city,
                'logo' => $uniform->school->logo,
            ];
        }

        return $data;
    }

    private function nivelLabel(?string $nivel): ?string
    {
        $labels = [
            'kinder' => 'Kinder',
            'primaria' => 'Primaria',
            'secundaria' => 'Secundaria',
            'bachillerato' => 'Bachillerato',
            'universidad' => 'Universidad',
            'otro' => 'Otro',
        ];
        return $labels[$nivel] ?? null;
    }
}
