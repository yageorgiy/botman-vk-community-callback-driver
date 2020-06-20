<?php
namespace BotMan\Drivers\VK\Events;

class GroupOfficersEdit extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'group_officers_edit';
    }
}