<?php
namespace BotMan\Drivers\VK\Events;

class GroupChangeSettings extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'group_change_settings';
    }
}