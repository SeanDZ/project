<?php
namespace app\models;
use yii\db\ActiveRecord;
class Mooc extends ActiveRecord{
	public static function tableName(){
		return "{{%mooc}}";
	}
}
