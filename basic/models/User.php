<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
class User extends ActiveRecord
{
    public $repass;
    public $loginname;
    public $rememberMe=true;
    public static function tableName()
    {
        return "{{%user}}";
    }
    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'userpass' => '用户密码',
            'repass' => '确认密码',
            'useremail' => '电子邮箱',
            'loginname' => '用户名/电子邮箱',
        ];
    }

    public function rules()
    {
        return [
            ['loginname', 'required', 'message' => '登录用户名不能为空', 'on' => ['login']],
            ['openid', 'required', 'message' => 'openid不能为空', 'on' => ['qqreg']],

            ['username', 'required', 'message' => '用户名不能为空', 'on' => ['reg', 'regbymail', 'qqreg']],
            ['openid', 'unique', 'message' => 'openid已经被注册', 'on' => ['qqreg']],
            ['username', 'unique', 'message' => '用户已经被注册', 'on' => ['reg', 'regbymail', 'qqreg']],
            ['useremail', 'required', 'message' => '电子邮件不能为空', 'on' => ['reg', 'regbymail']],
            ['useremail', 'email', 'message' => '电子邮件格式不正确', 'on' => ['reg', 'regbymail']],
            ['useremail', 'unique', 'message' => '电子邮件已被注册', 'on' => ['reg', 'regbymail']],
            ['userpass', 'required', 'message' => '用户密码不能为空', 'on' => ['reg', 'login', 'regbymail', 'qqreg']],
            ['repass', 'required', 'message' => '确认密码不能为空', 'on' => ['reg', 'qqreg']],
            ['repass', 'compare', 'compareAttribute' => 'userpass', 'message' => '两次密码输入不一致', 'on' => ['reg', 'qqreg']],
            ['userpass', 'validatePass', 'on' => ['login']],
        ];
    }
    public function validatePass()
    {
        if (!$this->hasErrors()) {
            $loginname = "username";
            if (preg_match('/@/', $this->loginname)) {
                $loginname = "useremail";
            }
            $data = self::find()
                ->where($loginname.' = :loginname and userpass = :pass',
                    [':loginname' => $this->loginname,
                    ':pass' => md5($this->userpass)])
                ->one();
            if (is_null($data)) {
                $this->addError("userpass", "用户名或者密码错误");
            }
        }
    }
    public function reg($data)
    {
        $this->scenario='reg';
        if($this->load($data) && $this->validate()){
            $this->createtime=time();
            $this->userpass=md5($this->userpass);
            if($this->save(false)){
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     * 链表查询数据 表user profile
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::className(),['userid'=>'userid']);
    }
    public function login($data)
    {
        $this->scenario='login';
        if($this->load($data) && $this->validate()){
            $lifetime=$this->rememberMe?24*3600:0;
            $session=Yii::$app->session;
            session_set_cookie_params($lifetime);
            $session['loginname']=$this->loginname;
            $session['isLogin']=1;
            return (bool)$session['isLogin'];
        }
        return false;
    }

    public function regByMail($data)
    {
        /**
         * 用户名和密码
         * $data['User']['username'] = 'shop_'.uniqid();
         * $data['User']['userpass'] = uniqid();
         */
        $data['User']['username'] = 'shop_'.uniqid();
        $data['User']['userpass'] = uniqid();
        $this->scenario = 'regbymail';
        if ($this->load($data) && $this->validate()) {
            $mailer = Yii::$app->mailer->compose('createuser', ['userpass' => $data['User']['userpass'], 'username' => $data['User']['username']]);
            $mailer->setFrom('m18515156022@163.com');
            $mailer->setTo($data['User']['useremail']);
            $mailer->setSubject('新建用户');
            if ($mailer->send() && $this->reg($data, 'regbymail')) {
                return true;
            }
        }
        return false;
    }
}