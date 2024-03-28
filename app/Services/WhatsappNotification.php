<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Sdkconsultoria\WhatsappCloudApi\Lib\Template\SendTemplate;
use Sdkconsultoria\WhatsappCloudApi\Models\Chat;
use Sdkconsultoria\WhatsappCloudApi\Models\Message;
use Sdkconsultoria\WhatsappCloudApi\Models\Template;
use Sdkconsultoria\WhatsappCloudApi\Models\WabaPhone;
use Sdkconsultoria\WhatsappCloudApi\Services\MessageService;

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
                        "body" => [
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $order->id
                                ],
                            ]
                        ],
                        "button" => [
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
                        "body" => [
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
                        "button" => [
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
            $this->notifyNewOrderToUser($user, $order);
        }

        if (strpos($order->service_type, 'Bordado') !== false) {
            foreach ($this->getAllEmbroideries() as $user) {
                $this->notifyNewOrderToUser($user, $order);
            }
        }

        if (strpos($order->service_type, 'Estampado') !== false) {
            foreach ($this->getAllPrinters() as $user) {
                $this->notifyNewOrderToUser($user, $order);
            }
        }

        if (strpos($order->service_type, 'Estampado') !== false && strpos($order->service_type, 'Bordado') !== false) {
            foreach ($this->getAllPrinters() as $user) {
                $this->notifyNewOrderToUser($user, $order);
            }
            foreach ($this->getAllEmbroideries() as $user) {
                $this->notifyNewOrderToUser($user, $order);
            }
        }
    }

    protected function notifyNewOrderToUser($user, $order)
    {
        if ($user->phone) {
            $this->send(
                $user->phone,
                'nuevo_pedido',
                [
                    "body" => [
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $order->id
                            ],
                        ]
                    ],
                    "button" => [
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

    public function sendOrderIsReadyNotification($order)
    {
        $this->send(
            $order->client->phone,
            'notificar_pedido_listo',
            [
                "body" => [
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
        $vars = ([
            "body" => [
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $order->id
                    ],
                ]
            ],
        ]);

        $this->send($order->client->phone, 'pedido_entregado', $vars);
    }


    public function sendOrderTicketToClient($order)
    {
        $vars = ([
            "body" => [
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $order->client->name
                    ],
                    [
                        "type" => "text",
                        "text" => $order->id
                    ],
                ]
            ],
            "header" => [
                "parameters" => [
                    [
                        "type" => "document",
                        "document" => [
                            "link" => URL::to('storage/tickets/' . $order->id) . '.pdf'
                            // "link" => 'https://los-chavos.site/storage/tickets/4341.pdf'
                        ]
                    ]
                ]
            ]
        ]);

        $this->send($order->client->phone, 'ticket_pedido', $vars);
    }

    protected function send($number, string $templateName, $vars = [])
    {
        try {
            $wabaPhone = WabaPhone::find(1)->first();
            $template = Template::where('name', $templateName)->first();

            resolve(SendTemplate::class)->send($wabaPhone, $template, "521$number", $vars, 'BOT');
        } catch (\Exception $e) {

        }
    }

    protected function getAllAdminis()
    {
        return User::whereHas("roles", function ($q) {
            $q->where("name", "super-admin");
        })->get();
    }

    protected function getAllEmbroideries()
    {
        return User::whereHas("roles", function ($q) {
            $q->where("name", "Bordador");
        })->get();
    }

    protected function getAllPrinters()
    {
        return User::whereHas("roles", function ($q) {
            $q->where("name", "Estampador");
        })->get();
    }
}
