<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

return [
    'id' => 'telegram-bot-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'webhook/index',
    'components' => [
        'request' => [
            'cookieValidationKey' => 'V9yFgmKsV2xAjv6ctHmGvQLVxvLB3AKk',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'telegramService' => [
            'class' => services\TelegramService::class,
        ],
        'apiService' => [
            'class' => services\ApiService::class,
        ],
    ],
    'params' => $params,
];
?>
