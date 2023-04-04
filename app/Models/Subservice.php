<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Subservice extends BaseModel
{
    protected function fields()
    {
        return[
            TextField::make('name')->label('Nombre')->rules(['required']),
            TextField::make('service_id')->label('Servicio')->rules(['required']),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Subservicio',
            'plural' => 'Subservicios',
        ];
    }
}
