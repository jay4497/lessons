<?php
namespace app\common\traits;

trait Apple
{
    protected function echo_media($file)
    {
        set_time_limit(0);
        $fp = @fopen($file, 'rb');
        $size = filesize($file); // File size
        $length = $size; // Content length
        $start = 0; // Start byte
        $end = $size - 1; // End byte
        header('Content-type: video/mp4');
        header("Accept-Ranges: bytes");
        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end = $end;
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }

            if ($range == '-') {
                $c_start = $size - substr($range, 1);
            }else{
                $range = explode('-', $range);
                $c_start = $range[0];
                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            }
            $c_end = ($c_end > $end) ? $end : $c_end;

            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }
            $start = $c_start;
            $end = $c_end;
            $length = $end - $start + 1;
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');
        }
        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: ".$length);
        $buffer = 1024 * 8;
        while(!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end) {
                $buffer = $end - $p + 1;
            }
            echo fread($fp, $buffer);
            flush();
        }
        fclose($fp);
        exit();
    }

    protected function treeGroup($pid = 0)
    {
        $model = new \app\apple\model\Group;
        $groups = $model
            ->where('pid', $pid)
            ->select();
        if(!empty($groups)) {
            foreach ($groups as &$group) {
                $group['children'] = $this->treeGroup($group['uqid']);
            }
            unset($group);
        }
        return $groups;
    }

    protected function treeList($data, $depth = 0)
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
            $item['subs'] = count($children);
        }
        unset($item);
        return $list;
    }

    protected function getGroupDir($group_id)
    {
        $dir = [];
        $model = new \app\apple\model\Group;
        $group = $model->where('uqid', $group_id)->find();
        if(empty($group)){
            return $dir;
        }
        $dir = array_merge([$group['name']], $dir);
        if($group['pid']) {
            $dir = array_merge($this->getGroupDir($group['pid']), $dir);
        }
        return $dir;
    }

    protected function getGroupNav($group_id)
    {
        $nav = [];
        $model = new \app\apple\model\Group;
        $group = $model->where('uqid', $group_id)->find();
        if(empty($group)){
            return $nav;
        }
        $nav = array_merge([$group['display_name']], $nav);
        if($group['pid']) {
            $nav = array_merge($this->getGroupNav($group['pid']), $nav);
        }
        return $nav;
    }

    protected function getOptions()
    {
        $model = new \app\apple\model\Options;
        $option = $model->find();
        return $option;
    }

    protected function frontGroups()
    {
        $list = '<ul class="list-group">';
        $trees = $this->treeGroup();
        $tree_list = $this->treeList($trees);
        $last_parent = '';
        foreach ($tree_list as $_tree) {
            if($_tree['depth'] === 0) {
                $last_parent = $_tree['uqid'];
            }
            $common = $last_parent;
            if($_tree['depth'] === 0) {
                $common = '';
            }
            $flag = $last_parent. '-'. $_tree['depth'];
            $check_icon = '<i class="fas fa-angle-down"></i> ';
            $href = 'javascript:;';
            if($_tree['subs'] <= 0) {
                $check_icon = '';
                $href = url('index/index', ['id' => $_tree['id']]);
            }
            $list .= '<li class="list-group-item '. $common. ' '. $flag. '" data-flag="'. $flag. '" onclick="toggleChildren(this)">'
                . '<a href="'. $href. '">'
                . str_repeat('&nbsp;&nbsp;', $_tree['depth']). $check_icon. $_tree['display_name']
                . '</a></li>';
        }
        $list .= '<li class="list-group-item">'
            . '<a href="'. url('auth/user/logout'). '"><i class="fas fa-sign-in-alt"></i> 退 出</a>'
            . '</li>';
        $list .= '</ul>';
        return $list;
    }
}
