<?php
namespace app\apple\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'user_name|用户名' => 'require|max:150',
        'password' => 'require',
        'password_confirm' => 'require|confirm:password'
    ];

    protected $scene = [
        'add' => ['user_name', 'password', 'password_confirm'],
        'update' => ['user_name']
    ];
}
