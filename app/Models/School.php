<?php

namespace App\Models;

use Sdkconsultoria\Base\Fields\CustomField;
use Sdkconsultoria\Core\Fields\FileField;
use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class School extends BaseModel
{
    protected $fillable = [
        'name',
        'location',
        'type',
        'nivel_educativo',
        'city',
        'logo',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (! isset($model->status) || $model->status === 0) {
                $model->status = self::STATUS_ACTIVE;
            }
        });
    }

    protected function fields()
    {
        return [
            TextField::make('name')->label('Nombre')->rules(['required']),
            TextField::make('location')->label('Localidad')->rules(['required']),
            CustomField::make('type')
                ->rules(['required'])
                ->label('Tipo')
                ->loadOptionsFromUrl('/admin/school-types/api')
                ->setComponent('SelectedField')
                ->addExtra('valueName', 'id'),
            CustomField::make('nivel_educativo')
                ->rules(['required'])
                ->label('Nivel educativo')
                ->loadOptionsFromUrl('/admin/school-niveles/api')
                ->setComponent('SelectedField')
                ->addExtra('valueName', 'id'),
            TextField::make('city')->label('Ciudad')->rules(['required']),
            FileField::make('logo')->setDisk('school/')->label('Logo')->rules(['nullable', 'mimes:jpg,jpeg,png,svg,webp'])->rulesUpdate(['nullable', 'mimes:jpg,jpeg,png,svg,webp'])->searchable(false),
        ];
    }

    public function getTranslations(): array
    {
        return [
            'singular' => 'Escuela',
            'plural' => 'Escuelas',
        ];
    }

    public function uniforms()
    {
        return $this->hasMany(Uniform::class);
    }
}
