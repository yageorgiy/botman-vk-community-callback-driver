<?php


namespace BotMan\Drivers\VK\Extensions;


class VKCarouselActionOpenPhoto implements VKCarouselAction
{
    public function toArray(): array
    {
        return [
            "type" => "open_photo"
        ];
    }
}