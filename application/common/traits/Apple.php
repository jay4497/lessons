<?php
namespace app\common\traits;

trait Apple
{
    protected function echo_media($file)
    {
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
            set_time_limit(0);
            echo fread($fp, $buffer);
            flush();
        }
        fclose($fp);
        exit();
    }

    protected function getGroupDir($group_id)
    {
        $dir = '';
        $model = new \app\apple\model\Group;
        $group = $model->where('id', $group_id)->find();
        if(empty($group)){
            return $dir;
        }
        $dir .= $group['name']. DIRECTORY_SEPARATOR;
        if($group['pid'] > 0) {
            $dir .= $this->getGroupDir($group['pid']);
        }
        return $dir;
    }

    protected function getGroupNav($group_id)
    {
        $nav = '';
        $model = new \app\apple\model\Group;
        $group = $model->where('id', $group_id)->find();
        if(empty($group)){
            return $nav;
        }
        $nav .= $group['display_name']. ' - ';
        if($group['pid'] > 0) {
            $nav .= $this->getGroupNav($group['pid']);
        }
        return $nav;
    }
}