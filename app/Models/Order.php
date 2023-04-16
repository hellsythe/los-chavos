<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Order extends BaseModel
{
    public const STATUS_MISSING_PAYMENT = 40;

    public const STATUS_PENDING = 50;

    public const STATUS_READY = 60;

    public const STATUS_FINISH = 70;

    protected function fields()
    {
        return[
            TextField::make('')->label('')->rules(['required']),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Pedido',
            'plural' => 'Pedidos',
        ];
    }
}
