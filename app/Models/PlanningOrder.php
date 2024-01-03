<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class PlanningOrder extends BaseModel
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
            'singular' => 'PlanningOrder',
            'plural' => 'PlanningOrder',
        ];
    }
}
