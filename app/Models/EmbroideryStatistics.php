<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class EmbroideryStatistics extends BaseModel
{
    protected function fields()
    {
        return[
            TextField::make('date')->label('Fecha')->rules(['required']),
            TextField::make('orders')->label('Total de ordenes')->rules(['required']),
            TextField::make('services')->label('Servicios')->rules(['required']),
            TextField::make('embroderies')->label('Total de prendas')->rules(['required']),
            TextField::make('minutes')->label('Puntadas')->rules(['required']),

        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Estadisticas de bordado',
            'plural' => 'Estadisticas de bordado',
        ];
    }
}
