<?php

namespace soareseneves\notifications\channels;

use Yii;
use yii\di\Instance;
use yii\base\InvalidConfigException;
use soareseneves\notifications\Channel;
use soareseneves\notifications\Notification;
use app\models\User;

class EmailChannel extends Channel
{
    /**
     * @var array the configuration array for creating a [[\yii\mail\MessageInterface|message]] object.
     * Note that the "to" option must be set, which specifies the destination email address(es).
     */
    public $message = [];

    /**
     * @var \yii\mail\MailerInterface|array|string the mailer object or the application component ID of the mailer object.
     * After the EmailChannel object is created, if you want to change this property, you should only assign it
     * with a mailer object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $mailer = 'mailer';

    public $viewPath = '@dektrium/user/views/mail';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->mailer = Instance::ensure($this->mailer, 'yii\mail\MailerInterface');
        $this->mailer->viewPath = $this->viewPath;
        $this->mailer->getView()->theme = Yii::$app->view->theme;
    }

    /**
     * Sends a notification in this channel.
     */
    public function send(Notification $notification)
    {
        $user_ids = $this->recipients($notification);

        if (!is_null($user_ids)){
            $fromUser = $notification->user;
            $notificationData = $notification->getData();

            foreach ($user_ids as $user_id) {
                $toUser = User::findOne($user_id);

                $emailParams = $notification->getEmailParams();

                if (isset($emailParams)){
                    if (is_array($emailParams)){
                        $emailParams = array_merge($emailParams, ['toUser' => $toUser, 'fromUser' => $fromUser, 'data' => (object)$notification->getData()]);
                    }
                } else {
                    $emailParams = ['toUser' => $toUser, 'fromUser' => $fromUser, 'data' => (object)$notification->getData()];
                }

                $message = $this->mailer->compose(['html' => '@app/views/notifications/mail/' . $notification->getEmailTemplate(), 'text' => '@app/views/notifications/mail/text/' . $notification->getEmailTemplate()], $emailParams);

                Yii::configure($message, $this->message);

                $message->setTo($toUser->email);
                $message->setSubject($notificationData['title']);
                $message->setTextBody($notificationData['body']);
                $message->send($this->mailer);
            }
        }
    }

}
