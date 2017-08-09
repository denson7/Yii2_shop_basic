<?php
/**
 * Created by PhpStorm.
 * User: Denson
 * Date: 2017/3/29
 * Time: 22:17
 */
namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class Cart extends ActiveRecord
{
    public static function tableName()
    {
        return "{{%cart}}";
    }

    public function rules()
    {
        return [
            [['productid','productnum','userid','price'], 'required'],
            ['createtime', 'safe']
        ];
    }


}
