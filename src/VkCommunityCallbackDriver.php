<?php
namespace BotMan\Drivers\VK;

use BotMan\BotMan\Drivers\Events\GenericEvent;
use BotMan\BotMan\Drivers\HttpDriver;
use BotMan\BotMan\Interfaces\DriverEventInterface;
use BotMan\BotMan\Interfaces\QuestionActionInterface;
use BotMan\BotMan\Messages\Attachments\Audio;
use BotMan\BotMan\Messages\Attachments\File;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Attachments\Location;
use BotMan\BotMan\Messages\Attachments\Video;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Users\User;
use BotMan\Drivers\VK\Events\AppPayload;
use BotMan\Drivers\VK\Events\AudioNew;
use BotMan\Drivers\VK\Events\BoardPostDelete;
use BotMan\Drivers\VK\Events\BoardPostEdit;
use BotMan\Drivers\VK\Events\BoardPostNew;
use BotMan\Drivers\VK\Events\BoardPostRestore;
use BotMan\Drivers\VK\Events\Confirmation;
use BotMan\Drivers\VK\Events\GroupChangePhoto;
use BotMan\Drivers\VK\Events\GroupChangeSettings;
use BotMan\Drivers\VK\Events\GroupJoin;
use BotMan\Drivers\VK\Events\GroupLeave;
use BotMan\Drivers\VK\Events\GroupOfficersEdit;
use BotMan\Drivers\VK\Events\LikeAdd;
use BotMan\Drivers\VK\Events\LikeRemove;
use BotMan\Drivers\VK\Events\MarketCommentDelete;
use BotMan\Drivers\VK\Events\MarketCommentEdit;
use BotMan\Drivers\VK\Events\MarketCommentNew;
use BotMan\Drivers\VK\Events\MarketCommentRestore;
use BotMan\Drivers\VK\Events\MarketOrderEdit;
use BotMan\Drivers\VK\Events\MarketOrderNew;
use BotMan\Drivers\VK\Events\MessageAllow;
use BotMan\Drivers\VK\Events\MessageDeny;
use BotMan\Drivers\VK\Events\MessageEdit;
use BotMan\Drivers\VK\Events\MessageNew;
use BotMan\Drivers\VK\Events\MessageReply;
use BotMan\Drivers\VK\Events\MessageTypingState;
use BotMan\Drivers\VK\Events\PhotoCommentDelete;
use BotMan\Drivers\VK\Events\PhotoCommentEdit;
use BotMan\Drivers\VK\Events\PhotoCommentNew;
use BotMan\Drivers\VK\Events\PhotoCommentRestore;
use BotMan\Drivers\VK\Events\PhotoNew;
use BotMan\Drivers\VK\Events\PollVoteNew;
use BotMan\Drivers\VK\Events\UserBlock;
use BotMan\Drivers\VK\Events\UserUnblock;
use BotMan\Drivers\VK\Events\VideoCommentDelete;
use BotMan\Drivers\VK\Events\VideoCommentEdit;
use BotMan\Drivers\VK\Events\VideoCommentNew;
use BotMan\Drivers\VK\Events\VideoCommentRestore;
use BotMan\Drivers\VK\Events\VideoNew;
use BotMan\Drivers\VK\Events\VKEvent;
use BotMan\Drivers\VK\Events\VKPayTransaction;
use BotMan\Drivers\VK\Events\WallPostNew;
use BotMan\Drivers\VK\Events\WallReplyDelete;
use BotMan\Drivers\VK\Events\WallReplyEdit;
use BotMan\Drivers\VK\Events\WallReplyNew;
use BotMan\Drivers\VK\Events\WallReplyRestore;
use BotMan\Drivers\VK\Events\WallRepost;
use BotMan\Drivers\VK\Exceptions\VKDriverDeprecatedFeature;
use BotMan\Drivers\VK\Exceptions\VKDriverException;
use CURLFile;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VkCommunityCallbackDriver extends HttpDriver {
    const DRIVER_NAME = "VkCommunityCallback";

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
     * @var VKEvent
     */
    private $driverEvent;


    /**
     * Building the payload
     *
     * @param Request $request
     */
    public function buildPayload(Request $request) {
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
    protected function reply() {
        if(!$this->reply){
            ob_end_clean();
            header("Connection: close");
            ignore_user_abort(true);
            ob_start();



            switch($this->payload->get("type")){
                // Echo the confirmation token
                // [DEPRECATED] Use $botman->on("confirmation", function($payload, Botman $bot){ echo("token"); }); in routes/botman.php instead
                case "confirmation":
//                    $this->echoConfirmationToken();
                    break;

                // Echo OK for all incoming events
                default:
                    $this->ok();
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
    public function ok() {
        if(!$this->ok){
            echo("ok");
            $this->ok = true;
        }
    }

    /**
     * Echoes confirmation pass-phrase
     * @deprecated deprecated since 1.4.2
     */
    public function echoConfirmationToken() {
        //TODO: save output?
        echo($this->config->get("confirm"));
    }


    /**
     * Determine if the request is for this driver.
     *
     * @return bool
     * @throws VKDriverDeprecatedFeature
     * @throws VKDriverException
     */
    public function matchesRequest() {
        //TODO: anything else?
        //TODO: verification via implementing VerifiesService's verifyRequest() method

        $check = !is_null($this->payload->get("secret")) &&
            $this->payload->get("secret") == $this->config->get("secret") &&
            !is_null($this->payload->get("group_id")) &&
            $this->payload->get("group_id") == $this->config->get("group_id");
        //&&
//              preg_match('/95\.142\.([0-9]+)\.([0-9]+)/', $this->ip) === true; //TODO: ip checkups for production server

        // Stop performing the request if errors
        if($check) $this->configurationCheckUp();

        return $check;

    }

    /**
     * Retrieve the chat message(s).
     *
     * @return array
     * @throws VKDriverException
     */
    public function getMessages() {

        if($this->payload->get("type") != "confirmation")
            $this->reply(); // Reply 'ok' for all events (except confirmation)

        if (empty($this->messages)) {
            $message_object = $this->extractPrivateMessageFromPayload($this->payload);

            if($message_object !== false){
                $this->peer_id = $message_object['peer_id'];

                // Replacing button's value from payload to message text
                if(isset($message_object["payload"])){
                    $payload_text = json_decode($message_object["payload"], true)["__message"];
                    if(isset($payload_text) && $payload_text != null) $message = $payload_text;
                }

                $incomingMessage = $this->serializeIncomingMessage($message_object['text'], $message_object['from_id'],
                    $message_object['peer_id'], $message_object);
                $incomingMessage->addExtras("message_object", $message_object);

                // Client information (only for new messages)
                if(isset($this->payload->get("object")["client_info"]))
                    $incomingMessage->addExtras("client_info", $this->payload->get("object")["client_info"]);

                $this->markSeen($incomingMessage);

                $this->messages = [$incomingMessage];
            }

        }



        return $this->messages ?? [];
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
    private function serializeIncomingMessage($message, $sender, $recipient, $message_object) {
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
    public function isConfigured() {

        $anyExceptions = false;
        try{
            $this->configurationCheckUp();
        } catch (VKDriverDeprecatedFeature $e){
            $anyExceptions = true;
        } catch (VKDriverException $e){
            $anyExceptions = true;
        }

        return
            !$anyExceptions; // &&
//            !empty($this->config->get('secret')) &&
//            !empty($this->config->get('token')) &&
//            !empty($this->config->get('version')) &&
//            version_compare($this->config->get('version'), "5.103", ">=");
    }


    /**
     *
     *
     * @return bool
     * @throws VKDriverDeprecatedFeature
     * @throws VKDriverException
     */
    public function configurationCheckUp(){
        // Error of deprecated and unused feature of VK_CONFIRM
        if(!empty($this->config->get("confirm")) || $this->config->get("confirm") != ""){
            throw new VKDriverDeprecatedFeature(
                "VK_CONFIRM (or \$botmanSettings[\"vk\"][\"confirm\"]) field is no longer used by driver. Please, just leave it blank (empty string) and use \$botman->on(); feature (in routes/botman.php) to echo confirmation pass-phrase. ".
                "Example code: ".
                "\$botman->on(\"confirmation\", function(\$payload, \$bot){ echo(\"CONFIRMATION_TOKEN_HERE\"); });"
            );
        }

        // Error if token is empty
        if(empty($this->config->get("token")) || $this->config->get("token") == ""){
            throw new VKDriverException("VK_ACCESS_TOKEN (or \$botmanSettings[\"vk\"][\"token\"]) is empty, but required by the driver. Please, add VK_ACCESS_TOKEN field with community access token to .env file in project's root folder (or configure settings via BotManFactory::create()). Example: VK_ACCESS_TOKEN=1a2b**************3c4d");
        }

        // Error if secret is empty
        if(empty($this->config->get("secret")) || $this->config->get("secret") == ""){
            throw new VKDriverException("VK_SECRET_KEY (or \$botmanSettings[\"vk\"][\"secret\"]) is empty, but required by the driver. Please, add VK_SECRET_KEY field with secret key to .env file in project's root folder (or configure settings via BotManFactory::create()). Example: VK_SECRET_KEY=i_love_apples");
        }

        // Error if version is empty
        if(empty($this->config->get("version")) || $this->config->get("version") == ""){
            throw new VKDriverException("VK_API_VERSION (or \$botmanSettings[\"vk\"][\"version\"]) is empty, but required by the driver. Please, add VK_API_VERSION field with version number to .env file in project's root folder (or configure settings via BotManFactory::create()). Note: driver supports API version newer than 5.103 only. Example: VK_API_VERSION=5.107");
        }

        // Error if version is incorrect
        if(!version_compare($this->config->get('version'), "5.103", ">=")){
            throw new VKDriverException("VK_API_VERSION (or \$botmanSettings[\"vk\"][\"version\"]) is older than version 5.103. Please, use 5.103 API version or greater.");
        }

        return true;
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
    public function hasMatchingEvent() {

        // Check if VK request
        $check =    $this->payload->get("secret") == $this->config->get("secret") &&
            $this->payload->get("group_id") == $this->config->get("group_id");

        if (!is_null($this->payload) && $check) {
            $this->driverEvent = $this->getEventFromEventData($this->payload);

            // Ignore incoming messages (used by Botman-native operations like hears(), etc.)
            switch ($this->driverEvent->getName()){
                case "message_new":
                case "message_edit":
                case "message_reply":
                    return true;
                    break;
            }

            // Return other events
            return $this->driverEvent;
        }

        return false;
    }

    /**
     * Generating event from payload
     *
     * @param Collection|ParameterBag $eventData
     * @return array|GenericEvent|AppPayload|AudioNew|BoardPostDelete|BoardPostEdit|BoardPostNew|BoardPostRestore|Confirmation|GroupChangePhoto|GroupChangeSettings|GroupJoin|GroupLeave|GroupOfficersEdit|LikeAdd|LikeRemove|MarketCommentDelete|MarketCommentEdit|MarketCommentNew|MarketCommentRestore|MarketOrderNew|MessageAllow|MessageDeny|MessageEdit|MessageNew|MessageReply|MessageTypingState|PhotoCommentDelete|PhotoCommentEdit|PhotoCommentNew|PhotoCommentRestore|PhotoNew|PollVoteNew|UserBlock|UserUnblock|VideoCommentDelete|VideoCommentEdit|VideoCommentNew|VideoCommentRestore|VideoNew|VKPayTransaction|WallPostNew|WallReplyDelete|WallReplyEdit|WallReplyNew|WallReplyRestore|WallRepost|MarketOrderEdit
     */
    protected function getEventFromEventData($eventData)
    {
        $name = (string) $eventData->get("type");
        $event = (array) $eventData->get("object") ?? [];
        switch ($name) {

            case 'confirmation':
                return new Confirmation($eventData); // Storing the whole data as there is no "object" field ("type" and "group_id" only)
                break;


            // All events of russian docs of https://vk.com/dev/groups_events (english docs are deprecated?)
            // incl. message_typing_state which is missing in official VK docs for some reason

            case 'message_new':
                return new MessageNew($event);
                break;

            case 'message_edit':
                return new MessageEdit($event);
                break;

            case 'message_reply':
                return new MessageReply($event);
                break;

            case 'message_allow':
                return new MessageAllow($event);
                break;

            case 'message_deny':
                return new MessageDeny($event);
                break;

            case 'message_typing_state':
                return new MessageTypingState($event);
                break;



            case 'photo_new':
                return new PhotoNew($event);
                break;

            case 'photo_comment_new':
                return new PhotoCommentNew($event);
                break;

            case 'photo_comment_edit':
                return new PhotoCommentEdit($event);
                break;

            case 'photo_comment_restore':
                return new PhotoCommentRestore($event);
                break;

            case 'photo_comment_delete':
                return new PhotoCommentDelete($event);
                break;



            case 'audio_new':
                return new AudioNew($event);
                break;



            case 'video_new':
                return new VideoNew($event);
                break;

            case 'video_comment_new':
                return new VideoCommentNew($event);
                break;

            case 'video_comment_edit':
                return new VideoCommentEdit($event);
                break;

            case 'video_comment_restore':
                return new VideoCommentRestore($event);
                break;

            case 'video_comment_delete':
                return new VideoCommentDelete($event);
                break;



            case 'wall_post_new':
                return new WallPostNew($event);
                break;

            case 'wall_repost':
                return new WallRepost($event);
                break;

            case 'wall_reply_new':
                return new WallReplyNew($event);
                break;

            case 'wall_reply_edit':
                return new WallReplyEdit($event);
                break;

            case 'wall_reply_restore':
                return new WallReplyRestore($event);
                break;

            case 'wall_reply_delete':
                return new WallReplyDelete($event);
                break;



            case 'board_post_new':
                return new BoardPostNew($event);
                break;

            case 'board_post_edit':
                return new BoardPostEdit($event);
                break;

            case 'board_post_restore':
                return new BoardPostRestore($event);
                break;

            case 'board_post_delete':
                return new BoardPostDelete($event);
                break;



            case 'market_comment_new':
                return new MarketCommentNew($event);
                break;

            case 'market_comment_edit':
                return new MarketCommentEdit($event);
                break;

            case 'market_comment_restore':
                return new MarketCommentRestore($event);
                break;

            case 'market_comment_delete':
                return new MarketCommentDelete($event);
                break;

            case 'market_order_new':
                return new MarketOrderNew($event);
                break;

            case 'market_order_edit':
                return new MarketOrderEdit($event);
                break;



            case 'group_leave':
                return new GroupLeave($event);
                break;

            case 'group_join':
                return new GroupJoin($event);
                break;

            case 'user_block':
                return new UserBlock($event);
                break;

            case 'user_unblock':
                return new UserUnblock($event);
                break;



            case 'poll_vote_new':
                return new PollVoteNew($event);
                break;

            case 'group_officers_edit':
                return new GroupOfficersEdit($event);
                break;

            case 'group_change_settings':
                return new GroupChangeSettings($event);
                break;

            case 'group_change_photo':
                return new GroupChangePhoto($event);
                break;

            case 'vkpay_transaction':
                return new VKPayTransaction($event);
                break;

            case 'app_payload':
                return new AppPayload($event);
                break;

            case 'like_add':
                return new LikeAdd($event);
                break;

            case 'like_remove':
                return new LikeRemove($event);
                break;

            default:
                $event = new GenericEvent($event);
                $event->setName($name);

                return $event;
                break;
        }
    }


    /**
     * Retrieve User information.
     *
     * @param IncomingMessage $matchingMessage
     * @return User
     * @throws VKDriverException
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
        $last_name = $response["response"][0]["last_name"];
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

        $answer = Answer::create($message->getText())->setMessage($message);

        $message_object = $message->getExtras("message_object");

        if(isset($message_object["payload"])){
            $answer->setInteractiveReply(true);
            $answer->setText($message_object["text"]);
            $answer->setValue($message->getText());
        }

        return $answer;
    }

    /**
     * Building payload for VK to send
     *
     * @param string|Question $message
     * @param IncomingMessage $matchingMessage
     * @param array $additionalParameters
     * @return array
     * @throws VKDriverException
     */
    public function buildServicePayload($message, $matchingMessage, $additionalParameters = [])
    {
        $text = $message->getText();
        $peer_id = ($matchingMessage->getRecipient() != "") ? $matchingMessage->getRecipient() : $matchingMessage->getSender();

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
        if($message instanceof Question){

            // Building simple keyboard used in Question (shown "inlined" and once by default)
            if(method_exists($message,'getActions') && $message->getActions() != null){
                /** @var QuestionActionInterface[] $actions */
                $actions = $message->getActions();

                $inline = false; // Force the keyboard to be non-inline
                $one_time = true; // Force the keyboard to be shown once

                $maxButtons = 1; //$inline ? 5 : 5;
                $maxRows = $inline ? 6 : 10;

                $buttons = Collection::make($actions)
                    // Use only BotMan\BotMan\Messages\Outgoing\Actions\Button class to send
                    ->reject(function($button){
                        return ($button instanceof Button);
                    })
                    // Use "additional" field as base, set required but unset values
                    ->map(function($button){
                        $item = $button["additional"];

                        // Set defaults of the button if unset
                        $item["color"] = $item["color"] ?? ( (isset($button["color"]) ) ? $button["color"] : "primary" );
                        $item["action"] = $item["action"] ?? [];
                        $item["action"]["label"] = $item["action"]["label"] ?? $button["text"];
                        $item["action"]["type"] = $item["action"]["type"] ?? "text";
                        $item["action"]["payload"] = $item["action"]["payload"] ??
                            ( (isset($button["value"])) ? json_encode(["__message" => $button["value"]]) : json_encode([]) );

                        // Unset field of migration (used in older versions of the driver)
                        unset($item["__x"]);
                        unset($item["__y"]);

                        return $item;
                    })
                    // Dividing buttons by max in a row
                    ->chunk($maxButtons)
                    // Rejecting extra rows to be accepted by VK
                    ->reject(function($row, $key) use($maxRows){
                        return $key > ($maxRows - 1);
                    })
                    // Serializing to array
                    ->toArray();

                $keyboard = [
                    "one_time" => $one_time,
                    "buttons" => $buttons,
                    "inline" => $inline
                ];

                $data["keyboard"] = json_encode($keyboard);
            }
        }

        // Adding attachment (both for Question and OutcomingMessage)
        if(method_exists($message,'getAttachment') && $message->getAttachment() != null){
            $data["attachment"] =
                $this->prepareAttachments($matchingMessage, $message->getAttachment());
        }

        if(isset($data["attachment"]) && is_array($data["attachment"]) && count($data["attachment"]) <= 0) unset($data["attachment"]);
        if(isset($data["attachment"]) && is_array($data["attachment"])) $data["attachment"] = implode(",", $data["attachment"]);


        $ret = [
            'data' => array_merge($data, $additionalParameters)
        ];

        return $ret;
    }

    /**
     * Preparing attachments to be sent.
     *
     * @param IncomingMessage $matchingMessage
     * @param $attachment
     * @return array
     * @throws VKDriverException
     * @throws VKDriverException
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
                    throw new VKDriverException("Can't upload image to VK. Please, be sure photo has correct extension.");

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
                throw new VKDriverException("Uploading videos with community token is not supported by VK API (uploading with user token is under construction)");
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

                    // Show "*bot* is recording audiomessage" caption
                    $this->api("messages.setActivity", [
                        "peer_id" => $matchingMessage->getRecipient(),
                        "type" => "audiomessage"
                    ], true);


                    $getUpload = $this->api("docs.getMessagesUploadServer", [
                        'peer_id' => $peer_id,
                        'type' => "audio_message"
                    ], true);


                    $upload = $this->upload($getUpload["response"]['upload_url'], $attachment->getUrl());

                    // If error
                    if($upload["file"] == "[]" || $upload["file"] == "" || $upload["file"] == null)
                        throw new VKDriverException("Can't upload audio to VK. Please, be sure audo has correct extension (OGG is preferred). Learn more: https://vk.com/dev/upload_files_2");

                    $save = $this->api('docs.save', [
                        'file' => $upload['file']
                    ], true);

                    $ret[] = "audio_message".$save["response"]["audio_message"]['owner_id']."_".$save["response"]["audio_message"]['id'];
                    break;
                }



                throw new VKDriverException("Uploading audio is restricted by VK API");
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
                    throw new VKDriverException("Can't upload file to VK. Please, be sure file has correct extension.");


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
     * @throws VKDriverException
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
     * @return array
     * @throws VKDriverException
     */
    public function sendRequest($endpoint, array $parameters, IncomingMessage $matchingMessage)
    {
        return $this->api($endpoint, $parameters, true);
    }

    /**
     * Sending typing action
     *
     * @param IncomingMessage $matchingMessage
     * @return bool|void
     * @throws VKDriverException
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
     * @throws VKDriverException
     */
    public function markSeen(IncomingMessage $matchingMessage)
    {
        $messageObject = $this->extractPrivateMessageFromPayload($matchingMessage->getPayload());
        if ($messageObject === false) {
            throw new VKDriverException('Cannot extract message from events of type ' . $matchingMessage->getPayload()->get['type']);
        }
        $messageId = $messageObject['id'];

        if($this->isConversation()){
            // Worked only with conversations created by the community
            return $this->api("messages.markAsRead", [
                "start_message_id" => $messageId,
                "mark_conversation_as_read" => 1,
                "peer_id" => $matchingMessage->getRecipient()
            ]);
        }

        // message_ids is deprecated
        return $this->api("messages.markAsRead", [
            "start_message_id" => $messageId,
            "peer_id" => $matchingMessage->getRecipient()
        ]);
    }


    /**
     * Executing all api requests via this method
     *
     * @param string $method
     * @param array $post_data
     * @param bool $asArray
     * @throws VKDriverException
     * @return Response|array
     */
    public function api($method, $post_data, $asArray = false)
    {

        $post_data += [
            "v" => $this->config->get("version"),
            "access_token" => $this->config->get("token")
        ];

        $response = $this->http->post($this->config->get("endpoint").$method, [], $post_data, [], false);

        //TODO: use Laravel-native value prettifying method (?)
        if(!$response->isOk())
            throw new VKDriverException("VK API said error. Response:\n".print_r($response, true));

        if(json_decode($response->getContent(),true) === false)
            throw new VKDriverException("VK API returned incorrect JSON-data. Response:\n".print_r($response, true));

        $json = json_decode($response->getContent(),true);

        if(isset($json["error"]))
            throw new VKDriverException("VK API returned error when processing method '{$method}': {$json["error"]["error_msg"]}. Response:\n".print_r($response, true));

        if($asArray)
            return $json;

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

        //TODO: upload with Laravel-native tools (?)
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

    /**
     * Retrieves a private message from payload.
     *
     * @param ParameterBag $payload
     * @return false|array Private message (https://vk.com/dev/objects/message) as array or false
     */
    private function extractPrivateMessageFromPayload(ParameterBag $payload)
    {
        switch ($payload->get("type")) {
            case "message_new":
                return $this->payload->get("object")["message"];

            case "message_reply":
            case "message_edit":
                return $this->payload->get("object");

            default:
                return false;
        }
    }

}