<?php
namespace app\apple\controller;

use app\common\controller\Backend;

class Options extends Backend
{
    public function index()
    {
        $model = new \app\apple\model\Options;
        $option = $model->find();
        if($this->request->isPost()) {
            $data = $this->request->post();
            $_validate = $this->validate($data, 'Options');
            if($_validate !== true) {
                $this->error($_validate);
            }

            try{
                $model->allowField(true)->isUpdate(true)->save($data, ['id' => $option['id']]);
            } catch (\Exception $ex) {
                $this->error('发生错误：'. $ex->getMessage());
            }
            $this->success('修改成功', url('options/index'));
        }

        $title = '系统设置';
        return view('', compact('title', 'option'));
    }
}
