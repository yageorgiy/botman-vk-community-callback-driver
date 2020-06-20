<?php
namespace BotMan\Drivers\VK\Events;

class LikeAdd extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'like_add';
    }
}