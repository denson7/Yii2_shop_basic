<?php
/**
 * Created by PhpStorm.
 * User: Denson
 * Date: 2017/3/30
 * Time: 22:27
 */
namespace app\controllers;

use yii\web\Controller;
use app\controllers\CommonController;
use app\models\User;
use Yii;

class MemberController extends Controller
{

	/**
	 * 用户登录
	 * @return string|\yii\web\Response
	 */
	public function actionAuth()
	{
		$this->layout = 'layout2';
		if (Yii::$app->request->isGet) {
			$url = Yii::$app->request->referrer;
			if (empty($url)) {
				$url = "/";
			}
			Yii::$app->session->setFlash('referrer', $url);
		}
		$model = new User;
		if (Yii::$app->request->isPost) {
			$post = Yii::$app->request->post();
			if ($model->login($post)) {
				$url = Yii::$app->session->getFlash('referrer');
				return $this->redirect($url);
			}
		}
		return $this->render("auth", ['model' => $model]);
	}

	/**
	 * 用户注册
	 * @return string
	 */
	public function actionReg()
	{
		$model = new User;
		if (Yii::$app->request->isPost) {
			$post = Yii::$app->request->post();
			if ($model->regByMail($post)) {
				Yii::$app->session->setFlash('info', '电子邮件发送成功');
			}
		}
		$this->layout = 'layout2';
		return $this->render('auth', ['model' => $model]);
	}


	/**
	 * 退出登录
	 * @return \yii\web\Response
	 */
	public function actionLogout()
	{
		Yii::$app->session->remove('loginname');
		Yii::$app->session->remove('isLogin');
		if (!isset(Yii::$app->session['isLogin'])) {
			return $this->goBack(Yii::$app->request->referrer);
		}
	}

	/**
	 * QQ登录
	 */
	public function actionQqlogin()
	{
		require_once("../vendor/qqlogin/qqConnectAPI.php");
		$qc = new \QC();
		$qc->qq_login();
	}


	/**
	 * QQ跳转后的回调地址
	 * @return \yii\web\Response
	 */
	public function actionQqcallback()
	{
		//登录后获取该用户信息
		require_once("../vendor/qqlogin/qqConnectAPI.php");
		$auth = new \OAuth();
		$accessToken = $auth->qq_callback();
		$openid = $auth->get_openid();
		$qc = new \QC($accessToken, $openid);
		$userinfo = $qc->get_user_info();//获取用户信息
		//var_dump($userinfo);
		//用户信息存入session
		$session = Yii::$app->session;
		$session['userinfo'] = $userinfo;
		$session['openid'] = $openid;

		//判断QQ号是否已经绑定用户
		if ($model = User::find()->where('openid = :openid', [':openid' => $openid])->one()) {
			$session['loginname'] = $model->username;
			$session['isLogin'] = 1;
			return $this->redirect(['index/index']);
		}

		return $this->redirect(['member/qqreg']);
	}


	/**
	 * 未绑定用户创建一个新用户
	 * @return string|\yii\web\Response
	 */
	public function actionQqreg()
	{
		$this->layout = "layout2";
		$model = new User;
        //完善个人资料
		if (Yii::$app->request->isPost) {
			$post = Yii::$app->request->post();
            //var_dump($post);
			$session = Yii::$app->session;
			$post['User']['openid'] = $session['openid'];

			if ($model->reg($post, 'qqreg')) {
				$session['loginname'] = $post['User']['username'];
				$session['isLogin'] = 1;
				return $this->redirect(['index/index']);
			}
		}
		return $this->render('qqreg', ['model' => $model]);
	}


}