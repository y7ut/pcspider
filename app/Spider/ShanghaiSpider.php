<?php
/**
 * 上海知识产权法院的抓取爬虫
 * User: YiChu
 * Date: 2019/4/23
 * Time: 15:03
 */

namespace App\Spider;

use App\Common\BaseSpider;
use App\Common\Model\Report;
use QL\QueryList;

class ShanghaiSpider extends BaseSpider
{
    public const SPIDER_HTTP_HOST = 'http://www.shzcfy.gov.cn/';

    public const SPIDER_NAME = 'ShanghaiSpider';

    /**
     * 执行抓取运行操作，将每个爬虫特有的逻辑，在这个方法中体现
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return ShanghaiSpider
     */
    public function run(): ShanghaiSpider
    {
        if (null === static::$httpClient) {
            throw new \RuntimeException('Please setup Spider first');
        }

        $firstResponse = static::$httpClient->request('GET', 'ktgg.jhtml', [
            'query' => [
                'lmdm' => 'ktgg',
                'ds_p' => 1,
            ],
        ]);

        $ql = QueryList::html(strval($firstResponse->getBody()));

        $pageText = $ql->find('.black2>a')->texts()->first();
        preg_match_all('/\d+/', $pageText, $page);
        $maxPage = $page[0][1];

        $table = $ql->find('table tr:eq(0) td')->texts()->toArray();

        $tableRows = $ql->find('tr:gt(0)')->map(function ($row) {
            return $row->find('td')->texts()->all();
        });

        static::$storage = array_merge(static::$storage, $tableRows->map(function ($item) use ($table) {
            return array_combine($table, $item);
        })->toArray());

        for ($i = 2; $i <= $maxPage; ++$i) {
            $this->show_status($i, $maxPage, '获取公告目录成功，开始尝试读取', '');
            $firstResponse = static::$httpClient->request('GET', 'ktgg.jhtml', [
                'query' => [
                    'lmdm' => 'ktgg',
                    'ds_p' => $i,
                ],
            ]);

            $ql = QueryList::html(strval($firstResponse->getBody()));

            $tableRows = $ql->find('tr:gt(0)')->map(function ($row) {
                return $row->find('td')->texts()->all();
            });

            static::$storage = array_merge(static::$storage, $tableRows->map(function ($item) use ($table) {
                return array_combine($table, $item);
            })->toArray());
        }

        return $this;
    }

    /**
     * 保存抓取结果
     *
     * @return ShanghaiSpider
     */
    public function save(): ShanghaiSpider
    {
        $count = 0;
        foreach (static::$storage as $item) {
            ++$count;
            $report = Report::firstOrNew([
                'case_number' => $item['案号'],
            ]);
            if (!$report->id) {
                ++$this->totalCount;
            }
            $report->case_account = $item['案由'];
            $report->court = $item['法庭'];
            $report->court_time = $this->makeTimeFormat($item['开庭日期']);
            $report->court_address = $item['承办部门'];
            $report->court_judge = $item['审判长/主审人'];
            $report->indicter = $item['原告/上诉人'];
            $report->accused = $item['被告/被上诉人'];
            $report->report_url = '';
            $report->save();
            $this->show_status($count, count(static::$storage), '正在存储至数据库', '本次共保存开庭报告数据'.$this->totalCount.'条。');
        }

        return $this;
    }

    /**
     * 转换日期格式 (例如 ： 2019年04月26日上午10点00分  -> 2019-04-26 10:00:00  )
     *
     * @param $pageText
     *
     * @return string
     */
    public function makeTimeFormat($pageText)
    {
        preg_match_all('/\d+/', $pageText, $page);

        if ('上午' == substr($pageText, strpos($pageText, '日') + 3, 6)) {
            echo '上午';

            return $page[0][0].'-'.$page[0][1].'-'.$page[0][2].' '.$page[0][3].':'.$page[0][4].':00';
        } else {
            if ($page[0][3] >= 12) {
                return $page[0][0].'-'.$page[0][1].'-'.$page[0][2].' '.($page[0][3]).':'.$page[0][4].':00';
            }

            return $page[0][0].'-'.$page[0][1].'-'.$page[0][2].' '.($page[0][3] + 12).':'.$page[0][4].':00';
        }
    }
}
