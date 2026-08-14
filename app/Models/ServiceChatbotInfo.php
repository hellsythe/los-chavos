<?php

namespace App\Models;

use Sdkconsultoria\Base\Fields\CustomField;
use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class ServiceChatbotInfo extends BaseModel
{
    protected $table = 'service_chatbot_info';

    protected $fillable = [
        'service_id',
        'description',
        'notes',
    ];

    public function getTranslations(): array
    {
        return [
            'singular' => 'Info Bot - Servicio',
            'plural' => 'Info Bot - Servicios',
            'gender' => 'f',
        ];
    }

    protected function fields()
    {
        return [
            CustomField::make('service_id')
                ->label('Servicio')
                ->rules(['required'])
                ->loadOptionsFromUrl('/admin/service/api')
                ->setComponent('SelectedField')
                ->addExtra('valueName', 'id'),
            TextField::make('description')->label('Descripción para el bot')->rules(['nullable'])->searchable(false),
            TextField::make('notes')->label('Notas adicionales')->rules(['nullable'])->searchable(false),
        ];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getIndexFields()
    {
        return ['service_id', 'description'];
    }

    public function getParseSearchFilters()
    {
        return [
            ['field' => 'description'],
            ['field' => 'notes'],
        ];
    }
}
