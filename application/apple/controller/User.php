<?php
namespace app\apple\controller;

use app\common\controller\Backend;
use think\Session;

class User extends Backend
{
    /**
     * @var \app\apple\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\apple\model\User;
    }

    public function index()
    {
        $where = [];
        $users = $this->model
            ->where($where)
            ->paginate(20);
        return view('', compact('users'));
    }

    public function add()
    {
        if($this->request->isPost()){
            $data = $this->request->post();
            $_validate = $this->validate($data, 'User');
            if($_validate !== true) {
                $this->error($_validate);
            }
            try{
                $this->model->insert($data);
            } catch (\Exception $ex) {
                $this->error($ex->getMessage());
            }
            $this->success('添加成功', url('user/add'));
        }
        $title = '添加用户';
        return view('', compact('title'));
    }

    public function update($uid)
    {
        $user = $this->model
            ->where('id', $uid)
            ->find();
        if(empty($user)){
            $this->error('用户不存在', url('index/index'));
        }
        if($this->request->isPost()){
            $data = $this->request->post();
            $_validate = $this->validate($data, 'User');
            if($_validate !== true) {
                $this->error($_validate);
            }
            try{
                $this->model
                    ->where('id', $uid)
                    ->save($data);
            } catch (\Exception $ex) {
                $this->error($ex->getMessage());
            }
            $this->success('更新成功', url('user/update', ['uid' => $uid]));
        }
        $title = '添加用户';
        return view('', compact('title'));
    }

    public function delete($ids)
    {
        $_ids = $ids;
        if(!is_array($ids)){
            $_ids = explode(',', $ids);
        }

        try{
            $this->model
                ->where('id', 'in', $_ids)
                ->delete();
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
        $this->success('删除成功', url('user/index'));
    }

    public function profile()
    {
        $uid = Session::get('user')['id'];
        $user = $this->model
            ->where('id', $uid)
            ->find();
        if(empty($user)){
            $this->error('用户不存在', url('index/index'));
        }
        if($this->request->isPost()){
            $data = $this->request->post();

            $_validate = $this->validate($data, 'User');
            if($_validate !== true) {
                $this->error($_validate);
            }
            try{
                $this->model
                    ->where('id', $uid)
                    ->save($data);
            } catch (\Exception $ex) {
                $this->error($ex->getMessage());
            }
            $this->success('更新成功', url('user/profile', ['uid' => $uid]));
        }

        $title = $user['user_name']. '个人资料';
        return view('', compact('title', 'user'));
    }
}
