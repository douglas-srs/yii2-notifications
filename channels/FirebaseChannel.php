<?php

namespace soareseneves\notifications\channels;

use Yii;
use soareseneves\notifications\Channel;
use soareseneves\notifications\Notification;
use soareseneves\firebase\FirebaseNotifications;
use app\models\Token;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

class FirebaseChannel extends Channel
{

	private function logFile($file, $text){
		$txt = print_r($text, true) . "\n";
		fwrite($file, $txt);
	}

    public function send(Notification $notification)
    {
        $service = new FirebaseNotifications(['authKey' => 'AAAALCabrjs:APA91bF4HFKFkdFtWmaAw1E7XjDYdvIybpbxnySO-HulnO_M3h25YL-nhIBLjd3nDMNgEU8-PCCs72IgrBRwb7mHM_1IvRQUjtUGgIdg5Ec9uAW7sCVDE50hGPU8aALv-EfMldT-lbnr']);

        $user_ids = $this->recipients($notification);

        $tokens = Token::find()->andFilterwhere([
				            'and',
				            ['in', 'user_id', $user_ids],
				            ['=', 'type', '4']
				        ])->asArray()->all();

        $tokens = ArrayHelper::getColumn($tokens, 'code');

        $notificationData = $notification->getData();

        $notificationData['notification_id'] = $notificationData['id'];
        unset($notificationData['id']);
        unset($notificationData['table_name']);
        unset($notificationData['table_id']);
        unset($notificationData['scheduled_date']);
        unset($notificationData['content']);

        $notificationData['url'] = \Yii::$app->urlManager->createAbsoluteUrl(json_decode($notificationData['click_action'], 'https'));

        $service->sendNotification($tokens, ['notification' => $notificationData, 'data' => $notificationData]);
    }

}
