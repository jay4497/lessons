<?php
namespace app\index\controller;

use app\common\controller\Frontend;

class Index extends Frontend
{
    /**
     * @var \app\apple\model\Video
     */
    protected $video_model;

    /**
     * @var \app\apple\model\Group
     */
    protected $group_model;

    public function _initialize()
    {
        parent::_initialize();

        $this->video_model = new \app\apple\model\Video;
        $this->group_model = new \app\apple\model\Group;
    }

    public function index($id = 0)
    {
        if(!in_array($id, $this->priv_ids)) {
            $this->error('权限受限', url('index/index'));
        }

        $group = $this->group_model
            ->where('id', $id)
            ->find();
        if(empty($group)) {

        } else {
            $group['apple_nav'] = implode(' / ', $this->getGroupNav($group['uqid']));
        }

        $videos = $this->video_model
            ->where('group_id', $id)
            ->paginate(20);

        $title = $group['display_name'];
        return view('', compact('title', 'videos', 'group'));
    }

    public function video($id)
    {
        $video = $this->video_model
            ->where('id', $id)
            ->find();
        if(empty($video)){
            $this->error('目标不存在');
        }
        if(!in_array($video['group_id'], $this->priv_ids)) {
            $this->error('权限不足');
        }

        $title = $video['name'];
        return view('', compact('title', 'video'));
    }

    public function play($id)
    {
        $video = $this->video_model
            ->where('id', $id)
            ->find();
        if(empty($video)){
            return '';
        }
        if(!in_array($video['group_id'], $this->priv_ids)) {
            return '';
        }
        $file = THINK_PATH. '..'. DIRECTORY_SEPARATOR. 'public'. $video['path'];
        //var_dump($file);exit;
        $this->echo_media($file);
    }
}
