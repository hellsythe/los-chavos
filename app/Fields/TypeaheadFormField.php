<?php

namespace App\Fields;

use Sdkconsultoria\Base\Fields\CustomField;

class TypeaheadFormField extends CustomField
{
    public function __construct()
    {
        $this->component = 'TypeaheadFormField';
    }
}
