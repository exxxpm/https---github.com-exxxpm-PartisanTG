<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use services\TelegramService;

class WebhookController extends Controller {
    private $telegramService;

    public function __construct($id, $module, TelegramService $telegramService, $config = []) {
        $this->telegramService = $telegramService;
        parent::__construct($id, $module, $config);
    }

    public function beforeAction($action) {
        if ($action->id === 'index') {
            Yii::$app->request->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex() {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $update = Yii::$app->request->bodyParams;
        
        Yii::info("Update received: " . json_encode($update, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'telegram');

        $botController = new \app\controllers\BotController();
        $botController->handleRequest($update);

        return ['status' => 'ok'];
    }
}
?>