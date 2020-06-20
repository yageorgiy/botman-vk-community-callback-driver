<?php
namespace BotMan\Drivers\VK\Events;

class AudioNew extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'audio_new';
    }
}