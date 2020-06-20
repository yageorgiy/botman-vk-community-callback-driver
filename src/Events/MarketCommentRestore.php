<?php
namespace BotMan\Drivers\VK\Events;

class MarketCommentRestore extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'market_comment_restore';
    }
}