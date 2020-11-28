<?php


namespace BotMan\Drivers\VK\Extensions;

use Illuminate\Support\Collection;

/**
 * VK Keyboard builder class.
 * @package BotMan\Drivers\VK\Extensions
 */
class VKKeyboard {

    // Vertical limit
    const MAX_ROWS_DEFAULT = 10;
    const MAX_ROWS_INLINE = 6;
    // Horizontal limit
    const MAX_BUTTONS_DEFAULT = 5;
    const MAX_BUTTONS_INLINE = 5;

    const MAX_INSTANCES_INLINE = 10;
    const MAX_INSTANCES_DEFAULT = 40;

    /**
     * Static keyboard creation.
     * @return VKKeyboard
     */
    public static function create(){
        return new self();
    }

    /**
     * Will the keyboard be shown once?
     * @var bool
     */
    protected $oneTime = true;

    /**
     * Keyboard's inline status (be shown under the message or in composing box).
     * @var bool
     */
    protected $inline = false;

    /**
     * @var VKKeyboardRow[]
     */
    public $rows = [];

    public function __construct(){

    }

    /**
     * Set "one_time" status.
     * @param bool $status
     * @return $this
     */
    public function setOneTime($status = true){
        $this->oneTime = $status;
        return $this;
    }

    /**
     * Set "inline" parameter.
     * @param $status
     * @return $this
     */
    public function setInline($status = true){
        $this->inline = $status;
        return $this;
    }

    /**
     * Add rows to the keyboard.
     * @param VKKeyboardRow[] $rows
     * @return VKKeyboard
     */
    public function addRows(...$rows){
        foreach($rows as $row){
            $this->rows[] = $row;
        }

        return $this;
    }

    /**
     * Serializing the keyboard to array.
     * @return array
     */
    public function toArray(){

        $_that = $this;

        $serializedRows = [];

        $btns_count = 0;
        foreach($this->rows as $row){
            // Buttons in row limit
            $serializedRows[] = Collection::make($row->toArray())->reject(function($item, $key) use($_that, &$btns_count){
                $btns_count++;
                return (
                    ( $_that->inline ) ?
                        ( ($key > VKKeyboard::MAX_BUTTONS_INLINE  - 1) || ($btns_count - 1 > VKKeyboard::MAX_INSTANCES_INLINE ) ) :
                        ( ($key > VKKeyboard::MAX_BUTTONS_DEFAULT - 1) || ($btns_count - 1 > VKKeyboard::MAX_INSTANCES_DEFAULT) )
                );
            })->toArray();
        }

        // Rows in keyboard limit
        $serializedRows = Collection::make($serializedRows)->reject(function($item, $key) use($_that){
            return
                (
                    ( $_that->inline ) ?
                        ( $key > VKKeyboard::MAX_ROWS_INLINE - 1 ) :
                        ( $key > VKKeyboard::MAX_ROWS_DEFAULT - 1 )
                ) ||
                count($item) <= 0;
        })->toArray();

        $result = [
            'buttons' => $serializedRows,
            'inline' => $this->inline
        ];

        if(!$this->inline)
            $result['one_time'] = $this->oneTime;

        return $result;
    }

    /**
     * Serializing the keyboard to JSON
     * Note: use this method to add keyboard to additional parameters
     * @return false|string
     */
    public function toJSON(){
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}