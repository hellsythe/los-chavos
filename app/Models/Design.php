<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Fields\FileField;
use Sdkconsultoria\Core\Fields\NumericField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Design extends BaseModel
{
    protected function fields()
    {
        return[
            TextField::make('name')->label('Nombre')->rules(['required']),
            NumericField::make('price')->rules(['nullable'])->label('Precio'),
            NumericField::make('minutes')->label('Puntadas')->rules(['required'])
                ->tooltip('Cantidad de tiempo en minutos que tarda la máquina en bordar este diseño'),
            FileField::make('media')->setDisk('design/')->label('Diseño')->rules(['required','mimes:jpg,pdf,png'])->rulesUpdate(['mimes:jpg,pdf,png'])->searchable(false),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Diseño Bordado',
            'plural' => 'Diseños Bordado',
        ];
    }
}
