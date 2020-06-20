<?php
namespace BotMan\Drivers\VK\Events;

class BoardPostEdit extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'board_post_edit';
    }
}