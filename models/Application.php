<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Application extends ActiveRecord {
    public static function tableName() {
        return 'applications';
    }

    public function rules() {
        return [
            [['user_id', 'problem_text', 'room_number', 'phone'], 'required'],
            [['user_id'], 'integer'],
            [['problem_text'], 'string'],
            [['room_number', 'phone'], 'string', 'max' => 50]
        ];
    }

    public function attributeLabels() {
        return [
            'user_id' => 'ID пользователя',
            'problem_text' => 'Проблема',
            'room_number' => 'Кабинет',
            'phone' => 'Телефон'
        ];
    }
}
?>