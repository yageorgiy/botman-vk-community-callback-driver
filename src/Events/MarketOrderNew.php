<?php
namespace BotMan\Drivers\VK\Events;

class MarketOrderNew extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'market_order_new';
    }
}