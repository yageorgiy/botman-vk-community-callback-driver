<?php
namespace BotMan\Drivers\VK\Events;

class GroupJoin extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'group_join';
    }
}