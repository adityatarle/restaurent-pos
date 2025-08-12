<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;

class NotificationPushed
{
    use Dispatchable, SerializesModels;

    public string $message;
    public ?string $link;

    public function __construct(string $message, ?string $link = null)
    {
        $this->message = $message;
        $this->link = $link;

        $client = new Client();
        try {
            $client->post('http://localhost:3000/broadcast', [
                'json' => [
                    'event' => 'notification',
                    'data' => [
                        'message' => $this->message,
                        'link' => $this->link,
                        'created_at' => now()->toDateTimeString(),
                    ],
                    'roles' => ['reception', 'superadmin'],
                ],
            ]);
        } catch (\Exception $e) {
            // Intentionally swallow errors to avoid breaking user flow
        }
    }
}