<?php
namespace app\apple\model;

use think\Model;

class Video extends Model
{
    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row) {
            $now = date('Y-m-d H:i:s');
            $row->created_at = $now;
            $row->updated_at = $now;
        });
        self::beforeUpdate(function ($row) {
            $now = date('Y-m-d H:i:s');
            $row->updated_at = $now;
        });
    }
}
