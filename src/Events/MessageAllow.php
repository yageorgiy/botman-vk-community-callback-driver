<?php
namespace BotMan\Drivers\VK\Events;

class MessageAllow extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'message_allow';
    }
}