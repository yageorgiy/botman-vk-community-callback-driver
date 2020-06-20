<?php
namespace BotMan\Drivers\VK\Events;

class VKPayTransaction extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'vkpay_transaction';
    }
}