<?php
namespace BotMan\Drivers\VK\Events;

class AppPayload extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'app_payload';
    }
}