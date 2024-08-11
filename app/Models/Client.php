<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Client extends BaseModel
{
    protected static function booted(): void
    {
        static::created(function ($model) {
            $model->branch_id = session('branch');
            $model->save();
        });
    }

    protected function fields()
    {
        return[
            TextField::make('name')->label('Nombre completo')->rules(['required']),
            TextField::make('phone')->label('Teléfono')->rules(['required']),
            TextField::make('email')->label('Correo')->rules(['required']),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Cliente',
            'plural' => 'Clientes',
        ];
    }
}
