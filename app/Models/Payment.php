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
            TextField::make('amount')->label('Pago total')->rules(['required']),
            TextField::make('payment_method')->label('Metodo de pago')->rules(['required']),
            TextField::make('created_at')->label('Fecha de creación')->rules(['nullable']),
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
        $this->updateOrderMissingPayment();
        parent::save($options);
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

        $this->updateMissingPayment();
        $order->save();
    }

    protected function updateMissingPayment()
    {
        $payment_available = $this->amount;

        $order = Order::findModel($this->order_id);

        if ($order->missing_embroidery > 0) {
            if ($payment_available > $order->missing_embroidery) {
                $this->total_embroidery = $order->missing_embroidery;
                $payment_available -= $order->missing_embroidery;
                $order->missing_embroidery = 0;
            } else {
                $order->missing_embroidery -= $payment_available;
                $this->total_embroidery = $payment_available;
                $payment_available = 0;
            }
        }

        if ($payment_available > 0) {
            $order->missing_print -= $payment_available;
            $this->total_print = $payment_available;
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

    // public function getAmountAttribute($value): string
    // {
    //     return '$'.number_format($value,2);
    // }
}
