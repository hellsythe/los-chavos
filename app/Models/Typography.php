<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Fields\FileField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Typography extends BaseModel
{
    protected function fields()
    {
        return[
            TextField::make('name')->label('Nombre')->rules(['required']),
            FileField::make('example')->setDisk('typography/')->label('Ejemplo')->rules(['required,mimes:jpg,pdf,png'])->rulesUpdate(['mimes:jpg,pdf,png'])->searchable(false),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Tipografía',
            'plural' => 'Tipografía',
        ];
    }
}
