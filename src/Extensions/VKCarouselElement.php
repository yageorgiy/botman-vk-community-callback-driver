<?php


namespace BotMan\Drivers\VK\Extensions;


use Illuminate\Support\Str;

class VKCarouselElement
{
    const MAX_TITLE_CHARS = 80;
    const MAX_DESCRIPTION_CHARS = 80;
    const MAX_BUTTONS = 3;



    /**
     * @var string $title
     */
    protected $title = "";
    /**
     * @var string $description
     */
    protected $description = "";
    /**
     * @var string $photo_id
     */
    protected $photo_id = "";
    /**
     * @var VKKeyboardButton[] $buttons
     */
    protected $buttons = [];
    /**
     * @var VKCarouselAction|null $action
     */
    protected $action;

    /**
     * VKCarouselElement constructor.
     * @param string $title
     * @param string $description
     * @param VKKeyboardButton[] $buttons
     * @param string $photo_id
     * @param VKCarouselAction|null $action
     */
    public function __construct(string $title, string $description, array $buttons, string $photo_id = "", VKCarouselAction $action = null)
    {
        $this->title = $title;
        $this->description = $description;
        $this->buttons = $buttons;
        $this->photo_id = $photo_id;
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getPhotoId(): string
    {
        return $this->photo_id;
    }

    /**
     * @param string $photo_id
     */
    public function setPhotoId(string $photo_id)
    {
        $this->photo_id = $photo_id;
    }

    /**
     * Serializing the carousel element to array.
     * @return array
     */
    public function toArray(){

        $result = [];

        $result["title"] = Str::limit($this->title, self::MAX_TITLE_CHARS);
        $result["description"] = Str::limit($this->description, self::MAX_DESCRIPTION_CHARS);

        if(!empty($this->photo_id))
            $result["photo_id"] = $this->photo_id;

        if(!empty($this->buttons))
        {
            $result["buttons"] = [];
            $i = 1;
            foreach($this->buttons as $button){
                if($i > self::MAX_BUTTONS) break;
                $result["buttons"][] = $button->toArray();
                $i++;
            }
        }

        if(!empty($this->action))
            $result["action"] = $this->action->toArray();


        return $result;
    }

}