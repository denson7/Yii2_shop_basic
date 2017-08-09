<?php
/**
 * Created by PhpStorm.
 * User: Denson
 * Date: 2017/3/30
 * Time: 23:27
 */
namespace app\controllers;

use app\controllers\CommonController;
use Yii;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Cart;
use app\models\Product;
use app\models\User;
use app\models\Address;
use app\models\Pay;
use dzer\express\Express;

class OrderController extends CommonController
{
	
	//订单中心
	public function actionIndex()
	{
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
        $userid = User::find()->where('username = :name', [':name' => Yii::$app->session['loginname']])->one()->userid;
        $cart = Cart::find()->where('userid = :uid', [':uid' => $userid])->asArray()->all();
        $data = [];
        foreach ($cart as $k=>$pro) {
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

	//结算页
	public function actionCheck()
	{
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
        $orderid = Yii::$app->request->get('orderid');
        $status = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one()->status;
        if ($status != Order::CREATEORDER && $status != Order::CHECKORDER) {
            return $this->redirect(['order/index']);
        }
        $loginname = Yii::$app->session['loginname'];
        $userid = User::find()->where('username = :name or useremail = :email', [':name' => $loginname, ':email' => $loginname])->one()->userid;
        $addresses = Address::find()->where('userid = :uid', [':uid' => $userid])->asArray()->all();
        $details = OrderDetail::find()->where('orderid = :oid', [':oid' => $orderid])->asArray()->all();
        $data = [];
        foreach($details as $detail) {
            $model = Product::find()->where('productid = :pid' , [':pid' => $detail['productid']])->one();
            $detail['title'] = $model->title;
            $detail['cover'] = $model->cover;
            $data[] = $detail;
        }
        //获取快递方式
        $express = Yii::$app->params['express'];
        $expressPrice = Yii::$app->params['expressPrice'];
        $this->layout = "layout1";
        return $this->render("check", ['express' => $express, 'expressPrice' => $expressPrice, 'addresses' => $addresses, 'products' => $data]);
	}

    /**
     * 提交订单
     * @return \yii\web\Response
     */
	public function actionAdd()
	{
		if (Yii::$app->session['isLogin'] != 1) {
			return $this->redirect(['member/auth']);
		}
        //创建事务
		$transaction = Yii::$app->db->beginTransaction();
		try {
			if (Yii::$app->request->isPost) {
				$post = Yii::$app->request->post();
				$ordermodel = new Order;
				$ordermodel->scenario = 'add';
                //根据session中的用户名或email获取user模型对象
				$usermodel = User::find()->where('username = :name or useremail = :email', [':name' => Yii::$app->session['loginname'], ':email' => Yii::$app->session['loginname']])->one();
				if (!$usermodel) {
					throw new \Exception();
				}
				$userid = $usermodel->userid;
				$ordermodel->userid = $userid;
                //创建订单状态
				$ordermodel->status = Order::CREATEORDER;
				$ordermodel->createtime = time();
				if (!$ordermodel->save()) {
					throw new \Exception();
				}
				$orderid = $ordermodel->getPrimaryKey();
				foreach ($post['OrderDetail'] as $product) {
					$model = new OrderDetail;
					$product['orderid'] = $orderid;
					$product['createtime'] = time();
					$data['OrderDetail'] = $product;
					if (!$model->add($data)) {
						throw new \Exception();
					}
					Cart::deleteAll('productid = :pid' , [':pid' => $product['productid']]);
					Product::updateAllCounters(['num' => -$product['productnum']], 'productid = :pid', [':pid' => $product['productid']]);
				}
			}
			$transaction->commit();
		}catch(\Exception $e) {
			$transaction->rollback();
			return $this->redirect(['cart/index']);
		}
		return $this->redirect(['order/check', 'orderid' => $orderid]);
	}

    /**
     * 确认订单
     * @return \yii\web\Response
     */
    public function actionConfirm()
    {
        //addressid, expressid, status, amount(orderid,userid)
        try {
            if (Yii::$app->session['isLogin'] != 1) {
                return $this->redirect(['member/auth']);
            }
            if (!Yii::$app->request->isPost) {
                throw new \Exception();
            }
            $post = Yii::$app->request->post();
            $loginname = Yii::$app->session['loginname'];
            $usermodel = User::find()->where('username = :name or useremail = :email', [':name' => $loginname, ':email' => $loginname])->one();
            if (empty($usermodel)) {
                throw new \Exception();
            }
            $userid = $usermodel->userid;
            $model = Order::find()->where('orderid = :oid and userid = :uid', [':oid' => $post['orderid'], ':uid' => $userid])->one();
            if (empty($model)) {
                throw new \Exception();
            }
            //更改订单状态
            $model->scenario = "update";
            $post['status'] = Order::CHECKORDER;
            $details = OrderDetail::find()->where('orderid = :oid', [':oid' => $post['orderid']])->all();
            //计算商品总价
            $amount = 0;
            foreach($details as $detail) {
                $amount += $detail->productnum*$detail->price;
            }
            if ($amount <= 0) {
                throw new \Exception();
            }
            $express = Yii::$app->params['expressPrice'][$post['expressid']];
            if ($express < 0) {
                throw new \Exception();
            }
            //商品总价+快递价格
            $amount += $express;
            $post['amount'] = $amount;
            $data['Order'] = $post;
            if (empty($post['addressid'])) {
                return $this->redirect(['order/pay', 'orderid' => $post['orderid'], 'paymethod' => $post['paymethod']]);
            }
            if ($model->load($data) && $model->save()) {
                return $this->redirect(['order/pay', 'orderid' => $post['orderid'], 'paymethod' => $post['paymethod']]);
            }
        }catch(\Exception $e) {
            return $this->redirect(['index/index']);
        }
    }

    /**
     * 支付方法
     * @return void|\yii\web\Response
     */
    public function actionPay()
    {
        try{
            if (Yii::$app->session['isLogin'] != 1) {
                throw new \Exception();
            }
            $orderid = Yii::$app->request->get('orderid');
            $paymethod = Yii::$app->request->get('paymethod');
            if (empty($orderid) || empty($paymethod)) {
                throw new \Exception();
            }
            if ($paymethod == 'alipay') {
                return Pay::alipay($orderid);
            }
        }catch(\Exception $e) {}
        return $this->redirect(['order/index']);
    }

    public function actionGetexpress()
    {
        $expressno = Yii::$app->request->get('expressno');
        $res = Express::search($expressno);
        echo $res;
        exit;
    }

    /**
     * @return \yii\web\Response
     */
    public function actionReceived()
    {
        $orderid = Yii::$app->request->get('orderid');
        $order = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one();
        if (!empty($order) && $order->status == Order::SENDED) {
            $order->status = Order::RECEIVED;
            $order->save();
        }
        return $this->redirect(['order/index']);
    }
}