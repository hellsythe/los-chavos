<?php

namespace App\Models;

use App\Fields\TypeaheadFormField;
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
            TypeaheadFormField::make('school_id')
                ->rules(['required'])
                ->label('Escuela')
                ->loadOptionsFromUrl('/admin/school/api?name={search}&page=1')
                ->addExtra('valueName', 'id'),
            TextField::make('description')->label('Descripción')->rules(['nullable'])->searchable(true),
            FileField::make('preview')->setDisk('uniform/')->label('Imagen principal')->rules(['nullable', 'mimes:jpg,jpeg,png,webp'])->rulesUpdate(['nullable', 'mimes:jpg,jpeg,png,webp'])->searchable(false),
        ];
    }

    public function getTranslations(): array
    {
        return [
            'singular' => 'Uniforme',
            'plural' => 'Uniformes',
            'name' => 'Nombre',
            'description' => 'Descripción',
            'school_name' => 'Nombre de escuela',
            'school_location' => 'Ubicación de escuela',
        ];
    }

    public function getIndexFields()
    {
        return ['name', 'school_name', 'description'];
    }

    public function getParseSearchFilters()
    {
        return [
            ['field' => 'name'],
            ['field' => 'description'],
            ['field' => 'school_name'],
            ['field' => 'school_location'],
        ];
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
