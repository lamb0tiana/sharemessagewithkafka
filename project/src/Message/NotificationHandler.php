<?php


namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NotificationHandler
{
    public function __invoke(Notification $notification){
    dd($notification);
    }
}