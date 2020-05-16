<?php
namespace BotMan\Drivers\VK;

use BotMan\BotMan\Drivers\Events\GenericEvent;
use BotMan\BotMan\Drivers\HttpDriver;
use BotMan\BotMan\Interfaces\DriverEventInterface;
use BotMan\BotMan\Messages\Attachments\File;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Attachments\Location;
use BotMan\BotMan\Messages\Attachments\Video;
use BotMan\BotMan\Messages\Attachments\Audio;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Users\User;
use BotMan\Drivers\VK\Events\Confirmation;
use BotMan\Drivers\VK\Events\MessageEdit;
use BotMan\Drivers\VK\Events\MessageNew;
use BotMan\Drivers\VK\Events\MessageReply;
use BotMan\Drivers\VK\Exceptions\VKException;
use CURLFile;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class VkCommunityCallbackDriver extends HttpDriver
{
    const DRIVER_NAME = "VK Community Callback Driver";

    /**
     * Array of messages
     *
     * @var array
     */
    private $messages;

    /**
     * IP-address of client
     *
     * @var string
     */
    private $ip;

    /**
     * Peer ID (user or conversation ID)
     * TODO: changing to int?
     *
     * @var string
     */
    private $peer_id;

    /**
     * Incoming message from user/conversation
     *
     * @param Request $request
     */

    /**
     * @var bool
     */
    private $reply = false;

    /**
     * @var
     */
//    private $driverEvent;


    /**
     * Building the payload
     *
     * @param Request $request
     */
    public function buildPayload(Request $request)
    {
        // Setting IP-address
        $this->ip = $request->getClientIp();
        // Setting the payload, which contains all JSON data sent by VK
        $this->payload = new ParameterBag((array) json_decode($request->getContent(), true));
        // Setting the event, which contains only JSON 'object' field
        $this->event = Collection::make((array) $this->payload->get("object"));
        // Setting the content, contains raw data sent by VK
        $this->content = $request->getContent();
        // Setting the config values from 'config/vk.php' file
        $this->config = Collection::make($this->config->get('vk', []));
    }



    /**
     * Manages the text to be echoed for VK API
     */
    protected function reply()
    {
        if(!$this->reply){
            ob_end_clean();
            header("Connection: close");
            ignore_user_abort(true);
            ob_start();

            switch($this->payload->get("type")){
                // Echo OK for incoming messages
                case "message_new":
                case "message_reply":
                case "message_edit":

                    $this->ok();
                    break;

                // Echo the confirmation token
                case "confirmation":
                    $this->echoConfirmationToken();
                    break;
            }

            $size = ob_get_length();
            header("Content-Length: $size");
            ob_end_flush();
            flush();

            $this->reply = true;
        }



    }

    /**
     * @var bool
     */
    private $ok = false;

    /**
     * Echos 'ok'
     */
    public function ok()
    {
        if(!$this->ok){
            echo("ok");
            $this->ok = true;
        }
    }

    /**
     * Echoes confirmation pass-phrase
     */
    public function echoConfirmationToken()
    {
        //TODO: save output?
        echo($this->config->get("confirm"));
    }


    /**
     * Determine if the request is for this driver.
     *
     * @return bool
     */
    public function matchesRequest()
    {
        //TODO: anything else?

        return
            !is_null($this->payload->get("secret")) &&
            $this->payload->get("secret") == $this->config->get("secret") &&
            !is_null($this->payload->get("group_id")) &&
            $this->payload->get("group_id") == $this->config->get("group_id"); //&&
//            preg_match('/95\.142\.([0-9]+)\.([0-9]+)/', $this->ip) === true; //TODO: ip checkups for production server
    }

    /**
     * Retrieve the chat message(s).
     *
     * @return array
     */
    public function getMessages()
    {

        $this->reply();

        if (empty($this->messages)) {
            $message = "generic";
            $peer_id = 0;
            $message_object = [];

            // message_new and message_reply / message_edit has different JSON schemas!
            switch($this->payload->get("type")){
                case "message_new":
                    $message_object = $this->payload->get("object")["message"];
                    $message = $this->payload->get("object")["message"]["text"];
                    $peer_id = $this->payload->get("object")["message"]["peer_id"];
                    break;

                case "message_reply":
                case "message_edit":
                    $message_object = $this->payload->get("object");
                    $message = $this->payload->get("object")["text"];
                    $peer_id = $this->payload->get("object")["peer_id"];
                    break;
            }
            $this->peer_id = $peer_id;

            // Replacing button's value from payload to message text
            if(isset($message_object["payload"])){
                $payload_text = json_decode($message_object["payload"], true)["__message"];
                if(isset($payload_text) && $payload_text != null) $message = $payload_text;
            }

            $incomingMessage = $this->serializeIncomingMessage($message, $peer_id, $peer_id, $message_object);
            $incomingMessage->addExtras("message_object", $message_object);

            $this->markSeen($incomingMessage);

            $this->messages = [$incomingMessage];
        }



        return $this->messages;
    }


    /**
     * Making up an incoming message
     *
     * @param $message
     * @param $sender
     * @param $recipient
     * @param $message_object
     * @return IncomingMessage
     */
    private function serializeIncomingMessage($message, $sender, $recipient, $message_object)
    {
        $attachments = [];
        $collection = Collection::make($message_object["attachments"]);

        // Getting photos
        (($_ = $collection->where('type', 'photo')->pluck('photo')->map(function ($item) {
                // Pick the best photo (with high resolution)
                $found = Collection::make($item["sizes"])->sortBy("height")->last();

                return new Image($found['url'], $item);
            })->toArray()) && count($_) > 0) ? ($attachments["photos"] = $_) : false;

        // Getting videos
        (($_ = $collection->where('type', 'video')->pluck('video')->map(function ($item) {
                // TODO: try to get URL by API methods if possible
                // Empty string is given here as it could not be directly retrieved via VK request
                return new Video("", $item);
            })->toArray()) && count($_) > 0) ? ($attachments["videos"] = $_) : false;

        // Getting audio
        (($_ = $collection->where('type', 'audio')->pluck('audio')->map(function ($item) {
                return new Audio($item["url"], $item);
            })->toArray()) && count($_) > 0) ? ($attachments["audios"] = $_) : false;

        // Getting files/documents
        (($_ = $collection->where('type', 'doc')->pluck('doc')->map(function ($item) {
                return new File($item["url"], $item);
            })->toArray()) && count($_) > 0) ? ($attachments["files"] = $_) : false;

        // Getting location
        (($_ = $message_object["geo"] ?? []) && count($_) > 0) ? ($attachments["location"] = new Location($_["coordinates"]["latitude"], $_["coordinates"]["longitude"], $_)) : false;


        // Make an incoming message with no text if it is so
        if($message == ""){
            // Returning message with images only
            if(count($attachments) == 1 and isset($attachments["photos"])){
                $result = new IncomingMessage(Image::PATTERN, $sender, $recipient, $this->payload);
                $result->setImages($attachments["photos"]);

                return $result;
            }

            // Returning message with videos only
            if(count($attachments) == 1 and isset($attachments["videos"])){
                $result = new IncomingMessage(Video::PATTERN, $sender, $recipient, $this->payload);
                $result->setVideos($attachments["videos"]);

                return $result;
            }

            // Returning message with audio only
            if(count($attachments) == 1 and isset($attachments["audios"])){
                $result = new IncomingMessage(Audio::PATTERN, $sender, $recipient, $this->payload);
                $result->setAudio($attachments["audios"]);

                return $result;
            }

            // Returning message with files only
            if(count($attachments) == 1 and isset($attachments["files"])){
                $result = new IncomingMessage(File::PATTERN, $sender, $recipient, $this->payload);
                $result->setFiles($attachments["files"]);

                return $result;
            }

            // Returning message with location only
            if(count($attachments) == 1 and isset($attachments["location"])){
                $result = new IncomingMessage(Location::PATTERN, $sender, $recipient, $this->payload);
                $result->setLocation($attachments["location"]);

                return $result;
            }
        }

        // Returning message with mixed attachments or with text given
        if(count($attachments) >= 1){
            $result = new IncomingMessage($message, $sender, $recipient, $this->payload);
            if(isset($attachments["photos"])) $result->setImages($attachments["photos"]);
            if(isset($attachments["videos"])) $result->setVideos($attachments["videos"]);
            if(isset($attachments["audios"])) $result->setAudio($attachments["audios"]);
            if(isset($attachments["files"])) $result->setFiles($attachments["files"]);
            if(isset($attachments["location"])) $result->setLocation($attachments["location"]);

            return $result;
        }

        // Returning regular message (if no attachments detected)
        return new IncomingMessage($message, $sender, $recipient, $this->payload);
    }

    /**
     * Checking if bot is configured
     *
     * @return bool
     */
    public function isConfigured()
    {
        return
            !empty($this->config->get('secret')) &&
            !empty($this->config->get('token')) &&
            !empty($this->config->get('version')) &&
            version_compare($this->config->get('version'), "5.103", ">=");
    }

    /**
     * Setting the driver event via payload
     * as we need to get type of the event.
     * Example data:
     * {"type": "group_join", "object": {"user_id": 1, "join_type" : "approved"}, "group_id": 1}
     * {"type": "confirmation", "group_id": 1}
     *
     * @return DriverEventInterface|bool
     */
//    public function hasMatchingEvent() {
//        if (!is_null($this->payload)) {
//            $this->driverEvent = $this->getEventFromEventData($this->payload);
//            return $this->driverEvent;
//        }
//
//        return false;
//    }

    /**
     * Generating event from payload
     *
     * @param $eventData
     * @return GenericEvent|Confirmation|MessageEdit|MessageNew|MessageReply
     */
//    protected function getEventFromEventData($eventData)
//    {
//        $name = (string) $eventData->get("type");
//        $event = (array) $eventData->get("object") ?? [];
//        switch ($name) {
//            case 'message_new':
//                return new MessageNew($event);
//                break;
//
//            case 'message_edit':
//                return new MessageEdit($event);
//                break;
//
//            case 'message_reply':
//                return new MessageReply($event);
//                break;
//
//            case 'confirmation':
//                return new Confirmation($event);
//                break;
//
//            default:
//                $event = new GenericEvent($event);
//                $event->setName($name);
//
//                return $event;
//                break;
//        }
//    }


    /**
     * Retrieve User information.
     *
     * @param IncomingMessage $matchingMessage
     * @return User
     */
    public function getUser(IncomingMessage $matchingMessage)
    {
        // Retrieving all relevant information about user
        $fields = $this->config->get("user_fields", "");

        $response = $this->api("users.get", [
            "user_ids" => $matchingMessage->getExtras("message_object")["from_id"],
            "fields" => $fields
        ], true);

        $first_name = $response["response"][0]["first_name"];
        $last_name = $response["response"][0]["first_name"];
        $username = "id".$response["response"][0]["id"];


        // TODO: remade with proper user class suitable for VK user
        return new User($matchingMessage->getExtras("message_object")["from_id"], $first_name, $last_name, $username, $response["response"][0]);
    }

    /**
     * Building conversation message created by bot
     *
     * @param IncomingMessage $message
     * @return Answer
     */
    public function getConversationAnswer(IncomingMessage $message)
    {
        return Answer::create($message->getText())->setMessage($message);
    }

    /**
     * Building payload for VK to send
     *
     * @param string|Question $message
     * @param IncomingMessage $matchingMessage
     * @param array $additionalParameters
     * @return array
     * @throws VKException
     */
    public function buildServicePayload($message, $matchingMessage, $additionalParameters = [])
    {
        $text = $message->getText();
        $peer_id = $matchingMessage->getRecipient();

        $data = [
            "peer_id" => $peer_id,
            "message" => $text,
            "random_id" => 0
        ];

        /*
        Not supported by VK API yet =(

        if($this->config->get("forward_messages") && $this->isConversation())
            $data["forward_messages"] = $this->event->get("message")["conversation_message_id"];
        */

        /* Building attachments */
        if(!is_string($message)){

            // Building buttons
            // TODO: make a dedicated method
            // TODO: make a suitable VK buttons class
            // TODO: optimize the mess
            if(method_exists($message,'getActions') && $message->getActions() != null){
                $actions = $message->getActions();

                $inline = false;
                $max_fields = $inline ? 10 : 50;
                $max_x = $inline ? 5 : 5;
                $max_y = $inline ? 6 : 10;

                $x = 0;
                $y = 0;
                $fields = 0;

                $buttons = [];
                foreach($actions as $action){
                    if($fields >= $max_fields) break;

                    $break_me = false;

                    $current_x = (isset($action["additional"]["__x"])) ? $action["additional"]["__x"] : $x;
                    $current_y = (isset($action["additional"]["__y"])) ? $action["additional"]["__y"] : $y;

                    if(!isset($action["additional"]["__x"])){
                        if($x + 1 > $max_x - 1) $x = 0; else $x++;
                    } else {
                        unset($action["additional"]["__x"]);
                    }
                    if(!isset($action["additional"]["__y"])){
                        if($x + 1 > $max_x - 1) $y++;
                        if($y > $max_y - 1) $break_me = true;
                    } else {
                        unset($action["additional"]["__y"]);
                    }


                    $cur_btn = &$buttons[$current_y][$current_x];

                    $cur_btn = $action["additional"];

                    $cur_btn["color"] = $cur_btn["color"] ?? "primary";
                    $cur_btn["action"] = $cur_btn["action"] ?? [];
                    $cur_btn["action"]["label"] = $cur_btn["action"]["label"] ?? $action["text"];
                    $cur_btn["action"]["type"] = $cur_btn["action"]["type"] ?? "text";
                    $cur_btn["action"]["payload"] = $cur_btn["action"]["payload"] ??
                        (isset($action["value"])) ? json_encode(["__message" => $action["value"]]) : json_encode([]);

                    $fields++;

                    if($break_me) break;
                }

                $keyboard = [
                    "buttons" => $buttons,
                    "inline" => $inline
                ];

                if(!$inline) $keyboard["one_time"] = true;



                $data["keyboard"] = json_encode($keyboard);
            }

            if(method_exists($message,'getAttachment') && $message->getAttachment() != null){
                $attachment = $message->getAttachment();

                $data["attachment"] = $this->prepareAttachments($matchingMessage, $attachment);
            }
        }

        if(isset($data["attachment"]) && is_array($data["attachment"]) && count($data["attachment"]) <= 0) unset($data["attachment"]);
        if(isset($data["attachment"]) && is_array($data["attachment"])) $data["attachment"] = implode(",", $data["attachment"]);

        $ret = [
            'data' => $data
        ];

        return $ret;
    }

    /**
     * Preparing attachments to be sent.
     *
     * @param IncomingMessage $matchingMessage
     * @param $attachment
     * @return array
     * @throws VKException
     */
    private function prepareAttachments($matchingMessage, $attachment){
        $ret = [];
        $peer_id = $matchingMessage->getRecipient();

        switch(get_class($attachment)){
            case "BotMan\BotMan\Messages\Attachments\Image":
                /** @var $attachment Image */

                // Just return already uploaded photo
                if(is_string($attachment->getExtras("vk_photo"))){
                    $ret[] = $attachment->getExtras("vk_photo");
                    break;
                }

                // Otherwise, upload image to VK
                // TODO: throw exceptions if error
                $getUploadUrl = $this->api("photos.getMessagesUploadServer", [
                    'peer_id' => $peer_id
                ], true);


                $uploadImg = $this->upload($getUploadUrl["response"]['upload_url'], $attachment->getUrl());

                // If error
                if($uploadImg["photo"] == "[]")
                    throw new VKException("Can't upload image to VK. Please, be sure photo has correct extension.");

                $saveImg = $this->api('photos.saveMessagesPhoto', [
                    'photo' => $uploadImg['photo'],
                    'server' => $uploadImg['server'],
                    'hash' => $uploadImg['hash']
                ], true);

                $ret[] = "photo".$saveImg["response"][0]['owner_id']."_".$saveImg["response"][0]['id'];

                break;

            case "BotMan\BotMan\Messages\Attachments\Video":
                /** @var $attachment Video */

                // Just return already uploaded video
                if(is_string($attachment->getExtras("vk_video"))){
                    $ret[] = $attachment->getExtras("vk_video");
                    break;
                }

                // TODO: upload video with user token feature
                throw new VKException("Uploading videos with community token is not supported by VK API (uploading with user token is under construction)");
                break;

            case "BotMan\BotMan\Messages\Attachments\Audio":
                /** @var $attachment Audio */

                // Just return already uploaded audio
                if(is_string($attachment->getExtras("vk_audio"))){
                    $ret[] = $attachment->getExtras("vk_audio");
                    break;
                }

                // Send audio as voice message
                if(is_bool($attachment->getExtras("vk_as_voice")) && $attachment->getExtras("vk_as_voice") == true){
                    $getUpload = $this->api("docs.getMessagesUploadServer", [
                        'peer_id' => $peer_id,
                        'type' => "audio_message"
                    ], true);


                    $upload = $this->upload($getUpload["response"]['upload_url'], $attachment->getUrl());

                    // If error
                    if($upload["file"] == "[]" || $upload["file"] == "" || $upload["file"] == null)
                        throw new VKException("Can't upload audio to VK. Please, be sure audo has correct extension (OGG is preferred). Learn more: https://vk.com/dev/upload_files_2");

                    $save = $this->api('docs.save', [
                        'file' => $upload['file']
                    ], true);

                    $ret[] = "audio_message".$save["response"]["audio_message"]['owner_id']."_".$save["response"]["audio_message"]['id'];
                    break;
                }



                throw new VKException("Uploading audio is restricted by VK API");
                break;

            case "BotMan\BotMan\Messages\Attachments\File":
                /** @var $attachment File */

                // Just return already uploaded document
                if(is_string($attachment->getExtras("vk_doc"))){
                    $ret[] = $attachment->getExtras("vk_doc");
                    break;
                }

                $getUpload = $this->api("docs.getMessagesUploadServer", [
                    'peer_id' => $peer_id,
                    'type' => "doc"
                ], true);


                $upload = $this->upload($getUpload["response"]['upload_url'], $attachment->getUrl());

                // If error
                if(!isset($upload["file"]) || $upload["file"] == "[]" || $upload["file"] == "" || $upload["file"] == null)
                    throw new VKException("Can't upload file to VK. Please, be sure file has correct extension.");


                $_ = [
                    'file' => $upload['file']
                ];

                if($attachment->getExtras("vk_doc_title") != null)
                    $_["title"] = $attachment->getExtras("vk_doc_title");

                if($attachment->getExtras("vk_doc_tags") != null)
                    $_["tags"] = $attachment->getExtras("vk_doc_tags");

                $save = $this->api('docs.save', $_, true);

                $ret[] = "doc".$save["response"]["doc"]['owner_id']."_".$save["response"]["doc"]['id'];
                break;
        }

        return $ret;
    }


    /**
     * Sending payload to VK
     *
     * @param mixed $payload
     * @return Response
     */
    public function sendPayload($payload)
    {
        return $this->api("messages.send", $payload["data"]);
    }

    /**
     * Low-level method to perform driver specific API requests (unused)
     *
     * @param string $endpoint
     * @param array $parameters
     * @param IncomingMessage $matchingMessage
     * @return void
     */
    public function sendRequest($endpoint, array $parameters, IncomingMessage $matchingMessage)
    {

    }

    /**
     * Sending typing action
     *
     * @param IncomingMessage $matchingMessage
     * @return bool|void
     */
    public function types(IncomingMessage $matchingMessage)
    {
        $this->api("messages.setActivity", [
            "peer_id" => $matchingMessage->getRecipient(),
            "type" => "typing"
        ], true);

        return true;
    }

    /**
     * @param IncomingMessage $matchingMessage
     * @return Response
     */
    public function markSeen(IncomingMessage $matchingMessage)
    {
        return $this->api("messages.markAsRead", [
            "message_ids" => $matchingMessage->getPayload()->get("id"),
            "peer_id" => $matchingMessage->getSender()
        ]);
    }


    /**
     * Executing all api requests via this method
     *
     * @param string $method
     * @param array $post_data
     * @param bool $asArray
     * @return Response
     */
    public function api($method, $post_data, $asArray = false)
    {

        $post_data += [
            "v" => $this->config->get("version"),
            "access_token" => $this->config->get("token")
        ];
        $response = $this->http->post($this->config->get("endpoint").$method, [], $post_data, [], false);

        if($asArray)
            return json_decode($response->getContent(),true);

        return $response;
    }

    /**
     * Uploading files for attachments
     *
     * @param string $url
     * @param string $filename
     * @return array
     */
    public function upload($url, $filename/*, $asArray = false*/)
    {

        //TODO: upload with native tools
        $basename = "";
        if(preg_match("/^http/i", $filename)){
            $temp_dir = sys_get_temp_dir();

            $basename = tempnam($temp_dir, "botman_vk_driver_api_");
            $contents = fopen($filename, 'r');
            file_put_contents($basename, $contents);
            fclose($contents);
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => new CURLfile($filename)));
        $json = curl_exec($curl);
        curl_close($curl);

        //TODO: check for exceptions

        if(preg_match("/^http/i", $filename)) {
            if(file_exists($basename)) unlink($basename);
        }

        return json_decode($json, true);
    }

    /**
     * Is conversation?
     *
     * @return bool
     */
    public function isConversation()
    {
        return $this->peer_id >= 2000000000;
    }

}