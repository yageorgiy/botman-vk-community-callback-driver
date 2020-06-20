<?php
namespace BotMan\Drivers\VK\Events;

class WallPostNew extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'wall_post_new';
    }
}