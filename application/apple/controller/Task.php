<?php
namespace app\apple\controller;

use app\common\controller\Backend;
use think\Db;

class Task extends Backend
{
    public function autoFill()
    {
        $base_dir = THINK_PATH. '..'. DIRECTORY_SEPARATOR. 'public'. DIRECTORY_SEPARATOR. 'videos'. DIRECTORY_SEPARATOR;
        $save_dir = '/videos/';
        $group_model = new \app\apple\model\Group;
        $video_model = new \app\apple\model\Video;
        $groups = $group_model->select();

        Db::startTrans();
        $video_model->where('id', '>', 0)->delete();
        foreach ($groups as $group) {
            $group_id = $group['id'];
            $group_dir = $this->getGroupDir($group_id);
            $group_dir = implode(DIRECTORY_SEPARATOR, $group_dir). DIRECTORY_SEPARATOR;
            $final_dir = $base_dir. $group_dir;
            if(is_dir($final_dir)) {
                $dir_list = scandir($final_dir);
                foreach ($dir_list as $_item) {
                    if ($_item === '.' || $_item === '..') {
                        continue;
                    }
                    if (is_file($final_dir . $_item)) {
                        $filesize = filesize($final_dir . $_item);
                        $segments = explode('.', $_item);
                        $ext = array_pop($segments);
                        $file_name = implode('', $segments);
                        $file_path = $save_dir . str_replace('\\', '/', $group_dir). $_item;
                        try {
                            $video_model->allowField(true)->save([
                                'group_id' => $group['id'],
                                'name' => $file_name,
                                'path' => $file_path,
                                'ext' => $ext,
                                'filesize' => $filesize
                            ]);
                        } catch (\Exception $ex) {
                            Db::rollback();
                            return $this->error('发生错误：' . $ex->getMessage());
                        }
                    }
                }
            }
        }
        Db::commit();
        return $this->success('刷新成功');
    }

    protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = [
            'code' => 1,
            'msg' => $msg,
            'data' => $data
        ];
        return json_encode($body, JSON_UNESCAPED_UNICODE);
    }

    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = [
            'code' => 0,
            'msg' => $msg,
            'data' => $data
        ];
        return json_encode($body, JSON_UNESCAPED_UNICODE);
    }
}
