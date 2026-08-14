<?php

namespace App\Models;

use Sdkconsultoria\Base\Fields\CustomField;
use Sdkconsultoria\Core\Fields\FileField;
use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Uniform extends BaseModel
{
    protected $fillable = [
        'name',
        'school_id',
        'description',
        'preview',
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
            TextField::make('name')->label('Nombre')->rules(['required'])->searchable(true),
            CustomField::make('school_id')
                ->rules(['required'])
                ->label('Escuela')
                ->loadOptionsFromUrl('/admin/school/api')
                ->setComponent('SelectedField')
                ->addExtra('valueName', 'id'),
            TextField::make('description')->label('Descripción')->rules(['nullable'])->searchable(true),
            FileField::make('preview')->setDisk('uniform/')->label('Imagen principal')->rules(['nullable', 'mimes:jpg,jpeg,png,webp'])->rulesUpdate(['nullable', 'mimes:jpg,jpeg,png,webp'])->searchable(false),
            CustomField::make('school_name')
                ->rules(['nullable'])
                ->label('Escuela')
                ->canBeSaved(false)
                ->searchable(true),
            CustomField::make('school_location')
                ->rules(['nullable'])
                ->label('Localidad')
                ->canBeSaved(false)
                ->searchable(true),
        ];
    }

    public function getTranslations(): array
    {
        return [
            'singular' => 'Uniforme',
            'plural' => 'Uniformes',
        ];
    }

    public function getIndexFields()
    {
        return ['name', 'school_name', 'description'];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function photos()
    {
        return $this->hasMany(UniformPhoto::class)->orderBy('order');
    }
}
