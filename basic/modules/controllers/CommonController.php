<?php
/**
 * Created by PhpStorm.
 * User: Denson
 * Date: 2017/3/26
 * Time: 16:28
 */

namespace app\modules\controllers;

use yii\web\Controller;
use Yii;

class CommonController extends Controller
{
    public function init()
    {
        if (Yii::$app->session['admin']['isLogin'] != 1) {
            return $this->redirect(['/admin/public/login']);
        }
    }
}
