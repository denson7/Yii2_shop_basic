<?php
/**
 * Created by PhpStorm.
 * User: Denson
 * Date: 2017/3/26
 * Time: 16:25
 */

namespace app\modules\controllers;
use yii\web\Controller;
use yii\data\Pagination;
use app\models\User;
use app\models\Profile;
use Yii;
use app\modules\controllers\CommonController;

class UserController extends CommonController
{
    /**
     * 用户列表
     * @return string
     */
    public function actionUsers()
    {
        $model = User::find()->joinWith('profile');//关联表user和profile
        $count = $model->count();
        $pageSize = Yii::$app->params['pageSize']['user'];
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $users = $model->offset($pager->offset)->limit($pager->limit)->all();
        $this->layout = "layout1";
        return $this->render('users', ['users' => $users, 'pager' => $pager]);
    }

    /**
     * 添加用户
     * @return string
     */
    public function actionReg()
    {
        $this->layout = "layout1";
        $model = new User;
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->reg($post)) {
                Yii::$app->session->setFlash('info', '添加成功');
            }
        }
        $model->userpass = '';
        $model->repass = '';
        return $this->render("reg", ['model' => $model]);
    }

    /**
     * 删除用户，先删除profile表，再删除user表
     */
    public function actionDel()
    {
        try{
            $userid = (int)Yii::$app->request->get('userid');
            if (empty($userid)) {
                throw new \Exception();
            }
            //开启事务
            $trans = Yii::$app->db->beginTransaction();
            if ($obj = Profile::find()->where('userid = :id', [':id' => $userid])->one()) {
                $res = Profile::deleteAll('userid = :id', [':id' => $userid]);
                if (empty($res)) {
                    throw new \Exception();
                }
            }
            if (!User::deleteAll('userid = :id', [':id' => $userid])) {
                throw new \Exception();
            }
            $trans->commit();
        } catch(\Exception $e) {
            if (Yii::$app->db->getTransaction()) {
                $trans->rollback();//事务回滚
            }
        }
        $this->redirect(['user/users']);
    }

}
