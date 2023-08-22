<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class WhatsappNotification
{
    public function sendRequestNotification($order)
    {
        foreach ($this->getAllAdminis() as $user) {
            if ($user->phone) {
                $this->send(
                    $user->phone,
                    'notificar_solicitud',
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

    public function sendApprovedNotification($order)
    {
        foreach ($this->getAllAdminis() as $user) {
            if ($user->phone) {
                $this->send(
                    $user->phone,
                    'pedido_aprobado',
                    [
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $order->id
                                ],
                                [
                                    "type" => "text",
                                    "text" => auth()->user()->name . ' ' . auth()->user()->lastname
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

    public function sendNewOrder($order)
    {
        foreach ($this->getAllAdminis() as $user) {
            if ($user->phone) {
                $this->send(
                    $user->phone,
                    'nuevo_pedido',
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

    public function sendOrderIsReadyNotification($order)
    {
        $this->send(
            $order->client->phone,
            'servicio_listo_para_entregar',
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
            ]
        );
    }

    public function sendOrderIsDeliveryNotification($order)
    {
        $this->send(
            $order->client->phone,
            'pedido_entregado',
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
            ]
        );
    }


    public function sendOrderTicketToClient($order)
    {
        $this->send(
            $order->client->phone,
            'ticket_pedido',
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
                    "type" => "header",
                    "parameters" => [
                        [
                            "type" => "document",
                            "document" => [
                                "link" => URL::to('storage/tickets/' . $order->id) . '.pdf'
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    protected function send($number, $template, $components = [])
    {
        $request = Http::withToken(config('app.whatsapp_token'))->post('https://graph.facebook.com/v16.0/' . config('app.whatsapp_phone_id') . '/messages', [
            "messaging_product" => "whatsapp",
            "to" => '52' . $number,
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
