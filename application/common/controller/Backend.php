<?php
namespace app\common\controller;

use app\apple\model\Group;
use think\Controller;
use think\Session;

class Backend extends Controller
{
    public function _initialize()
    {
        parent::_initialize();

        if(!Session::has('user') && empty(Session::get('user'))){
            $this->error('请登录', url('auth/user/login'));
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
