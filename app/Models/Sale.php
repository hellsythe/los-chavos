<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Sale extends BaseModel
{
    protected function fields()
    {
        return[
            TextField::make('')->label('')->rules(['required']),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Venta',
            'plural' => 'Ventas',
        ];
    }
}
