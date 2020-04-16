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
        $video_model_init = new \app\apple\model\Video;
        $groups = $group_model->select();

        Db::startTrans();
        $video_model_init->where('id', '>', 0)->delete();
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
                            $video_model = new \app\apple\model\Video([
                                'group_id' => $group['id'],
                                'name' => $file_name,
                                'path' => $file_path,
                                'ext' => $ext,
                                'filesize' => $filesize
                            ]);
                            $video_model->allowField(true)->save();
                        } catch (\Exception $ex) {
                            Db::rollback();
                            $this->error('发生错误：' . $ex->getMessage());
                        }
                    }
                }
            }
        }
        Db::commit();
        $this->success('刷新成功');
    }

    public function gerateGroup()
    {
        $base_dir = THINK_PATH. '..'. DIRECTORY_SEPARATOR. 'public'. DIRECTORY_SEPARATOR. 'videos'. DIRECTORY_SEPARATOR;
        $all_group_dirs = $this->allDir($base_dir, 0);
        Db::startTrans();
        $init_group_model = new \app\apple\model\Group;
        $init_group_model->where('id', '>', 0)->delete();
        foreach($all_group_dirs as $item) {
            $group_model = new \app\apple\model\Group;
            $segments = explode(DIRECTORY_SEPARATOR, $item['dir']);
            $group_name = array_pop($segments);
            $group_data = [
                'uqid' => $item['id'],
                'pid' => $item['pid'],
                'name' => $group_name,
                'display_name' => $group_name
            ];
            try {
                $group_model->allowField(true)->data($group_data)->save();
            } catch (\Exception $ex) {
                Db::rollback();
                $this->error('发生错误：' . $ex->getMessage());
            }
        }
        Db::commit();
        $this->success('自动创建成功');
    }

    private function allDir($dir, $pid = 0)
    {
        $trees = [];
        $dir_list = scandir($dir);
        foreach($dir_list as $_item) {
            if($_item == '.' || $_item == '..') {
                continue;
            }
            if(is_dir($dir. $_item)){
                array_push($trees, [
                    'dir' => $dir. $_item,
                    'id' => md5($dir. $_item),
                    'pid' => $pid
                ]);
                $trees = array_merge($trees, $this->allDir($dir. $_item. DIRECTORY_SEPARATOR, md5($dir. $_item)));
            }
        }
        return $trees;
    }

    protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = [
            'code' => 1,
            'msg' => $msg,
            'data' => $data
        ];
        exit(json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = [
            'code' => 0,
            'msg' => $msg,
            'data' => $data
        ];
        exit(json_encode($body, JSON_UNESCAPED_UNICODE));
    }
}
