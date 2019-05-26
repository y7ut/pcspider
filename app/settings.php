<?php
/*
 * 配置信息.
 *
 * 这个文件最终是一个 return 语句，因此它是被设计为在赋值语句中 require 的。
 */

// 应用程序名称
define('APP_NAME', 'pcspider');

// 应用程序所处的阶段，在 pipelines 中处理这行，可能得值有 rel 和 dev
define('APP_STAGE', 'dev');

// 调试模式，主要用于控制日志等级和是否输出错误信息。
define('DEBUG', true);

// 返回的配置
//return [
//    'settings' => [
//        'db' => [
//            'default' => [
//                'driver' => 'mysql',
//                'host' => '172.18.172.158',
//                'port' => 3306,
//                'database' => 'jiwei',
//                'username' => 'jiweiscript',
//                'password' => 'Jt09Tdiv3BB2KLyW',
//                'charset' => 'utf8',
//                'collation' => 'utf8_unicode_ci',
//                'timezone' => '+08:00',
//            ],
//        ],
//    ],
//];
return [
    'settings' => [
        'db' => [
            'default' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => 'dev',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'timezone' => '+08:00',
            ],
        ],
    ],
];
