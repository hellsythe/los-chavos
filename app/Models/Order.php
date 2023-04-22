<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends BaseModel
{
    public const STATUS_MISSING_PAYMENT = 40;

    public const STATUS_WAITING_ORDER = 50;

    public const STATUS_ORDER_ARRIVED = 60;

    public const STATUS_PENDING = 70;

    public const STATUS_READY = 80;

    public const STATUS_FINISH = 90;

    protected function fields()
    {
        return [
            TextField::make('id')->label('Folio')->rules(['required']),
        ];
    }

    public function getTranslations(): array
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

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getStatusAttribute($value)
    {
        switch ($value) {
            case self::STATUS_MISSING_PAYMENT:
                return 'Pago pendiente';
                break;
            case self::STATUS_WAITING_ORDER:
                return 'Esperando el pedido';
                break;
            case self::STATUS_ORDER_ARRIVED:
                return 'Pedido LLego';
                break;
            case self::STATUS_PENDING:
                return 'Pendiente';
                break;
            case self::STATUS_READY:
                return 'Listo';
                break;
            case self::STATUS_FINISH:
                return 'Entregado';
                break;
        }
    }
}
