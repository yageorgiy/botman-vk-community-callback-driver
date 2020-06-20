<?php
namespace BotMan\Drivers\VK\Events;

class WallRepost extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'wall_repost';
    }
}