<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    public function __construct(
        public Message $message
    ) {}

    public function broadcastOn()
{
    // Broadcast to both sender and receiver channels
    return [
        new PrivateChannel('chat.' . $this->message->receiver_id),
        new PrivateChannel('chat.' . $this->message->sender_id),
    ];
}

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        return [
            'id'       => $this->message->id,
            'message'  => $this->message->message,
            'sender'   => $this->message->sender_id,
            'receiver' => $this->message->receiver_id,
            'time'     => $this->message->created_at->toDateTimeString(),
        ];
    }
}
