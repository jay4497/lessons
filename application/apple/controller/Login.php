<?php
namespace app\apple\controller;

use think\Controller;
use think\Session;

class Login extends Controller
{
    /**
     * @var \app\apple\model\User
     */
    private $user_model = null;

    public function _initialize()
    {
        parent::_initialize();

        $this->user_model = new \app\apple\model\User;
    }

    public function index()
    {
        if($this->request->isPost()){
            $user_name = $this->request->post('user_name');
            $pass = $this->request->post('password');
            $user = $this->user_model
                ->where('user_name|phone|email', $user_name)
                ->find();
            if(empty($user)){
                $this->error('用户名或密码错误');
            }
            if(password_verify($pass, $user['password'])){
                Session::set('user', $user);
                $this->success('登录成功', url('index/index'));
            }
            $this->error('用户名或密码错误');
        }
        return view();
    }
}
