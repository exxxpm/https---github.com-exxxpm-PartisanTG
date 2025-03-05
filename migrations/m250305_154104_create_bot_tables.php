<?php

use yii\db\Migration;

class m250305_154104_create_bot_tables extends Migration{
    /**
     * {@inheritdoc}
     */
    public function safeUp(){
        $this->createTable('applications', [
            'id' => $this->primaryKey(),
            'user_id' => $this->bigInteger()->notNull(),
            'username' => $this->string(255),
            'first_name' => $this->string(255),
            'last_name' => $this->string(255),
            'problem_text' => $this->text()->notNull(),
            'room_number' => $this->string(50),
            'phone' => $this->string(20),
            'status' => "ENUM('new', 'in_progress', 'done') DEFAULT 'new'",
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-applications-user_id', 'applications', 'user_id');

        $this->createTable('user_states', [
            'id' => $this->primaryKey(),
            'user_id' => $this->bigInteger()->notNull()->unique(),
            'step' => "ENUM('start', 'waiting_for_problem', 'waiting_for_room', 'waiting_for_phone', 'completed') NOT NULL DEFAULT 'start'",
            'problem_text' => $this->text()->null(),
            'room_number' => $this->string(50)->null(),
            'phone' => $this->string(20)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-user_states-user_id', 'user_states', 'user_id');
    }

    public function safeDown(){
        $this->dropTable('user_states');
        $this->dropTable('applications');
    }
}
?>