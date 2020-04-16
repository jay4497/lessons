<?php
namespace app\apple\validate;

use think\Validate;

class Options extends Validate
{
    protected $rule = [
        'site_name' => 'require|max:100'
    ];
}
