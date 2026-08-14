<?php

namespace App\Models;

use Sdkconsultoria\Base\Fields\CustomField;
use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Uniform extends BaseModel
{
    protected $fillable = [
        'name',
        'school_id',
        'description',
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
            CustomField::make('school_id')
                ->rules(['required'])
                ->label('Escuela')
                ->loadOptionsFromUrl('/admin/school/api')
                ->setComponent('SelectedField')
                ->addExtra('valueName', 'id'),
            TextField::make('description')->label('Descripción')->rules(['nullable']),
        ];
    }

    public function getTranslations(): array
    {
        return [
            'singular' => 'Uniforme',
            'plural' => 'Uniformes',
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
