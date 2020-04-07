<?php
namespace app\apple\controller;

use app\common\controller\Backend;

class Group extends Backend
{
    /**
     * @var \app\apple\model\Group
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();

        $this->model = new \app\apple\model\Group;

        $group_tree = $this->treeGroup();
        $parents = $this->treeList($group_tree);
        $this->assign('parents', $parents);
    }

    public function index()
    {
        $group_tree = $this->treeGroup();
        $groups = $this->treeList($group_tree);

        $title = '分组管理';
        return view('', compact('groups', 'title'));
    }

    public function add()
    {
        if($this->request->isPost()) {
            $data = $this->request->post();

            $_validate = $this->validate($data, 'Group');
            if($_validate !== true) {
                $this->error($_validate);
            }

            try{
                $this->model->insert($data);
            } catch (\Exception $ex) {
                $this->error($ex->getMessage());
            }
            $this->success('添加成功', url('group/add'));
        }

        $title = '添加分组';
        return view('', compact('title'));
    }

    public function update($id)
    {
        $group = $this->model
            ->where('id', $id)
            ->find();
        if(empty($group)){
            $this->error('目标不存在');
        }
        if($this->request->isPost()) {
            $data = $this->request->post();

            $_validate = $this->validate($data, 'Group');
            if($_validate !== true) {
                $this->error($_validate);
            }

            try{
                $this->model->where('id', $id)->save($data);
            } catch (\Exception $ex) {
                $this->error($ex->getMessage());
            }
            $this->success('更新成功', url('group/update', ['id' => $id]));
        }

        $title = '编辑分组信息';
        return view('', compact('title', 'group'));
    }

    public function delete()
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
        $this->success('删除成功', url('group/index'));
    }

    private function treeGroup($pid = 0)
    {
        $groups = $this->model
            ->where('pid', $pid)
            ->select();
        if(!empty($groups)){
            foreach ($groups as &$group) {
                $group['children'] = $this->treeGroup($group['id']);
            }
            unset($group);
        }
        return $groups;
    }

    private function treeList($data, $depth = 0)
    {
        $list = [];
        foreach ($data as &$item) {
            $item['depth'] = $depth;
            array_push($list, $item);
            $children = $item['children'];
            unset($item['children']);
            if(!empty($children)) {
                $list = array_merge($list, $this->treeList($children, $depth + 1));
            }
        }
        unset($item);
        return $list;
    }
}
