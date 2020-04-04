<?php
namespace app\apple\controller;

use app\common\controller\Backend;

class Index extends Backend
{
    public function index()
    {
        $title = '后台管理';
        return view('', compact('title'));
    }
}
