<?php
namespace app\modules\models;
use yii\db\ActiveRecord;
use Yii;

class Admin extends ActiveRecord
{
	public $rememberMe = true;
	public $repass;

    public static function tableName()
    {
        return "{{%admin}}";
    }

    /**
     * 属性标签
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'adminuser' => '管理员账号',
            'adminemail' => '管理员邮箱',
            'adminpass' => '管理员密码',
            'repass' => '管理员确认密码',
        ];
    }


    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return [
            ['adminuser', 'required', 'message' => '管理员账号不能为空', 'on' => ['login', 'seekpass', 'changepass', 'adminadd', 'changeemail']],//on表示指定场景进行规则验证
            ['adminpass', 'required', 'message' => '管理员密码不能为空', 'on' => ['login', 'changepass', 'adminadd', 'changeemail']],
            ['rememberMe', 'boolean', 'on' => 'login'],
            ['adminpass', 'validatePass', 'on' => ['login', 'changeemail']],//执行自定义回调函数validatePass();
            ['adminemail', 'required', 'message' => '电子邮箱不能为空', 'on' => ['seekpass', 'adminadd', 'changeemail']],
            ['adminemail', 'email', 'message' => '电子邮箱格式不正确', 'on' => ['seekpass', 'adminadd', 'changeemail']],
            ['adminemail', 'unique', 'message' => '电子邮箱已被注册', 'on' => ['adminadd', 'changeemail']],
            ['adminuser', 'unique', 'message' => '管理员已被注册', 'on' => 'adminadd'],
            ['adminemail', 'validateEmail', 'on' => 'seekpass'],
            ['repass', 'required', 'message' => '确认密码不能为空', 'on' => ['changepass', 'adminadd']],
            ['repass', 'compare', 'compareAttribute' => 'adminpass', 'message' => '两次密码输入不一致', 'on' => ['changepass', 'adminadd']],
        ];
    }

    /**
     * 验证密码规则
     */
    public function validatePass()
    {
        if (!$this->hasErrors()) {
            $data = self::find()->where(
                'adminuser = :user and adminpass = :pass',
                [
                    ":user" => $this->adminuser,
                    ":pass" => md5($this->adminpass),
                ]
            )->one();
            if (is_null($data)) {
                $this->addError("adminpass", "用户名或者密码错误");
            }
        }
    }

    /**
     * 验证邮箱规则
     */
    public function validateEmail()
    {
        if (!$this->hasErrors()) {
            $data = self::find()->where(
                'adminuser = :user and adminemail = :email',
                [
                    ':user' => $this->adminuser,
                    ':email' => $this->adminemail
                ]
            )->one();
            if (is_null($data)) {
                $this->addError('adminemail','管理员电子邮箱不匹配');
            }
        }
    }

    /**
     * @param $data
     * @return bool
     */
    public function login($data)
    {
        //指定场景
        $this->scenario = 'login';
    	//执行验证并写入session
        if ($this->load($data) && $this->validate()) {
            //写入session
            $lifetime = $this->rememberMe ? 24*3600 : 0;
            $session = Yii::$app->session;
            session_set_cookie_params($lifetime);
            $session['admin'] = [
                'adminuser' => $this->adminuser,
                'isLogin' => 1,
            ];
            //更新时间和IP
            $this->updateAll([
                'logintime' => time(),
                'loginip' => ip2long(Yii::$app->request->userIP)],
                'adminuser = :user', [':user' => $this->adminuser]
            );
            return (bool)$session['admin']['isLogin'];
        }
        return false;
    }


    /**
     * 忘记密码
     * @param $data
     * @return bool
     */
    public function seekPass($data)
    {
        //指定场景
        $this->scenario = 'seekpass';
        if ($this->load($data) && $this->validate()) {
            //发送邮件
            $time = time();
            $token = $this->createToken($data['Admin']['adminuser'], $time);
            $mailer = Yii::$app->mailer->compose('seekpass', ['adminuser' => $data['Admin']['adminuser'], 'time' => $time, 'token' => $token]);
            $mailer->setFrom("yue344520@163.com");
            $mailer->setTo($data['Admin']['adminemail']);
            $mailer->setSubject("找回密码");
            if ($mailer->send()) {
                return true;
            }

        }
        return false;
    }

    public function createToken($adminuser, $time)
    {
        return md5(md5($adminuser).base64_encode(Yii::$app->request->userIP).md5($time));
    }

    /**
     * 更改密码
     * @param $data
     * @return bool
     */
    public function changePass($data)
    {
        $this->scenario = 'changepass';
        if ($this->load($data) && $this->validate()){
            return (bool)$this->updateAll([
                'adminpass' => md5($this->adminpass)],
                'adminuser = :user', [':user' => $this->adminuser]
            );
        }
        return false;
    }

    /**
     * 添加管理员
     * @param $data
     * @return bool
     */
    public function reg($data)
    {
        $this->scenario = 'adminadd';
        if ($this->load($data) && $this->validate()) {
            $this->adminpass = md5($this->adminpass);
            if ($this->save(false)) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * 管理员修改邮箱
     * @param $data
     * @return bool
     */
    public function changeEmail($data)
    {
        $this->scenario = "changeemail";
        if ($this->load($data) && $this->validate()) {
            return (bool)$this->updateAll(
                [
                'adminemail' => $this->adminemail],
                'adminuser = :user', [':user' => $this->adminuser
                ]
            );
        }
        return false;
    }


}