<?php
namespace BotMan\Drivers\VK\Events;

class BoardPostNew extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'board_post_new';
    }
}