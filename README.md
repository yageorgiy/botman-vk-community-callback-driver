# BotMan VK Community Callback driver

BotMan driver to connect VK Community with [BotMan](https://github.com/botman/botman) via Callback API.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Packagist](https://img.shields.io/packagist/v/yageorgiy/botman-vk-community-callback-driver.svg)](https://packagist.org/packages/yageorgiy/botman-vk-community-callback-driver)

## Support
Table of driver's features:

|Feature|Is Supported|
| --- | --- |
|Sending text messages|✔ Fully supported|
|Sending images|✔ Supported (no titles for images provided by VK API, pictures can't be uploaded to custom albums with community token)*|
|Sending videos|⚠ Partially supported (uploading videos with community token is not supported by VK API)*|
|Sending audio|⚠ Partially supported (uploading audio is restricted by VK API)|
|Sending voice messages|✔ Fully supported (as `Audio` object with `addExtras('vk_as_voice', true)`)|
|Sending documents (files)|✔ Supported (any of *.mp3 and executable files are restricted by the platform to be uploaded)|
|Sending links|⚠ Partially supported (via sending a low-level API request, under construction)|
|Sending locations|⚠ Partially supported (via sending a low-level API request, under construction)|
|Sending stickers|⚠ Partially supported (via sending a low-level API request, under construction)|
|Sending keyboards|⚠ Partially supported (under construction)|
|Listening for images|✔ Supported (no titles for images provided by VK API)|
|Listening for videos|⚠ Partially supported (no video URL provided by VK API, info of copyrighted videos can be unavailable via API)*|
|Listening for audio|✔ Fully supported|
|Listening for files|✔ Fully supported|
|Listening for locations|✔ Fully supported|
|Listening for voice messages|❌ Not supported yet|
|Receiving messages with mixed attachments|✔ Fully supported|
|Typing status|✔ Fully supported|
|Mark seen|✔ Fully supported|
|Retrieving user data|✔ Fully supported (use `VK_USER_FIELDS` property for retrieving custom user fields)|
|Usage in VK conversations|⚠ Partially supported (under construction)|
|Multiple communities handling|❌ Not supported yet|
|VK API low-level management|✔ Fully supported|
|Events listener|✔ Fully supported (as for 20.06.2020)|

\* \- uploading feature with user token is under construction

## Setup
### Getting the Community API key
From the page of your community, go to `Manage -> Settings tab -> API usage -> Access tokens tab`. Click `Create token` button.

![API usage](https://i.imgur.com/LqSm5Fy.png)

Then tick all the permissions in the dialog box.

![Dialog box with permissions](https://i.imgur.com/XDwA7JA.png)

Copy your created token by clicking `Show` link.

![Firstly added API token](https://i.imgur.com/OHhiMHA.png)

### Mounting the bot
From the page of your community, go to `Manage -> Settings tab -> API usage -> Callback API tab`:

- Choose `5.103` API version.
- Fill the required field of URL address of your's bot mount (examples: https://example.com/botman, http://some.mysite.ru/botman).
- Fill the Secret key field *(required for driver!)*. Later fill the `VK_SECRET_KEY` property with this value.
- Click `Confirm` button.

![Callback API tab](https://i.imgur.com/Du7jSug.png)

### Installing the driver
Require the driver via composer:
```bash
composer require yageorgiy/botman-vk-community-callback-driver
```

If you're using BotMan Studio, you should define in the `.env` file the following properties:

```dotenv
VK_ACCESS_TOKEN="REPLACE_ME"                    # User or community token for sending messages (from Access tokens tab, see above)
VK_SECRET_KEY="REPLACE_ME"                      # Secret phrase for validating the request sender (from Callback API tab, see above)
VK_API_VERSION=5.103                            # API version to be used for sending an receiving messages (should be 5.103 and higher) (not recommended to change)
VK_MESSAGES_ENDPOINT=https://api.vk.com/method/ # VK API endpoint (don't change it if unnecessary)
VK_CONFIRM=                                     # DEPRECATED SINCE v.1.4.2, LEAVE BLANK (EMPTY STRING) - see 'Confirming the bot' section. Confirmation phrase for VK
VK_GROUP_ID="REPLACE_ME"                        # Community or group ID
VK_USER_FIELDS=                                 # Extra user fields (see https://vk.com/dev/fields for custom fields) (leave blank for no extra fields)
```

If you don't use BotMan Studio, the driver should be applied manually:
```php
// ...

// Applying driver
DriverManager::loadDriver(\BotMan\Drivers\VK\VkCommunityCallbackDriver::class);

// Applying settings for driver
BotManFactory::create([
    "vk" => [
        "token" => "REPLACE_ME",                    // User or community token for sending messages (from Access tokens tab, see above)
        "secret" => "REPLACE_ME",                   // Secret phrase for validating the request sender (from Callback API tab, see above)
        "version" => "5.103",                       // API version to be used for sending an receiving messages (should be 5.103 and higher) (not recommended to change)
        "endpoint" => "https://api.vk.com/method/", // VK API endpoint (don't change it if unnecessary)
        "confirm" => "",                            // DEPRECATED SINCE v.1.4.2, LEAVE BLANK (EMPTY STRING) - see 'Confirming the bot' section. Confirmation phrase for VK
        "group_id" => "REPLACE_ME",                 // Community or group ID
        "user_fields" => ""                         // Extra user fields (see https://vk.com/dev/fields for custom fields) (leave blank for no extra fields)
    ]
]);

// ...
```

### Confirming the bot

**⚠ \[Important note\] Migration from v.1.4.1 and older.** Method of confirming the bot has changed since driver version 1.4.2: validation should be managed by using events listener, `VK_SECRET_KEY` (or `$botmanSettings["vk"]["confirm"]`) should be blank (empty string).

- Find the string (validation code) in section `String to be returned`:

![Callback API tab](https://i.imgur.com/2HoB6lu.png)

- Add the following code to `routes/botman.php` file, replace `REPLACE_ME` with the validation code (e.g. `1a2b3c4d5e`):

```php
$botman->on("confirmation", function($payload, $bot){
    // Use $payload["group_id"] to get group ID if required for computing the passphrase.
    echo("REPLACE_ME");
});
```

- Click `Confirm` button.

## Usage examples
*In usage examples, the used file is `routes/botman.php`.*

### Sending simple message
If bot receives `Hello` message, it will answer `Hi, <First Name>`:
```php
$botman->hears('Hello', function ($bot) {
    $bot->reply('Hi, '.$bot->getUser()->getFirstName());
});
```

![Example image](https://i.imgur.com/EemEq8u.png)

### Typing activity
Bot will wait 10 seconds before answering the question:

```php
$botman->hears("What\'s your favourite colour\?", function ($bot) {
    $bot->reply('Let me think...');
    $bot->typesAndWaits(10);
    $bot->reply("I guess it's orange! 😄");
});
```

![Example image](https://i.imgur.com/2GsW7Iz.png)

After all, it will answer:

![Example image](https://i.imgur.com/NR2zg2q.png)

### Attaching image
If bot receives `Gimme some image` message, it will answer `Here it is!` with an attached image:

```php
$botman->hears('Gimme some image', function ($bot) {
    // Create attachment
    $attachment = new Image('https://botman.io/img/logo.png');
    // $attachment->addExtras("vk_photo", "photo123456_123456"); // Or send an already uploaded photo (driver will ignore image url)    

    // Build message object
    $message = OutgoingMessage::create('Here it is!')
        ->withAttachment($attachment);

    // Reply message object
    $bot->reply($message);
});
```

![Example image](https://i.imgur.com/XVLQn1f.png)

### Attaching video

Example of sending an already uploaded video:

**Note**: uploading videos to VK is not supported by the driver yet.

```php
$botman->hears('Gimme some video', function ($bot) {
    // Create attachment
    $attachment = new Video('http://unused-video-url');
    // Attaching already uploaded videos is the ONLY way to send them (as for now):
    $attachment->addExtras("vk_video", "video-2000416976_41416976"); // Send an already uploaded video (driver will ignore video url)

    // Build message object
    $message = OutgoingMessage::create('Here it is!')
        ->withAttachment($attachment);

    // Reply message object
    $bot->reply($message);
});
```

![Example image](https://i.imgur.com/dPGi4w6.png)

### Attaching audio

Example of sending an already uploaded audio:

**Note**: uploading audio to VK is restricted by the platform.

```php
$botman->hears('Gimme some audio', function ($bot) {
    // Create attachment
                            // URL can be uploaded ONLY as voice message (due to restrictions of VK)
    $attachment = new Audio('https://unused-audio-url');
    $attachment->addExtras("vk_audio", "audio371745438_456268888"); // Send an already uploaded audio (driver will ignore audio url and vk_as_voice parameter)

    // Build message object
    $message = OutgoingMessage::create('Here it is!')
        ->withAttachment($attachment);

    // Reply message object
    $bot->reply($message);
});
```

### Sending voice message

Voice messages can be send using `Audio` with extra parameter `vk_as_voice = true`. 

Example of sending a voice message with message text:

**Note**: better to upload an *.ogg file rather than *.mp3, *.wav and others. See [Uploading Voice Message](https://vk.com/dev/upload_files_3) for more info.

```php
$botman->hears('Sing me a song', function ($bot) {
    // Create attachment
                            // URL can be uploaded ONLY as voice message (due to restrictions of VK)
    $attachment = new Audio('https://url-to-ogg-file');
//  $attachment->addExtras("vk_audio", "audio371745438_456268888"); // Send an already uploaded audio (driver will ignore audio url and vk_as_voice parameter)
    $attachment->addExtras("vk_as_voice", true);                    // Send as voice message (better to use *.ogg file)

    // Build message object
    $message = OutgoingMessage::create('Well...')
        ->withAttachment($attachment);

    // Reply message object
    $bot->reply($message);
});
```

![Example image](https://i.imgur.com/ZqQS8tD.png)

### Attaching document (file)

Example of sending file:

**Note**: not all files are available to upload. See [Uploading documents](https://vk.com/dev/upload_files_2?f=10.%2BUploading%2BDocuments) for more info.

```php
$botman->hears("Any files\?", function ($bot) {
    $attachment = new File('https://url-to-file');
//  $attachment->addExtras("vk_doc", "doc123456_123456"); // Send an already uploaded document (driver will ignore audio url and vk_doc_title, vk_doc_tags parameters)
    $attachment->addExtras("vk_doc_title", "Cool guy.gif"); // Title
    $attachment->addExtras("vk_doc_tags", "cool, guy"); // Document tags

    // Build message object
    $message = OutgoingMessage::create('Yep!')
        ->withAttachment($attachment);

    // Reply message object
    $bot->reply($message);
});
```

![Example image](https://i.imgur.com/MiFD3wm.png)

### Sending simple keyboard

Example of sending simple keyboard (**getting keyboard event is not completed yet**). Keyboard will be shown as **`one_time = true`** (shown once) and **`inline = false`** (default non-inline keyboard). Customization of this parameters is under construction, too.

```php
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
$botman->hears("What can you do\?", function ($bot) {
    $question = Question::create('Ha-ha! Lots of!')
        ->addButtons([
            Button::create('Function 1')->value('f1'),
            Button::create('Function 2')->value('f2'),
            Button::create('Function 3')->value('f2'),
            Button::create('Function 4')->value('f2'),
            Button::create('Function 5')->value('f3')
        ]);

    $bot->ask($question, function ($answer) {
        // Detect if button was clicked (UNDER CONSTRUCTION!):
        if ($answer->isInteractiveMessageReply()) {
            $selectedValue = $answer->getValue(); // will be like 'f1', 'f2', ...
            $selectedText = $answer->getText(); // will be like 'Function 1', 'Function 2', ...
        }
    });
});
```

![Example image](https://i.imgur.com/DBUmbE4.png)

**NOTE**: better to send keyboards only in Conversation class, asking a question with buttons. See more [here](https://botman.io/2.0/conversations).

### Customizing the keyboard

You can also change button's properties via additional parameters such as colour and position **(X and Y coords are 1-based!)**:

```php
//...
$botman->hears("What can you do\?", function ($bot) {
    $question = Question::create('Ha-ha! Lots of!')
        ->addButtons([
            Button::create('Function 1')->value('f1')->additionalParameters([
                // Button features
                "__x" => 1, // X position, won't be sent to VK (local only), 1-based!
                "__y" => 1, // Y position, won't be sent to VK (local only), 1-based!
                "color" => "secondary" // Colour (see available colours here - https://vk.com/dev/bots_docs_3)
            ]),
            Button::create('Function 2')->value('f2')->additionalParameters([
                "__x" => 1,
                "__y" => 2,
                "color" => "negative"
            ]),
            Button::create('Function 3')->value('f2')->additionalParameters([
                "__x" => 1,
                "__y" => 3,
                "color" => "primary"
            ])
        ]);

    $bot->ask($question, function ($answer) {
        //...
    });
});
```

![Example image](https://i.imgur.com/wcTWALB.png)

See [VK documentation page](https://vk.com/dev/bots_docs_3) for available colours, types and other features. Just add new fields in array of additional parameters as it is shown in the example above.

### Listening for images

Native way for receiving images.

**Note**: no message text will be provided via `receivesImages()` method.

```php
$botman->receivesImages(function($bot, $images) {
    foreach ($images as $image) {
        $url = $image->getUrl(); // The direct url
        $title = $image->getTitle(); // The title (empty string as titles are not supported by VK)
        $payload = $image->getPayload(); // The original payload

        $bot->reply("Detected image: {$url}");
    }
});
```

![Example image](https://i.imgur.com/ETJBzzN.png)

### Listening for videos

Native way for receiving videos.

**Note**: no message text will be provided via `receivesVideos()` method.

```php
$botman->receivesVideos(function($bot, $videos) {
    foreach ($videos as $video) {
        $url = $video->getUrl(); // The direct url
        $payload = $video->getPayload(); // The original payload

        // For YouTube videos title can be accessed in the following way:
        $bot->reply("Detected video: {$payload["title"]}");
    }
});
```

![Example image](https://i.imgur.com/w2pVLNJ.png)

### Listening for audio

Native way for receiving audio.

**Note**: no message text will be provided via `receivesAudio()` method.

```php
$botman->receivesAudio(function($bot, $audios) {
    foreach ($audios as $audio) {
        $url = $audio->getUrl(); // The direct url
        $payload = $audio->getPayload(); // The original payload

        $bot->reply("Detected audio: {$url}");
    }
});
```

![Example image](https://i.imgur.com/6T48P04.png)

### Listening for documents (files)

Native way for receiving files.

**Note**: no message text will be provided via `receivesFiles()` method.

```php
$botman->receivesFiles(function($bot, $files) {
    foreach ($files as $file) {
        $url = $file->getUrl(); // The direct url
        $payload = $file->getPayload(); // The original payload

        $bot->reply("Detected file (document): {$url}");
    }
});
```

![Example image](https://i.imgur.com/BszRFg6.png)

### Listening for location

Native way for receiving location.

**Note**: no message text will be provided via `receivesLocation()` method.

```php
$botman->receivesLocation(function($bot, $location) {
    $lat = $location->getLatitude();
    $lng = $location->getLongitude();

    $bot->reply("Detected location: $lat $lng");
});
```

![Example image](https://i.imgur.com/tOl4hYn.png)

### Receiving messages with mixed attachments

Message with mixed attachments can be asked via `hears()`, `ask()` or `fallback()` method (`IncomingMessage` with message text and attachments with all supported types).

Example with video and image attachments:

```php
$botman->hears('I have both image and video for you.', function ($bot) {
    $bot->reply("Cool!");

    // Scanning for images
    $images = $bot->getMessage()->getImages() ?? [];
    foreach ($images as $image) {

        $url = $image->getUrl();

        $bot->reply("Image found: {$url}");
    }

    // Scanning for videos
    $videos = $bot->getMessage()->getVideos() ?? [];
    foreach ($videos as $video) {
        $payload = $video->getPayload();

        $bot->reply("Video found: {$payload["title"]}");
    }
});
```

![Example image](https://i.imgur.com/f8FYnTt.png)

### Retrieving extra user data

Extra user fields should be defined in `.env` file and can be accessed via `getUser()->getInfo()` method.

Example contents of `.env`:

```dotenv
# ...
VK_USER_FIELDS="photo_200_orig"
# ...
```

Example route:

```php
$botman->hears('Gimme my photo_200_orig', function ($bot) {
    $bot->reply('Here it is: '.$bot->getUser()->getInfo()["photo_200_orig"]);
});
```

![Example image](https://i.imgur.com/SlO8aTy.png)

Multiple fields should be comma-separated:

```dotenv
# ...
VK_USER_FIELDS="photo_200_orig, photo_50"
# ...
```

See [User object](https://vk.com/dev/fields) for available fields.


### Retrieving extra client information

Information about supported features of user's VK client can be accessed via `$bot->getMessage()->getExtras("client_info")`:

**Note:** the feature works only with new messages sent (`message_new` event).

```php
$botman->hears('my info', function(BotMan $bot) {
    // Prints raw "client_info" array
    $bot->reply(print_r($bot->getMessage()->getExtras("client_info"), true));
});
```

![The reply](https://i.imgur.com/cxINTnA.png)


See [Information about features available to the user](https://vk.com/dev/bots_docs?f=2.3.%20Information%20about%20features%20available%20to%20the%20user) for more details.


### Mark seen example

Every message will be marked as seen even if there is no response for it:

```php
$botman->hears("Don\'t answer me", function ($bot) {
    // Do nothing
});
```

![Example image](https://i.imgur.com/pt1gwqA.png)

### Listening to events

List of supported events:
- `confirmation`
- `message_allow`
- `message_deny`
- `message_typing_state`        **
- `photo_new`
- `photo_comment_new`
- `photo_comment_edit`
- `photo_comment_restore`
- `photo_comment_delete`
- `audio_new`
- `video_new`
- `video_comment_new`
- `video_comment_edit`
- `video_comment_restore`
- `video_comment_delete`
- `wall_post_new`
- `wall_repost`
- `wall_reply_new`
- `wall_reply_edit`
- `wall_reply_restore`
- `wall_reply_delete`
- `board_post_new`
- `board_post_edit`
- `board_post_restore`
- `board_post_delete`
- `market_comment_new`
- `market_comment_edit`
- `market_comment_restore`
- `market_comment_delete`
- `market_order_new`            *
- `market_order_edit`           *
- `group_leave`
- `group_join`
- `user_block`
- `user_unblock`
- `poll_vote_new`
- `group_officers_edit`
- `group_change_settings`
- `group_change_photo`
- `vkpay_transaction`           *
- `app_payload`                 *
- `like_add`                    *
- `like_remove`                 *

\* - missing english version in VK docs, but feature exists (as for 20.06.2020)

\*\* - missing in VK docs, but feature exists (as for 20.06.2020)

**Note:** events of `message_new`, `message_reply`, `message_edit` are assessable via Hearing Messages functions (e.g. `$botman->hear()`).

[Full list of events (VK docs)](https://vk.com/dev/groups_events)

Example of sending message when the typing state changed:

```php
$botman->on("message_typing_state", function($payload, $bot){
    // $payload is an array of the event object ("Object field format"),
    // excepting Confirmation event, where $payload contains full root JSON schema.
    // See https://vk.com/dev/groups_events for more info
    $bot->say("Hey! You're typing something!", $payload["from_id"]);
});
```

**Note:** `$bot->reply()` is not supported here, use `$bot->say("...", $peer_id_from_data)` instead.

The result:

![The result image](https://i.imgur.com/lNhp9si.png)

### Sending low-level API requests

Example of sending a sticker:

```php
$botman->hears('sticker', function($bot) {
    // API method
    $endpoint = "messages.send";
    
    // Arguments ("v" and "access_token" are set by driver, no need to define)
    $arguments = [
         "peer_id" => $bot->getUser()->getId(), // User ID
         "sticker_id" => 12, // Sticker ID
         "random_id" => rand(10000,100000) // Random ID (required by VK API, to prevent doubling messages)
    ];

    $test = $bot->sendRequest($endpoint, $arguments);
    // $test now equals to ["response" => 1234];
});
```

The result:

![The result of sticker sending](https://i.imgur.com/DyIjsww.png)

## See also
- [VK documentation for developers](https://vk.com/dev/callback_api)
- [BotMan documentation](https://botman.io/2.0/welcome)

## Contributing
Contributions are welcome, I would be glad to accept contributions via Pull Requests. Of course, everyone will be mentioned in the contributors list. 🙂

## License
VK Community Callback driver is made under the terms of MIT license. BotMan is free software distributed under the terms of the MIT license.