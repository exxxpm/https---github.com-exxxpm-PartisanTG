<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class UserState extends ActiveRecord{
    public static function tableName(){
        return 'user_states';
    }

    public function rules(){
        return [
            [['user_id', 'step'], 'required'],
            [['user_id'], 'integer'],
            [['problem_text'], 'string'],
            [['room_number', 'phone'], 'string', 'max' => 50],
            [['step'], 'in', 'range' => ['start', 'waiting_for_problem', 'waiting_for_room', 'waiting_for_phone', 'completed']]
        ];
    }

    public static function getUserState($userId){
        $userState = self::findOne(['user_id' => $userId]);
        if (!$userState) {
            $userState = new self();
            $userState->user_id = $userId;
            $userState->step = 'idle';
            $userState->save();
        }
        return $userState;
    }
}
?>