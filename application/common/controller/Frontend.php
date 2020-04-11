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

        if(!Session::has('user') || empty(Session::get('user'))) {
            $this->error('未登录或登录超时，请重新登录', url('auth/user/login'));
        }
        $this->priv_ids = Session::get('priv');
    }
}
