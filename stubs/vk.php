<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VK Community (or User) Access Token
    |--------------------------------------------------------------------------
    |
    | Used for sending action requests to VK.
    |
    */
    "token" => env("VK_ACCESS_TOKEN"),

    /*
    |--------------------------------------------------------------------------
    | VK App Secret
    |--------------------------------------------------------------------------
    |
    | Verifying the non-fake request from VK
    |
    */
    "secret" => env("VK_SECRET_KEY"),

    /*
    |--------------------------------------------------------------------------
    | VK Callback Version
    |--------------------------------------------------------------------------
    |
    | VK Callback version (5.103 and newer only!)
    |
    */
    "version" => env("VK_API_VERSION"),

    /*
    |--------------------------------------------------------------------------
    | VK Endpoint
    |--------------------------------------------------------------------------
    |
    | VK endpoint URL for sending requests
    |
    */
    "endpoint" => env("VK_MESSAGES_ENDPOINT"),

    /*
    |--------------------------------------------------------------------------
    | VK Confirmation Pass-phrase (deprecated and no longer used by driver!)
    |--------------------------------------------------------------------------
    |
    | Used for validating the bot, is it responding or not.
    | Should be copied from the Callback API tab.
    |
    | [FEATURE IS DEPRECATED]
    | Leave it blank (empty string). Use $botman->on(); feature in routes/botman.php to echo confirmation pass-phrase:
    |
   // $botman->on("confirmation", function($payload, $bot){
   //   echo("CONFIRMATION_TOKEN_HERE");
   // });
    |
    */
    "confirm" => env("VK_CONFIRM", ""),

    /*
    |--------------------------------------------------------------------------
    | VK Group (Community) ID
    |--------------------------------------------------------------------------
    |
    | Integer value of community ID
    |
    */
    "group_id" => env("VK_GROUP_ID"),

    /*
    |--------------------------------------------------------------------------
    | VK Extra User fields
    |--------------------------------------------------------------------------
    |
    | Used when retrieving info about sender
    |
    */
    "user_fields" => env("VK_USER_FIELDS", "")
];