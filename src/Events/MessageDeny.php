<?php
namespace BotMan\Drivers\VK\Events;

class MessageDeny extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'message_deny';
    }
}