<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
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

    public function sendOrderIsReadyNotification($order)
    {
        $this->send(
            $order->client->phone,
            'notificar_pedido_listo',
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
        $template = Template::find(4);
        $template->setComponentsWithVars([
            [
                "type" => "body",
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => $order->id
                    ],
                ]
            ],
        ]);

        $phoneNumber = WabaPhone::find(1)->first();
        $response = resolve(MessageService::class)
            ->sendTemplate($phoneNumber, '521'.$order->client->phone, $template);

        $chat = $this->getChat($phoneNumber, '521'.$order->client->phone);
        $message = [
            'id' => $response['messages'][0]['id'],
            'content' => json_encode($template->getMessage()),
            'sender' => 'BOT'
        ];

        $this->registerMessage($chat, $message);
    }


    public function sendOrderTicketToClient($order)
    {
        $template = Template::find(5);

        $template->setComponentsWithVars([
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
        ]);

        $phoneNumber = WabaPhone::find(1)->first();
        $response = resolve(MessageService::class)
            ->sendTemplate($phoneNumber, '521'.$order->client->phone, $template);

        $chat = $this->getChat($phoneNumber, '521'.$order->client->phone);
        $message = [
            'id' => $response['messages'][0]['id'],
            'content' => json_encode($template->getMessage()),
            'sender' => 'BOT'
        ];

        $this->registerMessage($chat, $message);
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

    private function registerMessage(Chat $chat, $message)
    {
        $messageModel = new Message();
        $messageModel->direction = 'toClient';
        $messageModel->body = $message['content'];
        $messageModel->timestamp = time();
        $messageModel->message_id = $message['id'];
        $messageModel->type = 'text';
        $messageModel->chat_id = $chat->id;
        $messageModel->sended_by = $message['sender'] ?? null;
        $messageModel->save();
    }

    private function getChat($phoneNumber, $to)
    {
        $chat = Chat::firstOrCreate([
            'waba_phone' => $phoneNumber->phone_number_clean,
            'client_phone' => $to,
        ]);

        $chat->last_message = date('Y-m-d H:i:s');
        $chat->save();

        return $chat;
    }
}
