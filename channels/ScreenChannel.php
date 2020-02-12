<?php

namespace soareseneves\notifications\channels;

use Yii;
use soareseneves\notifications\Channel;
use soareseneves\notifications\Notification;

class ScreenChannel extends Channel
{
    public function send(Notification $notification)
    {
        $db = Yii::$app->getDb();
        $className = $notification->className();
        $currTime = time();
        $notificationData = $notification->getData();

        $db->createCommand()->insert('{{%notifications}}', [
            'class' => strtolower(substr($className, strrpos($className, '\\')+1, -12)),
            'title' => $notificationData['title'],
            'body' => $notificationData['body'],
            'icon_class' => $notification->iconClass,
            'click_action' => $notificationData['click_action'],
            'user_id' => $notification->user->id,
            'users_notification_id' => $notificationData['id'],
            'created_at' => $currTime,
        ])->execute();
    }

}
