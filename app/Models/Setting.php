<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Setting extends BaseModel
{
    protected function fields()
    {
        return[
            TextField::make('name')->label('Identificador')->rules(['required']),
            TextField::make('label')->label('Nombre')->rules(['required']),
            TextField::make('value')->label('Valor')->rules(['required']),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Configuración',
            'plural' => 'Configuración',
        ];
    }
}
