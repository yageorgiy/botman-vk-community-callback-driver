<?php
namespace BotMan\Drivers\VK\Events;

class MarketOrderEdit extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'market_order_edit';
    }
}