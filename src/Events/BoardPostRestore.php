<?php
namespace BotMan\Drivers\VK\Events;

class BoardPostRestore extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'board_post_restore';
    }
}