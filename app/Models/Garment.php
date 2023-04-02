<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Fields\FileField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Garment extends BaseModel
{
    protected function fields()
    {
        return [
            TextField::make('name')->label('Nombre')->rules(['required']),
            FileField::make('front')->setDisk('garment/front/')->label('Frente')->rules(['required,mimes:jpg,pdf,png'])->rulesUpdate(['mimes:jpg,pdf,png'])->searchable(false),
            FileField::make('back')->setDisk('garment/back/')->label('Espalda')->rules(['required,mimes:jpg,pdf,png'])->rulesUpdate(['mimes:jpg,pdf,png'])->searchable(false),
        ];
    }

    public function getTranslations(): array
    {
        return [
            'singular' => 'Prenda',
            'plural' => 'Prendas',
        ];
    }
}
