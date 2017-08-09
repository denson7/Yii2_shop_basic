<?php
/**
 * Created by PhpStorm.
 * User: Denson
 * Date: 2017/3/26
 * Time: 16:29
 */
namespace app\models;

use yii\db\ActiveRecord;

class Profile extends ActiveRecord
{
    public static function tableName()
    {
        return "{{%profile}}";
    }
}
