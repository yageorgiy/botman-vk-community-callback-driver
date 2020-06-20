<?php
namespace BotMan\Drivers\VK\Events;

class PollVoteNew extends VKEvent
{
    /**
     * Return the event name to match.
     *
     * @return string
     */
    public function getName()
    {
        return 'poll_vote_new';
    }
}