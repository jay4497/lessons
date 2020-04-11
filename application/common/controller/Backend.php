<?php
namespace app\common\controller;

use app\apple\model\Group;
use app\common\traits\Apple;
use think\Controller;
use think\Session;

class Backend extends Controller
{
    use Apple;

    public function _initialize()
    {
        parent::_initialize();

        if(!Session::has('user') || empty(Session::get('user'))){
            $this->error('请登录', url('auth/user/login'));
        }
        $user = Session::get('user');
        if($user['type'] === 1) {
            $this->error('非法的用户', url('auth/user/login'));
        }
        if($user['status'] == 'forbiden') {
            $this->error('用户已被禁用', url('auth/user/login'));
        }
    }

    protected function jsTree($pid = 0)
    {
        $model = new Group;
        $groups = $model
            ->field('id, display_name, pid')
            ->where('pid', $pid)
            ->select();
        foreach ($groups as &$group) {
            $group['text'] = $group['display_name'];
            $group['children'] = $this->jsTree($group['id']);
        }
        unset($group);
        return $groups;
    }
}
