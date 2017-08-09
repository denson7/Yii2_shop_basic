<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
* 
*/
class Test extends ActiveRecord
{
	
	public static function tableName()
	{
		# code...
		return "{{%test}}";//适用于有表前缀的表名
	}
}