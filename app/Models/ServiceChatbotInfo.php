<?php

namespace App\Models;

use App\Fields\TextAreaField;
use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class ServiceChatbotInfo extends BaseModel
{
    protected $table = 'service_chatbot_info';

    protected $fillable = [
        'name',
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
            TextField::make('name')->label('Nombre (texto libre)')->rules(['required'])->searchable(true),
            TextAreaField::make('description')->label('Descripción para el bot')->rules(['nullable'])->searchable(false),
            TextAreaField::make('notes')->label('Notas adicionales')->rules(['nullable'])->searchable(false),
        ];
    }

    public function getIndexFields()
    {
        return ['name', 'description'];
    }

    public function getParseSearchFilters()
    {
        return [
            ['field' => 'name'],
            ['field' => 'description'],
            ['field' => 'notes'],
        ];
    }
}
