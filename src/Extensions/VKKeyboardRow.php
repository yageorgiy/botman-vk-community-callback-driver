<?php


namespace BotMan\Drivers\VK\Extensions;

/**
 * VK Keyboard Row builder class.
 * @package BotMan\Drivers\VK\Extensions
 */
class VKKeyboardRow {

    /** @var VKKeyboardButton[] $buttons */
    protected $buttons = [];


    /**
     * Static row creation.
     * @param VKKeyboardButton[] $buttons
     * @return VKKeyboardRow
     */
    public static function create($buttons = []){
        return new self($buttons);
    }

    /**
     * VKKeyboardRow constructor.
     * @param VKKeyboardButton[] $buttons
     */
    public function __construct($buttons) {
        $this->buttons = $buttons;
    }


    /**
     * Adds buttons to keyboard's row.
     * @param mixed ...$buttons
     * @return $this
     */
    public function addButtons(...$buttons){
        foreach($buttons as $button){
            $this->buttons[] = $button;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(){

        $serializedButtons = [];

        foreach($this->buttons as $button){
            $serializedButtons[] = $button->toArray();
        }

        return $serializedButtons;
    }

}