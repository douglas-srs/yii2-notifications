<?php

namespace soareseneves\notifications;

use Yii;
use app\models\User;
use yii\helpers\ArrayHelper;

abstract class Channel extends \yii\base\BaseObject
{

    public $id;

    public function __construct($id, $config = [])
    {
        $this->id = $id;
        parent::__construct($config);
    }

    public abstract function send(Notification $notification);


    public function recipients(Notification $notification){
    	$notificationData = $notification->getData();

        $modelClass = "app\\models\\" . ucfirst($notificationData['table_name']);

    	$model = \Yii::createObject([
			"class" => "app\\models\\" . ucfirst($notificationData['table_name'])
		]);

		$ownerUser = User::findOne($notification->user->id);
	    $databaseName = $ownerUser->database_name;		

        if ($notification->getPublicNotification()){        	

	    	$filterCongrega = false;

			if ($model->hasAttribute("congrega_id")){
				$congregaModel = $modelClass::find()->select('congrega_id')->where(['id' => $notificationData['table_id']])->asArray()->one();

				if ($congregaModel && isset($congregaModel['congrega_id'])){
					$filterCongrega = true;
					$congregaId = $congregaModel['congrega_id'];

					if (isset($notification->user->membro_id)){
						//verifico a congregação do membro == $congregaId
						$membro = Membros::findOne($notification->user->membro_id);

						$user_ids = User::find()->joinWith('membro')->where(['database_name' => $databaseName, 'membros.congrega_id' => $congregaId])->all();

	        			$user_ids = ArrayHelper::getColumn($user_ids, 'id');

	        			if ($notification->getExcludeOwner()){
	        				if (($key = array_search($notification->user->id, $user_ids)) !== false) {
								unset($user_ids[$key]);
							}
						}

						if (!$user_ids)
							$user_ids = [0, 0];

						return $user_ids;
					} else {
						//verifico se existe o $congregaId na tabela users_congregas com o $user->id
					    $user_ids = User::find()->joinWith(['userCongrega', 'authAssignment'])->where(['database_name' => $databaseName])->andFilterwhere([
        					'or',
			            	['users_congregas.congrega_id' => $congregaId],
			            	['auth_assignment.item_name' => 'admin']
				        ])->asArray()->all();			

	        			$user_ids = ArrayHelper::getColumn($user_ids, 'id');

						if ($notification->getExcludeOwner()){
	        				if (($key = array_search($notification->user->id, $user_ids)) !== false) {
								unset($user_ids[$key]);
							}
						}

						if (!$user_ids)
							$user_ids = [0, 0];

						return $user_ids;
					}

				}
			}

			if (!$filterCongrega){

				$user_ids = User::find()->where(['database_name' => $databaseName])->all();
	        	$user_ids = ArrayHelper::getColumn($user_ids, 'id');

	        	if ($notification->getExcludeOwner()){
    				if (($key = array_search($notification->user->id, $user_ids)) !== false) {
						unset($user_ids[$key]);
					}
				}

				if (!$user_ids)
					$user_ids = [0, 0];

		        return $user_ids;
			}	        
	    } else {
	    		if ($notification->getUserPermission()){

	    			if ($model->hasAttribute("congrega_id")){
						$congregaModel = $modelClass::find()->select('congrega_id')->where(['id' => $notificationData['table_id']])->asArray()->one();

						if ($congregaModel && isset($congregaModel['congrega_id'])){
							$congregaId = $congregaModel['congrega_id'];
							$user_ids = User::find()->joinWith(['userCongrega', 'authAssignment'])->where(['database_name' => $databaseName])->andFilterWhere([
		        				'or',
		        				['users_congregas.congrega_id' => $congregaId], 
		        				['auth_assignment.item_name' => 'admin']
		        			])->asArray()->all();

		        			$user_ids = ArrayHelper::getColumn($user_ids, 'id');

		        			foreach ($user_ids as $key => $user_id) {
		        				if (!\Yii::$app->authManager->checkAccess($user_id, $notification->getUserPermission())) {
		        					unset($user_ids[$key]);
		        				}
		        			}

							if ($notification->getExcludeOwner()){
		        				if (($key = array_search($notification->user->id, $user_ids)) !== false) {
									unset($user_ids[$key]);
								}
							}

							if (!$user_ids)
								$user_ids = [0, 0];

							return $user_ids;
						} else {
							return [$notification->user->id];
						}
					} else {
						return [$notification->user->id];
					}
    				
	    	} else {
		    	return [$notification->user->id];
		    }
	    }
    }

}
