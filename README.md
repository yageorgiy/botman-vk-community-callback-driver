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
|Sending documents|❌ Not supported yet|
|Sending links|❌ Not supported yet|
|Sending locations|❌ Not supported yet|
|Sending stickers|❌ Not supported yet|
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
|VK API low-level management|❌ Not supported yet|
|Events listener|❌ Not supported yet|

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
VK_CONFIRM="REPLACE_ME"                         # Confirmation phrase for VK (from Callback API tab, see above)
VK_GROUP_ID="REPLACE_ME"                        # Community or group ID
VK_USER_FIELDS=                                 # Extra user fields (see https://vk.com/dev/fields for custom fields) (left blank for no extra fields)
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
        "confirm" => "REPLACE_ME",                  // Confirmation phrase for VK (from Callback API tab, see above)
        "group_id" => "REPLACE_ME",                 // Community or group ID
        "user_fields" => ""                         // Extra user fields (see https://vk.com/dev/fields for custom fields) (left blank for no extra fields)
    ]
]);

// ...
```


## Usage examples
*In usage examples, the used file is `routes/botman.php`.*

### Sending a simple message
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

### Sending an image as an attachment
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

### Sending a video as an attachment

TODO

### Sending an audio as an attachment

TODO

### Sending a voice message

TODO

### Sending a message with simple keyboard

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

### Mark seen example

TODO

## See also
- [VK documentation for developers](https://vk.com/dev/callback_api)
- [BotMan documentation](https://botman.io/2.0/welcome)

## Contributing
Contributions are welcome, I would be glad to accept contributions via Pull Requests. Of course, everyone will be mentioned in contributors list. 🙂

## License
VK Community Callback driver is made under the terms of MIT license. BotMan is free software distributed under the terms of the MIT license.