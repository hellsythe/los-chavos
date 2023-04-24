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
                $this->send($user->phone, 'notificar_solicitud');
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
            ],
        ]);
    }

    protected function getAllAdminis()
    {
        return User::whereHas("roles", function($q){ $q->where("name", "super-admin"); })->get();
    }

    // $request = Http::withToken(config('app.whatsapp_token'))->post('https://graph.facebook.com/v16.0/' . config('app.whatsapp_phone_id') . '/messages', [
    //     "messaging_product" => "whatsapp",
    //     "to" => "522213428198",
    //     "type" => "template",
    //     "template" => [
    //         "name" => "pedido_aprobado",
    //         "language" => ["code" => "es_MX"],
    //         "components" => [
    //             [
    //                 "type" => "body",
    //                 "parameters" => [
    //                     [
    //                         "type" => "text",
    //                         "text" => "8675"
    //                     ],
    //                     [
    //                         "type" => "text",
    //                         "text" => "Camilo rodriguez"
    //                     ],
    //                 ]
    //             ],
    //             [
    //                 "type" => "button",
    //                 "sub_type" => "url",
    //                 "index" => "0",
    //                 "parameters" => [
    //                     [
    //                         "type" => "text",
    //                         "text" => "443"
    //                     ]
    //                 ]
    //             ]
    //         ]
    //     ],
    // ]);
    // dump($request->body());
}
