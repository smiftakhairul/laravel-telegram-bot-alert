### Installation
```sh
git clone
composer install
```
Setup database and update credentials in `.env` only if you want to manage logs and run the following.
```php
php artisan migrate
```
Copy `.env` file from `.env.example`
```sh
cp .env.example .env
```

### Steps
Some configuration need to be updated in the environment `.env` file.
```php
APP_TITLE=your-app-title
API_KEY=your-api-key
TELEGRAM_CHAT_ID=11111,22222
PROCESSLIST_ITEM_MIN=5
SPACE_ALLOC_PERCENT_MAX=70
TELEGRAM_BOT_TOKEN=telegram-bot-token
ENABLE_MONITOR_LOG=true
```
- Set an `APP_TITLE` for your app.
- Set an `API_KEY` for your project to validate every request.
- Set multiple `TELEGRAM_CHAT_ID` with comma separated, just use id without comma for single.
- Set a minimum limit of Database `PROCESSLIST_ITEM_MIN` by which system can determine whether to send bot alert message or not.
- Set `SPACE_ALLOC_PERCENT_MAX` by which system can determine whether drive space is used more that it and send bot alert message.
- Set `TELEGRAM_BOT_TOKEN`. You can create BOT from [here](https://t.me/botfather)

### Usage
Provided API's:  
**POST**: [www.example.com/api/monitor/send-message](#)  
**POST**: [www.example.com/api/monitor/check-domain](#)  
**POST**: [www.example.com/api/monitor/check-db](#)  
**POST**: [www.example.com/api/monitor/check-directory](#)

##### Example of */api/monitor/send-message*
```php
$response = Http::post('www.example.com/api/monitor/check-domain', [
    'api_key' => 'your-api-key',
    'telegram_chat_id' => [11111, 22222], // Optional
    'telegram_bot_token' => 'token', // Optional
    'message' => ['Message 1', 'Message 2'],
]);
```

##### Example of */api/monitor/check-domain*
```php
$response = Http::post('www.example.com/api/monitor/check-domain', [
    'api_key' => 'your-api-key',
    'telegram_chat_id' => [11111, 22222], // Optional
    'telegram_bot_token' => 'token', // Optional
    'domain_list' => [
        ['domain' => 'www.google.com', 'port' => 80],
        ['domain' => 'www.example.com', 'port' => 800],
    ],
    'title' => 'app-title-otf', // Optional
]);
```

##### Example of */api/monitor/check-db*
```php
$response = Http::post('www.example.com/api/monitor/check-db', [
    'api_key' => 'your-api-key',
    'telegram_chat_id' => [11111, 22222], // Optional
    'min_processlist_item' => 5, // Optional
    'telegram_bot_token' => 'token', // Optional
    'title' => 'app-title-otf', // Optional
]);
```

##### Example of */api/monitor/check-directory*
```php
$response = Http::post('www.example.com/api/monitor/check-directory', [
    'api_key' => 'your-api-key',
    'telegram_chat_id' => [11111, 22222], // Optional
    'telegram_bot_token' => 'token', // Optional
    'directory_list' => [
        '/var/www/html', '/home', 'D:'
    ],
    'max_alloc_space_percent' => 70, // Optional
    'title' => 'app-title-otf', // Optional
]);
```

> How to get Chat ID in telegram?  
> If you want to send message to specific user, forward any message of the user to [userinfobot](https://t.me/userinfobot).  
> If you want to send message to specific group, add [RawDataBot](https://t.me/RawDataBot) to the group.

