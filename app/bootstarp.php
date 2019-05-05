<?php
/**
 * 初始化项目的引导文件
 * User: YiChu
 * Date: 2019/4/22
 * Time: 11:37
 */
use App\Commands\BeijingCourt;
use App\Commands\GuangzhouCourt;
use App\Commands\ShanghaiCourt;
use Symfony\Component\Console\Application;

$settings = require __DIR__.'/settings.php';

// 初始化Eloquent ORM
$capsule = new \Illuminate\Database\Capsule\Manager();

$connectionList = [];

foreach ($settings['settings']['db'] as $name => $settings) {
    $capsule->addConnection($settings, $name);
    // 记录添加的连接名称
    $connectionList[] = $name;
}
$capsule->setAsGlobal();
$capsule->bootEloquent();

//初始化应用
$application = new Application();

//注册命令
$application->add(new GuangzhouCourt());
$application->add(new ShanghaiCourt());
$application->add(new BeijingCourt());
