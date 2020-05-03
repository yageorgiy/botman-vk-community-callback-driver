# BotMan VK Community Callback driver
BotMan driver to connect VK Community with [BotMan](https://github.com/botman/botman) via Callback API.

## Support
Table of driver's features:

|Feature|Is Supported|
| --- | --- |
|Sending text messages|✔ Fully supported|
|Sending images|✔ Fully supported|
|Sending videos|❌ Not supported yet|
|Sending documents|❌ Not supported yet|
|Sending links|❌ Not supported yet|
|Sending locations|❌ Not supported yet|
|Sending stickers|❌ Not supported yet|
|Sending voice messages|❌ Not supported yet|
|Sending keyboards|⚠ Partially supported|
|Typing status|✔ Fully supported|
|Retrieving user data|✔ Supported *|
|Usage in VK conversations|⚠ Partially supported|
|Switching language|❌ Not supported yet|
|Multiple communities handling|❌ Not supported yet|

\* \- Can access the following user fields: id, first_name, last_name, is_closed, can_access_closed, photo_id, verified, sex, bdate, city, country, photo_50, photo_100, photo_200, photo_max, online, status, nickname, can_post, can_see_all_posts, can_see_audio, can_write_private_message, can_send_friend_request, is_favorite, is_hidden_from_feed, timezone, blacklisted, blacklisted_by_me, can_be_invited_group

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
        "group_id" => "REPLACE_ME"                  // Community or group ID
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

    // Build message object
    $message = OutgoingMessage::create('Here it is!')
        ->withAttachment($attachment);

    // Reply message object
    $bot->reply($message);
});
```

![Example image](https://i.imgur.com/XVLQn1f.png)

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



## See also
- [VK documentation for developers](https://vk.com/dev/callback_api)
- [BotMan documentation](https://botman.io/2.0/welcome)

## Contributing
Contributions are welcome, I would be glad to accept contributions via Pull Requests. Of course, everyone will be mentioned in contributors list. 🙂

## License
VK Community Callback driver is made under the terms of MIT licence. BotMan is free software distributed under the terms of the MIT license.