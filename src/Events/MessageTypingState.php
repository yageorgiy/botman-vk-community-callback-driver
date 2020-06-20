<?php
namespace BotMan\Drivers\VK\Events;

class MessageTypingState extends VKEvent
{

    /*
     * Example:
     {
        "type": "message_typing_state",
        "object": {
            "state": "typing",
            "from_id": 1234567,
            "to_id": -1234567
        },
        "group_id": 1234567,
        "event_id": "1a2b3c4d5e6f7g8h9k0l",
        "secret": "1234567890"
     }
     *
     *
     */

    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'message_typing_state';
    }
}