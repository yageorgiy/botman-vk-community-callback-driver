<?php


namespace BotMan\Drivers\VK\Extensions;


class VKKeyboardButton {

    // Button colours
    const COLOR_PRIMARY = "primary";
    const COLOR_SECONDARY = "secondary";
    const COLOR_POSITIVE = "positive";
    const COLOR_NEGATIVE = "negative";
    const EMPTY_LABEL = " "; // Special char, imitates empty string

    // Max UTF-8 string length
    const MAX_CHARS = 40;

    /**
     * Static button creation.
     * @return VKKeyboardButton
     */
    public static function create(){
        return new self();
    }

    /** @var string */
    private $color = self::COLOR_PRIMARY;
    /** @var array */
    private $action = [
        "label" => "Button",
        "type" => "text",
        "payload" => "{}"
    ];


    /**
     * @param string $color
     * @return VKKeyboardButton
     */
    public function setColor($color) {
        $this->color = $color;
        return $this;
    }

    /**
     * Set action array
     * Note: method replaces all the data of: text, payload, type, value (and vice versa)
     * @param array $action
     * @return VKKeyboardButton
     */
    public function setRawAction($action){
        $this->action = $action;
        return $this;
    }

    /**
     * Set button text (title)
     * @param string $text
     * @return $this
     */
    public function setText($text = "Button"){
        if(trim($text) == "")
            $text = self::EMPTY_LABEL;

        if(mb_strlen($text) > self::MAX_CHARS)
            $text = mb_substr($text, 0, self::MAX_CHARS - 3, 'UTF-8').'...';

        $this->action["label"] = $text;
        return $this;
    }

    /**
     * Set button's payload
     * @param string $payload
     * @return $this
     */
    public function setPayload($payload = "{}"){
        $this->action["payload"] = $payload;
        return $this;
    }

    /**
     * Set button type
     * @param string $type
     * @return $this
     */
    public function setType($type = "text"){
        $this->action["type"] = $type;
        return $this;
    }

    /**
     * Set buttons value (push to payload)
     * @param string $value
     * @return $this
     */
    public function setValue($value = "button_value"){
        $this->action["payload"] = json_encode(["__message" => $value]);
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(){
        return [
            "color" => $this->color,
            "action" => $this->action
        ];
    }


}