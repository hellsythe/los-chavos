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
            FileField::make('example')->setDisk('typography/')->label('Tipografia')->rules(['required,mimetypes:font/ttf,font/sfnt'])->rulesUpdate(['mimetypes:font/ttf,font/sfnt'])->searchable(false),
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
