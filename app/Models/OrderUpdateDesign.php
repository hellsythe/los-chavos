<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class OrderUpdateDesign extends BaseModel
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
            'singular' => 'OrderUpdateDesign',
            'plural' => 'OrderUpdateDesign',
        ];
    }

    public function design()
    {
        return $this->belongsTo(Design::class);
    }


    public function oldDesign()
    {
        return $this->belongsTo(Design::class, 'old_design_id')->withTrashed();
    }
}
