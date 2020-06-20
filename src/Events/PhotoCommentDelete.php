<?php
namespace BotMan\Drivers\VK\Events;

class PhotoCommentDelete extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'photo_comment_delete';
    }
}