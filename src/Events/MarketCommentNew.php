<?php
namespace BotMan\Drivers\VK\Events;

class MarketCommentNew extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'market_comment_new';
    }
}