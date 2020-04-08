<?php
namespace app\apple\model;

use think\Db;
use think\Model;

class User extends Model
{
    protected static function init()
    {
        parent::init();

        $now = date('Y-m-d H:i:s');
        self::beforeInsert(function($row) use ($now) {
            $row->created_at = $now;
            $row->updated_at = $now;
        });
        self::beforeUpdate(function($row) use ($now) {
            $row->updated_at = $now;
        });
        self::afterDelete(function ($row) {
            Db::table('apl_group_user')
                ->where('user_id', $row->id)
                ->delete();
        });
    }
}
