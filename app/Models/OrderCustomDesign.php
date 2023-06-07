<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class OrderCustomDesign extends BaseModel
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
            'singular' => 'OrderCustomDesign',
            'plural' => 'OrderCustomDesign',
        ];
    }

    public function typography(): BelongsTo
    {
        return $this->belongsTo(Typography::class);
    }
}
