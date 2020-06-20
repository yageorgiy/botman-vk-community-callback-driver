<?php
namespace BotMan\Drivers\VK\Events;

class GroupChangePhoto extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'group_change_photo';
    }
}