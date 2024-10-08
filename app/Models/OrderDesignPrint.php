<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class OrderDesignPrint extends BaseModel
{
    protected function fields()
    {
        return[
            TextField::make('')->label('')->rules(['required']),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'OrderDesignPrint',
            'plural' => 'OrderDesignPrint',
        ];
    }

    public function designPrint()
    {
        return $this->belongsTo(DesignPrint::class);
    }
}
