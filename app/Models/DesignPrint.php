<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\FileField;
use Sdkconsultoria\Core\Fields\NumericField;
use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class DesignPrint extends BaseModel
{
    protected function fields()
    {
        return[
            TextField::make('name')->label('Nombre')->rules(['required']),
            NumericField::make('price')->rules(['nullable'])->label('Precio'),
            FileField::make('media')->setDisk('design-print/')->label('Diseño')->rules(['required','mimes:jpg,png'])->rulesUpdate(['mimes:jpg,png'])->searchable(false),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Diseño Estampado',
            'plural' => 'Diseños Estampado',
        ];
    }
}
