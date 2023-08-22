<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Date;

class Order extends BaseModel
{
    public const STATUS_MISSING_PAYMENT = 40;

    public const STATUS_WAITING_AUTH = 45;

    public const STATUS_WAITING_ORDER = 50;

    public const STATUS_ORDER_ARRIVED = 60;

    public const STATUS_PENDING = 70;

    public const STATUS_READY = 80;

    public const STATUS_FINISH = 90;

    protected $appends = ['deadlinex'];


    protected function fields()
    {
        return [
            TextField::make('id')->label('Folio')->rules(['required']),
            TextField::make('status')->label('Estado')->rules(['required']),
        ];
    }

    public function getTranslations(): array
    {
        return [
            'singular' => 'Orden',
            'plural' => 'Ordenes',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class)
            ->with('orderDesignPrint')
            ->with('orderDesign')
            ->with('orderNewDesign')
            ->with('orderUpdateDesign')
            ->with('orderCustomDesign');
    }

    public function services(): HasMany
    {
        return $this->hasMany(OrderDetail::class)
            ->with('service')
            ->with('subservice')
            ->with('garment')
            ->with('orderDesignPrint')
            ->with('orderDesign')
            ->with('orderNewDesign')
            ->with('orderUpdateDesign')
            ->with('orderCustomDesign');
    }

    public function garment(): BelongsTo
    {
        return $this->belongsTo(Garment::class);
    }

    public function getStatusAttribute($value)
    {
        return $this->statusMapping()[$value] ?? 'unkown';
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
                return 'Sin realizar por Bordador/Estampador';
                break;
            case self::STATUS_READY:
                return 'Listo Para ser entregado';
                break;
            case self::STATUS_FINISH:
                return 'Entregado';
                break;
            case self::STATUS_WAITING_AUTH:
                return 'Esperando autorización';
                break;
        }
    }

    public function statusMapping(): array
    {
        return [
            self::STATUS_MISSING_PAYMENT => 'Pago pendiente',
            self::STATUS_WAITING_ORDER => 'Esperando el pedido',
            self::STATUS_ORDER_ARRIVED => 'Pedido LLego',
            self::STATUS_PENDING => 'Sin realizar por Bordador/Estampador',
            self::STATUS_READY => 'Listo Para ser entregado',
            self::STATUS_FINISH => 'Entregado',
            self::STATUS_WAITING_AUTH => 'Esperando autorización',
        ];
    }

    public function getOrderStatusAttribute($value)
    {
        if ($this->order_number == '0') {
            return 'NO APLICA';
        }

        return 'Es un pedido';
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getDeadlineAttribute($value): string
    {
        return  Date::createFromDate($value)->format('l j F Y');
    }

    public function getWhatsappNotificationAttribute($value): string
    {
        return  Date::createFromDate($value)->format('l j F Y');
    }

    public function getDeadlinexAttribute(): string
    {
        return $this->attributes['deadline'] ?? '';
    }

    public function getColorStatusAttribute()
    {
        switch ($this->getAttributes()['status']) {
            case self::STATUS_MISSING_PAYMENT:
                return 'error';
                break;
            case self::STATUS_WAITING_ORDER:
                return 'Warning';
                break;
            case self::STATUS_ORDER_ARRIVED:
                return 'info';
                break;
            case self::STATUS_PENDING:
                return 'warning';
                break;
            case self::STATUS_READY:
                return 'primary';
                break;
            case self::STATUS_FINISH:
                return 'success';
                break;
            case self::STATUS_WAITING_AUTH:
                return 'Warning';
                break;
        }
    }

    protected static function booted(): void
    {
        static::deleted(function (ORder $order) {
            $order_details = OrderDetail::where('order_id', $order->id)->get();

            foreach ($order_details as $detail) {
                $detail->delete();
            }
        });
    }
}
