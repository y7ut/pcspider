<?php
/**
 * 爬虫基类
 * User: YiChu
 * Date: 2019/4/19
 * Time: 18:48
 */

namespace App\Common;

use App\Common\Model\Report;
use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;

class BaseSpider
{
    const SPIDER_NAME = '';

    const SPIDER_HTTP_HOST = '';

    const TIME_OUT = 5;

    protected $lastResponse;

    protected static $httpClient;

    protected static $storage = [];

    public function __construct(Client $client)
    {
        self::$httpClient = $client;
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
        $output->writeln(count(static::$storage));

        return $this;
    }

    /**
     * 保存抓取结果
     *
     * @return BaseSpider
     */
    public function save(): BaseSpider
    {

        foreach (static::$storage as $item) {
            $report = Report::firstOrNew([
               'case_number' => $item['AH'],
           ]);
            $report->case_account = $item['AY'];
            $report->court = $item['FYMC'];
            $report->court_time = $item['KTKSSJ'];
            $report->court_address = $item['KTDD'];
            $report->court_judge = $item['KTZSFG'];
            $report->save();
        }

        return $this;
    }
}
