<?php
namespace app\apple\controller;

use app\common\controller\Backend;
use think\Db;
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

        $tree_data = $this->jsTree();
        $this->assign('tree_data', json_encode($tree_data, JSON_UNESCAPED_UNICODE));
    }

    public function index()
    {
        $where = [];
        $users = $this->model
            ->where($where)
            ->paginate(20);
        $title = '用户管理';
        return view('', compact('users', 'title'));
    }

    public function add()
    {
        if($this->request->isPost()){
            $data = $this->request->post();
            $_validate = $this->validate($data, 'User.add');
            if($_validate !== true) {
                $this->error('err: '. $_validate);
            }
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

            $privilleges = explode(',', $data['privilleges']);
            Db::startTrans();
            try{
                $this->model->allowField(true)->save($data);
                $uid = $this->model->getLastInsID();
                $pril_data = [];
                foreach ($privilleges as $_pril) {
                    $_temp = [
                        'user_id' => $uid,
                        'group_id' => $_pril,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    array_push($pril_data, $_temp);
                }
                Db::table('apl_group_user')->insertAll($pril_data);
            } catch (\Exception $ex) {
                Db::rollback();
                $this->error('dberr: '. $ex->getMessage());
            }
            Db::commit();
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
        $privilleges = Db::table('apl_group_user')
            ->where('user_id', $user['id'])
            ->select();
        $pril_ids = [];
        foreach ($privilleges as $_priv) {
            array_push($pril_ids, $_priv['group_id']);
        }
        $pril_ids = implode(',', $pril_ids);
        if($this->request->isPost()){
            $data = $this->request->post();
            $_validate = $this->validate($data, 'User.update');
            if($_validate !== true) {
                $this->error($_validate);
            }

            if(empty($data['password'])){
                unset($data['password']);
            } else {
                $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            $privilleges = explode(',', $data['privilleges']);
            Db::startTrans();
            try {
                $this->model
                    ->allowField(true)
                    ->isUpdate(true)
                    ->save($data, ['id' => $uid]);

                Db::table('apl_group_user')
                    ->where('user_id', $uid)
                    ->delete();
                $pril_data = [];
                foreach ($privilleges as $_pril) {
                    $_temp = [
                        'user_id' => $uid,
                        'group_id' => $_pril,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    array_push($pril_data, $_temp);
                }
                Db::table('apl_group_user')->insertAll($pril_data);
            } catch (\Exception $ex) {
                Db::rollback();
                $this->error($ex->getMessage());
            }
            Db::commit();
            $this->success('更新成功', url('user/update', ['uid' => $uid]));
        }
        $title = '编辑用户信息';
        return view('', compact('title', 'user', 'pril_ids'));
    }

    public function delete($ids)
    {
        $ids = $this->request->request('ids');
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
