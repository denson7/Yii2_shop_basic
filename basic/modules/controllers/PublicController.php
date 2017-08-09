<?php
namespace app\modules\controllers;

use yii\web\Controller;
use app\modules\models\Admin;
use Yii;

class PublicController extends Controller
{
	//登录
	public function actionLogin()
	{
//        session_start();
//        var_dump($_SESSION);
        $this->layout = false;
        $model = new Admin;
        if (Yii::$app->request->isPost) {
        	$post = Yii::$app->request->post();
//            print_r($post);
        	if ($model->login($post)) {
				$this->redirect(['default/index']);
				Yii::$app->end();
			}
        }
		return $this->render("login",['model' => $model]);
	}

    /**
     * 注销登录
     */
    public function actionLogout()
    {
        //清除session
        Yii::$app->session->removeAll();
        if (!isset(Yii::$app->session['admin']['isLogin'])) {
            $this->redirect(['public/login']);
            Yii::$app->end();
        }
        $this->goBack();
    }

    /**
     * 找回密码
     */
    public function actionSeekpassword()
    {
        $this->layout = false;
        $model = new Admin;
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->seekPass($post)) {
                Yii::$app->session->setFlash('info', '电子邮件已经发送成功，请查收');
            }
        }

        return $this->render('seekpassword',['model' => $model]);

    }

}