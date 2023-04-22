<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class OrderDetail extends BaseModel
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
            'singular' => 'OrderDetail',
            'plural' => 'OrderDetail',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function subservice(): BelongsTo
    {
        return $this->belongsTo(Subservice::class);
    }

    public function getDetailAttibute()
    {
        switch ($this->subservice_id) {
            case 3:
                return $this->orderUpdateDesign;
            case 4:
                return $this->orderNewDesign;
        }
    }

    public function orderDesign()
    {
        return $this->hasOne(OrderDesign::class);
    }

    public function orderNewDesign()
    {
        return $this->hasOne(OrderNewDesign::class);
    }

    public function orderUpdateDesign()
    {
        return $this->hasOne(OrderUpdateDesign::class);
    }

    public function orderCustomDesign()
    {
        return $this->hasOne(OrderCustomDesign::class);
    }
}
