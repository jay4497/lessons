<?php
namespace app\apple\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'user_name|用户名' => 'require|max:150'
    ];
}
