<?php
//表单验证配置信息
return array(
    'default'   => array(
    ),
    'enneagram' => array(
        'username'  => array(
            'req' => '请填写姓名！',
        ),
        'telephone' => array(
            'req' => '请填写电话号码！',
        ),
    ),
    'admin'     => array(
        'username' => array(
            'req'                     => '请输入您的帐号！',
            'regexp=/^[A-Za-z0-9]*$/' => '帐号不能存在特殊字符！',
        ),
        'password' => array(
            'req' => '请输入您的密码！',
        ),
    ),
    'cms'       => array(
        'yespoID'         => array(
            'req' => '请填写YesPo IM ID',
        ),
        'yespoPassword'   => array(
            'req' => '请填写Password！',
        ),
        'import_system'   => array(
            'req' => '请选择要上传的系统',
        ),
        'ajax_key_id'     => array(
            'req' => '参数错误：缺少参数key_id',
        ),
        'ajax_lang'       => array(
            'req' => '参数错误：缺少参数lang',
        ),
        'edit_key_id'     => array(
            'req' => '参数错误：缺少参数key_id',
        ),
        'edit_lang'       => array(
            'req' => '参数错误：缺少参数lang',
        ),
        'download_system' => array(
            'req' => '请选择要下载的系统',
        ),
        'edit_system'     => array(
            'req' => '请选择系统',
        ),
        'edit_key'        => array(
            'req'                     => '请输入key code',
            'regexp=/^[A-Za-z0-9_]*$/' => 'key code 不能存在特殊字符',
        ),
    ),
);
