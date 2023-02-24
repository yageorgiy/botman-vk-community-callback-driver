<?php


namespace BotMan\Drivers\VK\Extensions;


class VKCarousel {

    const MAX_ELEMENTS = 10;

    /**
     * @var VKCarouselElement[] $elements
     */
    protected $elements = [];

    /**
     * VKCarousel constructor.
     * @param VKCarouselElement[] $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }


    /**
     * Static carousel creation.
     * @return VKCarousel
     */
    public static function create(array $elements = []){
        return new self($elements);
    }

    /**
     * Add rows to the keyboard.
     * @param VKCarouselElement[] $elements
     * @return VKCarousel
     */
    public function addElements(...$elements){
        foreach($elements as $element){
            $this->elements[] = $element;
        }

        return $this;
    }

    /**
     * Serializing the carousel to array.
     * @return array
     */
    public function toArray(){

        $elements = [];

        $i = 1;
        foreach($this->elements as $element){
            if($i > self::MAX_ELEMENTS) break;
            $elements[] = $element->toArray();
            $i++;
        }

        $result = [
            "type" => "carousel",
            "elements" => $elements
        ];
        return $result;
    }

    /**
     * Serializing the carousel to JSON
     * Note: use this method to add carousel to additional parameters
     * @return false|string
     */
    public function toJSON(){
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}