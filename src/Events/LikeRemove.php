<?php
namespace BotMan\Drivers\VK\Events;

class LikeRemove extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'like_remove';
    }
}