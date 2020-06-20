<?php
namespace BotMan\Drivers\VK\Events;

class WallReplyRestore extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'wall_reply_restore';
    }
}