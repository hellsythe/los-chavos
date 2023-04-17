<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends BaseModel
{
    public const STATUS_MISSING_PAYMENT = 40;

    public const STATUS_PENDING = 50;

    public const STATUS_READY = 60;

    public const STATUS_FINISH = 70;

    protected function fields()
    {
        return[
            TextField::make('id')->label('Folio')->rules(['required']),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Pedido',
            'plural' => 'Pedidos',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
