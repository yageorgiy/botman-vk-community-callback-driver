<?php
namespace BotMan\Drivers\VK\Events;

class UserUnblock extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'user_unblock';
    }
}