<?php
namespace BotMan\Drivers\VK\Events;

class MessageEvent extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'message_event';
    }
}