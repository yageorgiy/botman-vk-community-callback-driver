<?php
namespace BotMan\Drivers\VK\Events;

class PhotoCommentRestore extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'photo_comment_restore';
    }
}