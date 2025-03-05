<?php
namespace services;

use Yii;
use GuzzleHttp\Client;

class ApiService {
    private $client;
    private $apiUrl;

    public function __construct() {
        $this->client = new Client();
        $this->apiUrl = Yii::$app->params['API_URL'] ?? null;

        if (!$this->apiUrl) {
            Yii::error("API_URL не задан!", 'application');
            throw new \Exception("API_URL не задан!");
        }
    }

    public function sendApplication($data) {
        try {
            $response = $this->client->post($this->apiUrl, [
                'json' => $data
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Yii::error("Ошибка при отправке заявки: " . $e->getMessage(), 'application');
            return false;
        }
    }
}

?>