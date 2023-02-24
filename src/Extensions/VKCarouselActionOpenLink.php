<?php


namespace BotMan\Drivers\VK\Extensions;


class VKCarouselActionOpenLink implements  VKCarouselAction
{

    /**
     * @var string $link
     */
    protected $link = "";

    /**
     * VKCarouselActionOpenLink constructor.
     * @param string $link
     */
    public function __construct(string $link)
    {
        $this->link = $link;
    }


    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link)
    {
        $this->link = $link;
    }



    public function toArray(): array
    {
        return [
            "type" => "open_link",
            "link" => $this->link
        ];
    }
}