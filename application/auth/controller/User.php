<?php
namespace app\auth\controller;

use think\Controller;
use think\Db;
use think\Session;

class User extends Controller
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

    public function login()
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
                $privs = Db::table('apl_group_user')
                    ->where('user_id', $user['id'])
                    ->select();
                $pril_ids = [];
                foreach ($privs as $_priv) {
                    array_push($pril_ids, $_priv['group_id']);
                }
                Session::set('priv', $pril_ids);
                $user_home = url('index/index/index');
                if($user['type'] === 1) {
                    $user_home = url('apple/index/index');
                }
                $this->success('登录成功', $user_home);
            }
            $this->error('用户名或密码错误');
        }
        return view();
    }

    public function logout()
    {
        unset($_SESSION);
        Session::delete('user');
        $this->success('已成功退出', url('auth/user/login'));
    }
}
