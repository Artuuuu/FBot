# FBot



## Quick Start
```php
<?php require __DIR__ . '/vendor/autoload.php';

$fbot = new Fajuu\FBot([
  "version" => "v2.6",
  "verify_token" => "####",
  "access_token" => "####",
  "locale_folder" => __DIR__."/locale",
]);
...
```



## Variables

### `$fbot->{variable}`
| Variable | Description |
|-----------|----------------------------------------------------|
| `input` | The data that Facebook sent out |
| `sender` | Sender ID |
| `recipient` | Recipient ID |
| `allow` | Did the user send the message to a bot? true/false |
| `data` | What you sent to Facebook |
| `response` | What you got from Facebook |



### `$fbot->received->{key}`

| Key | Type |
|:-|-|
| `text` | string (message) |
| `payload` | string (payload) |
| [`message`](https://developers.facebook.com/docs/messenger-platform/reference/webhook-events/messages) | array[] |
| [`postback`](https://developers.facebook.com/docs/messenger-platform/reference/webhook-events/messaging_postbacks) | array[] |
| [`quick_reply`](https://developers.facebook.com/docs/messenger-platform/reference/webhook-events/messages) | array[] |
| [`is_echo`](https://developers.facebook.com/docs/messenger-platform/reference/webhook-events/message-echoes) | boolean |
| [`is_read`](https://developers.facebook.com/docs/messenger-platform/reference/webhook-events/message-reads) | boolean |
| [`is_delivery`](https://developers.facebook.com/docs/messenger-platform/reference/webhook-events/message-deliveries) | boolean |
