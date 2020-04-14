<?php
namespace app\apple\model;

use think\Db;
use think\Model;

class Group extends Model
{
    protected static function init()
    {
        parent::init();

        self::beforeInsert(function($row) {
            $now = date('Y-m-d H:i:s');
            $row->created_at = $now;
            $row->updated_at = $now;
        });
        self::beforeUpdate(function($row) {
            $row->updated_at = date('Y-m-d H:i:s');
        });
        self::afterDelete(function($row) {
            Db::table('apl_group_user')
                ->where('group_id', $row->id)
                ->delete();
        });
    }
}
