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
API_KEY=your-api-key
PROCESSLIST_ITEM_MIN=5
TELEGRAM_BOT_TOKEN=telegram-bot-token
```
- Set an `API_KEY` for your project to validate every request.
- Set a minimum limit of Database `PROCESSLIST_ITEM_MIN` by which system can determine whether to send bot alert message or not.
- Set `TELEGRAM_BOT_TOKEN`. You can create BOT from [here](https://t.me/botfather)

### Usage
There are two api's  
**POST**: [www.example.com/api/monitor/check-domain](#)  
**POST**: [www.example.com/api/monitor/check-db](#)

##### Example of */api/monitor/check-domain*
```php
$response = Http::post('www.example.com/api/monitor/check-domain', [
    'api_key' => 'your-api-key',
    'telegram_chat_id' => 123456,
    'telegram_bot_token' => 'token', // Optional
    'domain_list' => [
        ['domain' => 'www.google.com', 'port' => 80],
        ['domain' => 'www.example.com', 'port' => 800],
    ]
]);
```

##### Example of */api/monitor/check-db*
```php
$response = Http::post('www.example.com/api/monitor/check-db', [
    'api_key' => 'your-api-key',
    'telegram_chat_id' => 123456,
    'telegram_bot_token' => 'token', // Optional
]);
```

> How to get Chat ID in telegram?  
> If you want to send message to specific user, forward any message from the user to [userinfobot](https://t.me/userinfobot).  
> If you want to send message to specific group, add [RawDataBot](https://t.me/RawDataBot) to the group.
