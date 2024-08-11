<?php

namespace App\Models;

use Sdkconsultoria\Base\Fields\CustomField;
use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Employee extends BaseModel
{
    protected function fields()
    {
        return[
            TextField::make('name')->label('Nombre')->rules(['required']),
            CustomField::make('branch_id')
            ->rules(['required'])
            ->label('Sucursal')
            ->loadOptionsFromUrl('/admin/branch/api')
            ->setComponent('SelectedField')
            ->addExtra('valueName', 'id'),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Empleado',
            'plural' => 'Empleados',
        ];
    }
}
