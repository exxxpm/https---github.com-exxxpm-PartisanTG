<?php
namespace app\controllers;

use yii\web\Controller;
use services\TelegramService;
use services\ApiService;
use app\models\Application;
use app\models\UserState;
use Yii;

class BotController extends Controller {
    private $telegram;
    private $api;

    public function __construct() {
        $this->telegram = new TelegramService();
        $this->api = new ApiService();
    }

    public function handleRequest($update) {
        $chatId = $update['message']['chat']['id'] ?? $update['callback_query']['from']['id'] ?? null;
        $text = $update['message']['text'] ?? '';
        $contact = $update['message']['contact']['phone_number'] ?? null;
        $callbackData = $update['callback_query']['data'] ?? null;

        $username = $update['message']['chat']['username'] ?? '';
        $firstName = $update['message']['chat']['first_name'] ?? '';
        $lastName = $update['message']['chat']['last_name'] ?? '';

        if (!$chatId) {
            Yii::error("Не удалось получить chat_id!", 'telegram');
            return;
        }

        $userState = UserState::getUserState($chatId);

        if ($callbackData === 'leave_request') {
            $this->telegram->sendMessage($chatId, "Опишите проблему:");
            $userState->step = 'waiting_for_problem';
            $userState->save();
            return;
        }

        if ($text === "/start" || !$userState->step) {
            $keyboard = [
                'reply_markup' => [
                    'inline_keyboard' => [
                        [['text' => 'Оставить заявку', 'callback_data' => 'leave_request']]
                    ]
                ]
            ];

            $this->telegram->sendMessage($chatId, "Здравствуйте! Что-то не так? Оставьте заявку, и мы разберемся!", $keyboard);
            $userState->step = 'idle';
            $userState->save();
            return;
        }

        switch ($userState->step) {
            case 'waiting_for_problem':
                $userState->problem_text = $text;
                $userState->step = 'waiting_for_room';
                $this->telegram->sendMessage($chatId, "Какой кабинет?");
                break;

            case 'waiting_for_room':
                $userState->room_number = $text;
                $userState->step = 'waiting_for_phone';

                $keyboard = [
                    'reply_markup' => json_encode([
                        'keyboard' => [
                            [['text' => '📱 Отправить мой контакт', 'request_contact' => true]]
                        ],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ])
                ];

                $this->telegram->sendMessage($chatId, "📞 Ваш контактный номер телефона? (пример: +7XXXXXXXXXX или 8XXXXXXXXXX)", $keyboard);
                break;

            case 'waiting_for_phone':
                if (!empty($contact)) {
                    // Контакт передан через Telegram — принимаем без проверки
                    $phone = preg_replace('/\D/', '', $contact);
                } else {
                    $phone = preg_replace('/\D/', '', $text);

                    // Проверяем, начинается ли номер с +7 или 8 и состоит ли из 11 цифр
                    if (!preg_match('/^(?:\+7|8)\d{10}$/', $text)) {
                        $this->telegram->sendMessage($chatId, "❌ Введите номер телефона в формате +7XXXXXXXXXX или 8XXXXXXXXXX.");
                        return;
                    }

                    // Если номер начинается с +7, заменяем на 8
                    if (strpos($phone, '7') === 1) {
                        $phone = '8' . substr($phone, 2);
                    }
                }

                $userState->phone = $phone;
                $userState->step = 'completed';

                // Очищаем клавиатуру после ввода номера
                $this->telegram->sendMessage($chatId, "✅ Ваша заявка зарегистрирована!", [
                    'reply_markup' => json_encode(['remove_keyboard' => true])
                ]);

                $application = new Application([
                    'user_id' => $chatId,
                    'username' => $username,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'problem_text' => $userState->problem_text,
                    'room_number' => $userState->room_number,
                    'phone' => $userState->phone
                ]);

                $this->api->sendApplication($application);

                $groupId = Yii::$app->params['TELEGRAM_GROUP_ID'];

                if (!$groupId) {
                    Yii::error("Не удалось получить TELEGRAM_GROUP_ID!", 'telegram');
                    return;
                }

                $message = "📌 *Новая заявка*\n"
                    . "👤 *Пользователь*: {$this->escapeMarkdownV2($firstName)} \\(@{$this->escapeMarkdownV2($username)}\\)\n"
                    . "📍 *Кабинет*: {$this->escapeMarkdownV2($userState->room_number)}\n"
                    . "📞 *Телефон*: {$this->escapeMarkdownV2($userState->phone)}\n"
                    . "📝 *Проблема*: {$this->escapeMarkdownV2($userState->problem_text)}";

                $this->telegram->sendMessage($groupId, $message, ['parse_mode' => 'MarkdownV2']);

                $userState->delete();
                return;
        }

        $userState->save();
    }

    private function escapeMarkdownV2($text) {
        $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
        return str_replace($specialChars, array_map(fn($char) => "\\" . $char, $specialChars), $text);
    }
}
?>