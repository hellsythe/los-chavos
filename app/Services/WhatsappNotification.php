<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Http;

class WhatsappNotification
{
    public function sendRequestNotification($order)
    {
        foreach ($this->getAllAdminis() as $user) {
            if ($user->phone) {
                $this->send(
                    $user->phone,
                    'solicitud_autorizacion_orden',
                    [
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $order->id
                                ],
                            ]
                        ],
                        [
                            "type" => "button",
                            "sub_type" => "url",
                            "index" => "0",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $order->id
                                ]
                            ]
                        ]
                    ]
                );
            }
        }
    }

    public function sendApprovedNotification()
    {
    }

    public function sendNewOrder()
    {
    }

    protected function send($number, $template, $components = [])
    {
        $request = Http::withToken(config('app.whatsapp_token'))->post('https://graph.facebook.com/v16.0/' . config('app.whatsapp_phone_id') . '/messages', [
            "messaging_product" => "whatsapp",
            "to" => $number,
            "type" => "template",
            "template" => [
                "name" => $template,
                "language" => ["code" => "es_MX"],
                'components' => $components
            ],
        ]);
    }

    protected function getAllAdminis()
    {
        return User::whereHas("roles", function ($q) {
            $q->where("name", "super-admin");
        })->get();
    }
}
