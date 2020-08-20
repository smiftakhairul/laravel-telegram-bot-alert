<?php

return [
    'api_key' => env('API_KEY', 'your-api-key'),
    'min_processlist_item' => env('PROCESSLIST_ITEM_MIN', 5),
    'max_space_alloc_percent' => env('SPACE_ALLOC_PERCENT_MAX', 70),
];
