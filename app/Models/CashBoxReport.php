<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class CashBoxReport extends BaseModel
{
    public const STATUS_OPEN = 50;
    public const STATUS_CLOSE = 60;

    protected function fields()
    {
        return[
            TextField::make('')->label('')->rules(['required']),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Corte de Caja',
            'plural' => 'Corte de Caja',
        ];
    }
}
