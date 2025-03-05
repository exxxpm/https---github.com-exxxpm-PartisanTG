<?php
namespace services;

use Yii;
use GuzzleHttp\Client;

class TelegramService{
    private $client;
    private $botToken;
    private $groupId;

    public function __construct(){
        $this->client = new Client();
        $this->botToken = Yii::$app->params['TELEGRAM_BOT_TOKEN'];
        $this->groupId = Yii::$app->params['TELEGRAM_GROUP_ID'];

    }

    public function sendMessage($chatId, $text, $extra = []) {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
    
        if (!empty($extra)) {
            foreach ($extra as $key => $value) {
                if (!empty($value)) {
                    $data[$key] = $value;
                }
            }
        }
    
        Yii::info("Отправка в Telegram: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'telegram');
    
        $this->client->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'json' => $data
        ]);
    }    


    public function sendGroupMessage($text){
        $groupId = Yii::$app->params['TELEGRAM_GROUP_ID'];
        
        if (!$groupId) {
            Yii::error("Ошибка: TELEGRAM_GROUP_ID не задан!", 'telegram');
            return;
        }
    
        $this->sendMessage($groupId, $text);
    }    
}
?>