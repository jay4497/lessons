<?php
namespace app\common\controller;

use app\common\traits\Apple;
use think\Controller;
use think\Session;

class Frontend extends Controller
{
    use Apple;

    protected $priv_ids = [];

    public function _initialize()
    {
        parent::_initialize();
        $option = $this->getOptions();
        if(@$option['status'] !== 'running') {
            exit('网站维护中...');
        }
        $this->assign('web_option', $option);
        if(!Session::has('user') || empty(Session::get('user'))) {
            $this->error('未登录或登录超时，请重新登录', url('auth/user/login'));
        }
        $user = Session::get('user');
        if($user['type'] !== 0) {
            $this->error('非法的用户', url('auth/user/login'));
        }
        if($user['status'] == 'forbiden') {
            $this->error('用户已被禁用', url('auth/user/login'));
        }
        $this->priv_ids = Session::get('priv');
        array_push($this->priv_ids, 0);

        $categories = $this->treeGroup();
        $this->assign('categories', $categories);
    }
}
