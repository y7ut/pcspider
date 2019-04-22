<?php
/**
 * Created by PhpStorm.
 * User: YiChu
 * Date: 2019/4/19
 * Time: 18:47
 */

namespace App\Spider;

use App\Common\BaseSpider;

class DemoSpider extends BaseSpider
{
    public const SPIDER_HTTP_HOST = 'http://www.gipc.gov.cn/';

    public const SPIDER_NAME = 'DemoSpider';

    /**
     * 执行抓取运行操作，将每个爬虫特有的逻辑，在这个方法中体现
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return DemoSpider
     */
    public function run(): DemoSpider
    {
        if (null === static::$httpClient) {
            throw new \RuntimeException('Please setup Spider first');
        }

        $firstResponse = static::$httpClient->request('GET', 'data/front/fyggFront!ktggListAjax.action', [
            'query' => [
                'pageNo' => 1,
                'rowSize' => 100,
            ],
        ]);

        $data = json_decode($firstResponse->getBody()->getContents(), true)['attach'];
        foreach ($data['list'] as $item) {
            static::$storage[] = $item;
        }
        $page = $data['pageCount'];

        for ($i = 2; $i <= $page; ++$i) {
            $response = static::$httpClient->request('GET', 'data/front/fyggFront!ktggListAjax.action', [
                'query' => [
                    'pageNo' => $i,
                    'rowSize' => 100,
                ],
            ]);
            echo $i;
            $data = json_decode($response->getBody()->getContents(), true)['attach']['list'];

            foreach ($data as $item) {
                static::$storage[] = $item;
            }
        }

        return $this;
    }
}
