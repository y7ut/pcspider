<?php
/**
 * 北京知识产权法院的抓取爬虫
 * User: YiChu
 * Date: 2019/4/23
 * Time: 15:03
 */

namespace App\Spider;

use App\Common\BaseSpider;

class BeijingSpider extends BaseSpider
{
    public const SPIDER_HTTP_HOST = 'http://bjzcfy.chinacourt.gov.cn/';

    public const SPIDER_NAME = 'BeijingSpider';

    /**
     * 执行抓取运行操作，将每个爬虫特有的逻辑，在这个方法中体现
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return BeijingSpider
     */
    public function run(): BeijingSpider
    {
        if (null === static::$httpClient) {
            throw new \RuntimeException('Please setup Spider first');
        }

        return $this;
    }

    /**
     * 保存抓取结果
     *
     * @return BeijingSpider
     */
    public function save(): BeijingSpider
    {
        return $this;
    }
}
