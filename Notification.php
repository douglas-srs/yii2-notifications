<?php

namespace soareseneves\notifications;

use Yii;

/**
 * This is the base class for a notification.
 *
 * @property string $key
 * @property integer $userId
 * @property array $data
 */
abstract class Notification extends \yii\base\BaseObject
{
    public $user;

    public $publicNotification;

    public $excludeOwner;

    public $emailTemplate;

    public $emailParams = [];

    public $iconClass = 'fa fa-flag text-sw';

    public $data = [];

    /**
     * Create an instance
     *
     * @param string $key
     * @param array $params notification properties
     * @return static the newly created Notification
     * @throws \Exception
     */
    public static function create($params = []){
        if (isset($params['public_notification'])){
            $this->publicNotification = $params['public_notification'];
            unset($params['public_notification']);
        }
        if (isset($params['exclude_owner'])){
            $this->excludeOwner = $params['exclude_owner'];
            unset($params['exclude_owner']);
        }
        return new static($params);
    }

    /**
     * Determines if the notification can be sent.
     *
     * @param  \webzop\notifications\Channel $channel
     * @return bool
     */
    public function shouldSend($channel)
    {
        return true;
    }

    /**
     * Gets notification data
     *
     * @return array
     */
    public function getData(){
        return $this->data;
    }

    /**
     * Sets notification data
     *
     * @return self
     */
    public function setData($data = []){
        $this->data = $data;
        return $this;
    }

    /**
     * Gets the User
     *
     * @return User ActiveRecord
     */
    public function getUser(){
        return $this->user;
    }

    /**
     * Sets the User
     *
     * @return self
     */
    public function setUser($user){
        $this->user = $user;
        return $this;
    }

    /**
     * Gets the Privacy
     *
     * @return Int
     */
    public function getPublicNotification(){
        return $this->publicNotification;
    }

    /**
     * Sets the Privacy
     *
     * @return self
     */
    public function setPublicNotification($publicNotification){
        $this->publicNotification = $publicNotification;
        return $this;
    }

    /**
     * Gets Exclude Owner
     *
     * @return Int
     */
    public function getExcludeOwner(){
        return $this->excludeOwner;
    }

    /**
     * Sets Exclude Owner
     *
     * @return self
     */
    public function setExcludeOwner($excludeOwner){
        $this->excludeOwner = $excludeOwner;
        return $this;
    }

    /**
     * Gets the Email Template
     *
     * @return array
     */
    public function getEmailTemplate(){
        return $this->emailTemplate;
    }

    /**
     * Sets the Email Template
     *
     * @return self
     */
    public function setEmailTemplate($emailTemplate){
        $this->emailTemplate = $emailTemplate;
        return $this;
    }

    /**
     * Gets the Email Params
     *
     * @return array
     */
    public function getEmailParams(){
        return $this->emailParams;
    }

    /**
     * Sets the Email Params
     *
     * @return self
     */
    public function setEmailParams($emailParams){
        $this->emailParams = $emailParams;
        return $this;
    }

    /**
     * Sends this notification to all channels
     *
     * @param string $key The key of the notification
     * @param integer $userId The user id that will get the notification
     * @param array $data Additional data information
     * @throws \Exception
     */
    public function send(){
        Yii::$app->getModule('notifications')->send($this);
    }

}
