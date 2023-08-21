<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Payment extends BaseModel
{
    protected function fields()
    {
        return [
            TextField::make('order_id')->label('Folio de orden')->rules(['required']),
            TextField::make('amount')->label('Abono')->rules(['required']),
            TextField::make('payment_method')->label('Metodo de pago')->rules(['required']),
            TextField::make('created_at')->label('Fecha de creación')->rules(['required']),
        ];
    }

    public function getTranslations(): array
    {
        return [
            'singular' => 'Pago',
            'plural' => 'Pagos',
        ];
    }

    public function save(array $options = [])
    {
        parent::save($options);

        $this->updateOrderMissingPayment();
    }

    protected function updateOrderMissingPayment()
    {
        $order = Order::findModel($this->order_id);
        $order->missing_payment -= $this->amount;

        if ($order->missing_payment < 1) {
            $order->status = Order::STATUS_PENDING;

            if ($order->order_number == '1') {
                $order->status = Order::STATUS_WAITING_ORDER;
            }
        }

        $order->save();
    }

    public function order()
    {
        return $this->belongsToMany(Order::class);
    }

    public function getPaymentMethodAttribute($value): string
    {
        switch ($value) {
            case 'cash':
                return 'Efectivo';
                break;

            default:
                return 'Tarjeta';
                break;
        }
    }

    public function getAmountAttribute($value): string
    {
        return '$'.number_format($value,2);
    }
}
