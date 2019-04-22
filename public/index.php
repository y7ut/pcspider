<?php
/**
 * 项目入口文件
 * User: YiChu
 * Date: 2019/4/19
 * Time: 18:30
 */


// 引入自动加载
require __DIR__.'/../vendor/autoload.php';

date_default_timezone_set('UTC');

//初始化引导
require __DIR__.'/../app/bootstarp.php';

$application->run();


