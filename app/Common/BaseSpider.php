<?php
/**
 * 爬虫基类
 * User: YiChu
 * Date: 2019/4/19
 * Time: 18:48
 */

namespace App\Common;

use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseSpider
{
    /**
     * 抓取爬虫名字
     *
     * @var string
     */
    const SPIDER_NAME = '';

    /**
     * 抓取网站基础域名
     *
     * @var string
     */
    const SPIDER_HTTP_HOST = '';

    /**
     * 超时时间
     *
     * @var int
     */
    const TIME_OUT = 5;

    /**
     * HTTP客户端
     *
     * @var Client
     */
    protected static $httpClient;

    /**
     * 取得的数据集
     *
     * @var array
     */
    protected static $storage = [];

    /**
     * 实际操作存储数量
     *
     * @var int
     */
    protected $totalCount = 0;

    public function __construct(Client $client)
    {
        self::$httpClient = $client;
    }

    /**
     * 操作进度条
     *
     * @param int    $done
     * @param int    $total
     * @param string $doing
     * @param string $show
     * @param int    $size
     */
    protected function show_status(int $done, int $total, string $doing, string $show, $size = 50)
    {
        static $start_time;

        // if we go over our bound, just ignore it
        if ($done > $total) {
            return;
        }

        if (empty($start_time)) {
            $start_time = time();
        }
        $now = time();

        $perc = (float) ($done / $total);

        $bar = floor($perc * $size);

        $status_bar = "\r[";
        $status_bar .= str_repeat('=', $bar);
        if ($bar < $size) {
            $status_bar .= '>';
            $status_bar .= str_repeat(' ', $size - $bar);
        } else {
            $status_bar .= '=';
        }

        $disp = number_format($perc * 100, 0);

        $status_bar .= "] $disp%  $done/$total";

        $rate = ($now - $start_time) / $done;
        $left = $total - $done;
        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;

        $status_bar .= $doing.' 剩余: '.number_format($eta).' sec.  爬取已进行: '.number_format($elapsed).' sec.';

        echo "$status_bar  ";

        flush();

        // when done, send a newline
        if ($done == $total) {
            echo "\n";
            echo "$show\n";
        }
    }

    /**
     * 初始化抓取器，使用链式操作时，首先要使用该方法来初始化。
     *
     * @return BaseSpider
     */
    public static function setup(): BaseSpider
    {
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => static::SPIDER_HTTP_HOST,
            // You can set any number of default request options.
            'timeout' => static::TIME_OUT,
        ]);

        $spider = new static($client);

        return $spider;
    }

    /**
     * 直接打印抓取结果，可链式调用
     *
     * @param OutputInterface $output
     *
     * @return BaseSpider
     */
    public function dd(OutputInterface $output): BaseSpider
    {
        foreach (static::$storage as $item) {
            $output->writeln('----------------------');
            $output->writeln(json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        $output->writeln('----------------------');

        $output->writeln(sprintf('本次生产数据%s条', count(static::$storage)));
        $output->writeln(sprintf('存储新的生产数据%s条', $this->totalCount));

        return $this;
    }

    /**
     *保存日志
     *
     * @return BaseSpider
     */
    public function log(): BaseSpider
    {
        $log = fopen('./log/'.strtolower(static::SPIDER_NAME).'-'.date('Y-m-d', time()).'.log', 'w+');

        foreach (static::$storage as $item) {
            if (isset($item['is_new'])) {
                fwrite($log, '----------------------'.PHP_EOL);
                fwrite($log, json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL);
            }
        }
        fwrite($log, '----------------------'.PHP_EOL);
        fwrite($log, sprintf('本次生产数据%s条', count(static::$storage)).PHP_EOL);
        fwrite($log, sprintf(sprintf('存储新的生产数据%s条', $this->totalCount)).PHP_EOL);
        fclose($log);

        return $this;
    }

    /**
     * 执行抓取运行操作，将每个爬虫特有的逻辑，在这个方法中体现
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return BaseSpider
     */
    abstract public function run(): BaseSpider;

    /**
     * 保存抓取结果
     *
     * @return BaseSpider
     */
    abstract public function save(): BaseSpider;
}
