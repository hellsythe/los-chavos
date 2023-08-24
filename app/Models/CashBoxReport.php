<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;
use Date;

class CashBoxReport extends BaseModel
{
    public const STATUS_OPEN = 50;
    public const STATUS_CLOSE = 60;
    public const STATUS_WAITING_AUTH = 70;
    public const STATUS_INVALID = 80;

    protected function fields()
    {
        return[
            TextField::make('start')->label('Fecha')->rules(['required']),
            TextField::make('type')->label('Tipo de corte')->rules(['required']),
            TextField::make('calculate_cash')->label('Efectivo Calculado')->rules(['required']),
            TextField::make('real_cash')->label('Efectivo Contado')->rules(['required']),
            TextField::make('missing_cash')->label('Efectivo Faltante')->rules(['required']),
            TextField::make('calculate_card')->label('Tarjeta Calculado')->rules(['required']),
            TextField::make('real_card')->label('Targeta Contado')->rules(['required']),
            TextField::make('missing_card')->label('Tarjeta Faltante')->rules(['required']),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Corte de Caja',
            'plural' => 'Corte de Caja',
        ];
    }

    public function getStartAttribute($value): string
    {
        return  Date::createFromDate($value)->format('l j F Y');
    }
}
