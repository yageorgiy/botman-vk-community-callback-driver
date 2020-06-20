<?php

namespace BotMan\Drivers\VK\Events;

use BotMan\BotMan\Interfaces\DriverEventInterface;

abstract class VKEvent implements DriverEventInterface
{
    /** @var array */
    protected $payload;

    /**
     * @param $payload
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Return the event name to match.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Return the event payload.
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}