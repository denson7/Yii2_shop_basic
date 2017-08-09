<?php
/**
 * Created by PhpStorm.
 * User: Denson
 * Date: 2017/3/30
 * Time: 22:27
 */
namespace app\controllers;

use yii\web\Controller;
use app\models\Test;


class IndexController extends Controller
{
	
	/*	
	public function actionIndex()
	{
		// echo 'index/index';
		$model = new Test;
		$data = $model->find()->one();
		// return $this->render('index');
		return $this->render('index', array('row' => $data));
	}*/

	public function actionIndex()
	{
		////去掉yii2模板的头部和尾部
		//方法一,使用render()
		// $this->layout = false; 
		$this->layout = "layout1";
		return $this->render('index');
		//方法二,使用rendPartial()
		// return $this->rendPartial('index');
	}
}