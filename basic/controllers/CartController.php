<?php
/**
 * Created by PhpStorm.
 * User: Denson
 * Date: 2017/3/30
 * Time: 22:27
 */
namespace app\controllers;

use app\controllers\CommonController;
use app\models\User;
use app\models\Cart;
use app\models\Product;
use Yii;

class CartController extends CommonController
{
	public function actionIndex()
	{
		if (Yii::$app->session['isLogin'] != 1) {
			return $this->redirect(['member/auth']);
		}
		$userid = User::find()->where('username = :name', [':name' => Yii::$app->session['loginname']])->one()->userid;
		$cart = Cart::find()->where('userid = :uid', [':uid' => $userid])->asArray()->all();
		$data = [];
		foreach ($cart as $k => $pro) {
			$product = Product::find()->where('productid = :pid', [':pid' => $pro['productid']])->one();
			$data[$k]['cover'] = $product->cover;
			$data[$k]['title'] = $product->title;
			$data[$k]['productnum'] = $pro['productnum'];
			$data[$k]['price'] = $pro['price'];
			$data[$k]['productid'] = $pro['productid'];
			$data[$k]['cartid'] = $pro['cartid'];
		}
		$this->layout = 'layout1';
		return $this->render("index", ['data' => $data]);
	}

	/**
	 * 加入购物车
	 */
	public function actionAdd()
	{
		//判断用户是否登录
		if (Yii::$app->session['isLogin'] != 1) {
			return $this->redirect(['member/auth']);
		}
		//获取用户ID
		$userid = User::find()->where('username = :name', [':name' => Yii::$app->session['loginname']])->one()->userid;
        //判断是post提交--详情页
		if (Yii::$app->request->isPost) {
			$post = Yii::$app->request->post();
            //商品数量
			$num = Yii::$app->request->post()['productnum'];
			$data['Cart'] = $post;
			$data['Cart']['userid'] = $userid;
		}
        //判断是get提交--列表页
		if (Yii::$app->request->isGet) {
            //获取商品ID
			$productid = Yii::$app->request->get("productid");
            //查询商品ID对应的model
			$model = Product::find()->where('productid = :pid', [':pid' => $productid])->one();
            //获取商品价格
			$price = $model->issale ? $model->saleprice : $model->price;
            //设置商品数量，默认为1
			$num = 1;
            //将数据存入$data数组
			$data['Cart'] = ['productid' => $productid, 'productnum' => $num, 'price' => $price, 'userid' => $userid];
		}
        //判断该购物车是否包含该商品，没有就添加，有就只更新数量
		if (!$model = Cart::find()->where('productid = :pid and userid = :uid', [':pid' => $data['Cart']['productid'], ':uid' => $data['Cart']['userid']])->one()) {
			$model = new Cart;
		} else {
			$data['Cart']['productnum'] = $model->productnum + $num;
		}
		$data['Cart']['createtime'] = time();
		$model->load($data);
		$model->save();
		return $this->redirect(['cart/index']);
	}

    /**
     * 更改购物车
     */
	public function actionMod()
	{
		$cartid = Yii::$app->request->get("cartid");
		$productnum = Yii::$app->request->get("productnum");
		Cart::updateAll(['productnum' => $productnum], 'cartid = :cid', [':cid' => $cartid]);
	}

    /**
     * @return \yii\web\Response
     */
	public function actionDel()
	{
		$cartid = Yii::$app->request->get("cartid");
		Cart::deleteAll('cartid = :cid', [':cid' => $cartid]);
		return $this->redirect(['cart/index']);
	}

}