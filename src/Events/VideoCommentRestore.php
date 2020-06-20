<?php
namespace BotMan\Drivers\VK\Events;

class VideoCommentRestore extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'video_comment_restore';
    }
}