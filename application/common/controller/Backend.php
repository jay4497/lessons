<?php
namespace app\common\controller;

use app\apple\model\Group;
use think\Controller;

class Backend extends Controller
{
    protected function jsTree($pid = 0)
    {
        $model = new Group;
        $groups = $model
            ->field('id, display_name, pid')
            ->where('pid', $pid)
            ->select();
        foreach ($groups as &$group) {
            $group['text'] = $group['display_name'];
            $group['children'] = $this->jsTree($group['id']);
        }
        unset($group);
        return $groups;
    }
}
