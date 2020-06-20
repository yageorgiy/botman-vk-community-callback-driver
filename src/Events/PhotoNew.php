<?php
namespace BotMan\Drivers\VK\Events;

class PhotoNew extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'photo_new';
    }
}