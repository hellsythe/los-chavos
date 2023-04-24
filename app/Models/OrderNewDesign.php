<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class OrderNewDesign extends BaseModel
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
            'singular' => 'OrderNewDesign',
            'plural' => 'OrderNewDesign',
        ];
    }

    public function design()
    {
        return $this->belongsTo(Design::class);
    }
}
