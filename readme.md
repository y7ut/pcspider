
## About PCSpider 

### 🕷️🕷️🕷️

This is a PHP crawler project that crawls court reports and referee documents from several websites.

一个用来抓取法院网站中的开庭报告和裁判文书的爬虫项目。
当前版本 `0.1.0` ,更新日期 2019.05.08 。

### 使用说明 

#### 安装

使用`composer install`安装依赖 

#### 使用

整个应用使用 `symfony/console` 构建，入口文件为 `public/index.php` ， 通过命令还来启动数据获取程序。


#### 法院的数据获取


```
# 目录
$ php public/index.php 
# 上海知识产权法院
$ php public/index.php shanghaispider -d
# 北京知识产权法院
$ php public/index.php beijingspider -d
# 广州知识产权法院
$ php public/index.php guangzhouspider -d
```

使用 `--log` 记录日志， `--dump` 在命令行输出结果， -h 查看帮助。

### 注意事项

北京法院由于网站机制问题，所以爬取速度较慢，并有一定几率超时。






