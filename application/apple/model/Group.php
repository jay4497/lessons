<?php
namespace app\apple\model;

use think\Db;
use think\Model;

class Group extends Model
{
    protected static function init()
    {
        parent::init();

        self::afterDelete(function($row) {
            Db::table('apl_group_user')
                ->where('group_id', $row->id)
                ->delete();
        });
    }
}
