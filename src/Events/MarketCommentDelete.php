<?php
namespace BotMan\Drivers\VK\Events;

class MarketCommentDelete extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'market_comment_delete';
    }
}