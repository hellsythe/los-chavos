<?php

namespace App\Fields;

use Sdkconsultoria\Base\Fields\CustomField;

class TypeaheadField extends CustomField
{
    public function __construct()
    {
        $this->component = 'TypeaheadInput';
    }
}
