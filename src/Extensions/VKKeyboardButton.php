<?php


namespace BotMan\Drivers\VK\Extensions;


class VKKeyboardButton {

    // Button colours
    const COLOR_PRIMARY = "primary";
    const COLOR_DEFAULT = "default";
    const COLOR_SECONDARY = "secondary";
    const COLOR_POSITIVE = "positive";
    const COLOR_NEGATIVE = "negative";
    const EMPTY_LABEL = " "; // Special char, imitates empty string

    // Max UTF-8 string length
    const MAX_CHARS = 40;

    // Type constants
    const TYPE_TEXT = "text";
    const TYPE_LOCATION = "location";
    const TYPE_OPEN_APP = "open_app";
    const TYPE_VK_PAY = "vkpay";
    const TYPE_CALLBACK = "callback";
    const TYPE_OPEN_LINK = "open_link";

    /**
     * Static button creation.
     * @return VKKeyboardButton
     */
    public static function create(){
        return new self();
    }

    /** @var string */
    protected $color = self::COLOR_PRIMARY;
    /** @var array */
    protected $action = [
        "label" => "Button",
        "type" => self::TYPE_TEXT,
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
     * Set button label (title)
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
     * Set button link (not for every button type, see https://vk.com/dev/bots_docs_3 for more info)
     * @param string $link
     * @return $this
     */
    public function setLink($link){
        $this->action["link"] = $link;
        return $this;
    }

    /**
     * Set button hash (not for every button type, see https://vk.com/dev/bots_docs_3 for more info)
     * @param string $hash
     * @return $this
     */
    public function setHash($hash){
        $this->action["hash"] = $hash;
        return $this;
    }

    /**
     * Set button AppID (not for every button type, see https://vk.com/dev/bots_docs_3 for more info)
     * @param int $id
     * @return $this
     */
    public function setAppID($id){
        $this->action["app_id"] = $id;
        return $this;
    }

    /**
     * Set button OwnerID (not for every button type, see https://vk.com/dev/bots_docs_3 for more info)
     * @param int $id
     * @return $this
     */
    public function setOwnerID($id){
        $this->action["owner_id"] = $id;
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
    public function setType($type = self::TYPE_TEXT){
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

        $result = [];
        // Color only for type = callback or text (according to docs https://vk.com/dev/bots_docs_3 of 09.09.2021)
        if(
            isset($this->action["type"]) and
            in_array($this->action["type"], [
                self::TYPE_CALLBACK,
                self::TYPE_TEXT
            ])
        )
            $result["color"] = $this->color;

        // Delete label field for several types of buttons
        if(
            isset($this->action["label"]) and
            isset($this->action["type"]) and
            in_array($this->action["type"], [
                self::TYPE_LOCATION,
                self::TYPE_VK_PAY
            ])
        )
            unset($this->action["label"]);

        $result["action"] = $this->action;
        return $result;
    }


}