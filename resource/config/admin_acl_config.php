<?php

//后台功能权限列表
return array(
	'membersys' => array(
        'name'         => '会员系统',
        'sub' => array(
            'member_role'    => array(
                'name'         => '角色设定',
                'sub' => array(
                ),
            ),
            'member_user' => array(
                'name'         => '会员设定',
                'sub' => array(
                ),
            ),
        )
    ),
    'adminsys' => array(
        'name'         => '管理员系统',
        'sub' => array(
            'admin_role'    => array(
                'name'         => '角色设定',
                'sub' => array(
                ),
            ),
            'admin_user' => array(
                'name'         => '管理员设定',
                'sub' => array(
                ),
            ),
        )
    ),
);
