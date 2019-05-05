<?php
/**
 * 广州知识产权法院的抓取爬虫
 * User: YiChu
 * Date: 2019/4/19
 * Time: 18:47
 */

namespace App\Spider;

use App\Common\BaseSpider;
use App\Common\Model\Report;

class GuangzhouSpider extends BaseSpider
{
    /**
     * 爬虫域名
     *
     * @var string
     */
    public const SPIDER_HTTP_HOST = 'http://www.gipc.gov.cn';

    /**
     * 爬虫名字
     *
     * @var string
     */
    public const SPIDER_NAME = 'GuangzhouSpider';

    /**
     * 执行抓取运行操作，将每个爬虫特有的逻辑，在这个方法中体现
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return BaseSpider
     */
    public function run(): BaseSpider
    {
        if (null === static::$httpClient) {
            throw new \RuntimeException('Please setup Spider first');
        }

        $firstResponse = static::$httpClient->request('GET', '/data/front/fyggFront!ktggListAjax.action', [
            'query' => [
                'pageNo' => 1,
                'rowSize' => 50,
            ],
        ]);

        $data = json_decode($firstResponse->getBody()->getContents(), true)['attach'];
        foreach ($data['list'] as $item) {
            static::$storage[] = $item;
        }
        $page = $data['pageCount'];

        for ($i = 2; $i <= $page; ++$i) {
            $this->show_status($i, $page, '获取公告目录成功，开始尝试读取', '');
            $response = static::$httpClient->request('GET', '/data/front/fyggFront!ktggListAjax.action', [
                'query' => [
                    'pageNo' => $i,
                    'rowSize' => 50,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true)['attach']['list'];

            foreach ($data as $item) {
                static::$storage[] = $item;
            }
        }

        return $this;
    }

    /**
     * 保存抓取结果
     *
     * @return BaseSpider
     */
    public function save(): BaseSpider
    {
        $count = 0;
        foreach (static::$storage as $item) {
            ++$count;
            $report = Report::firstOrNew([
                'case_number' => $item['AH'],
            ]);
            if (!$report->id) {
                ++$this->totalCount;
            }
            $report->case_account = $item['AY'];
            $report->court = $item['FYMC'];
            $report->court_time = $item['KTKSSJ'];
            $report->court_address = $item['KTDD'];
            $report->court_judge = $item['KTZSFG'];
            $report->report_url = self::SPIDER_HTTP_HOST.'/data//front/fyggFront!ktggDetail.action?id='.$item['KTZSFG'];
            $report->save();
            $this->show_status($count, count(static::$storage), '正在存储至数据库', '本次共保存开庭报告数据'.$this->totalCount.'条。');
        }

        return $this;
    }
}
