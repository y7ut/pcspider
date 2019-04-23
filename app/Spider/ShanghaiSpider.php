<?php
/**
 * Created by PhpStorm.
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
        preg_match_all('/\d+/',$pageText,$page);
        $maxPage = $page[0][1];

        $table = $ql->find('table tr:eq(0) td')->texts()->toArray();

        $tableRows = $ql->find('tr:gt(0)')->map(function($row){
            return $row->find('td')->texts()->all();
        });

        static::$storage = array_merge(static::$storage,$tableRows->map(function ($item) use ($table){
            return array_combine($table,$item);
        })->toArray());

        for ($i = 2; $i <= $maxPage; ++$i) {
            $this->show_status($i,$maxPage,'获取公告目录成功，开始尝试读取','');
            $firstResponse = static::$httpClient->request('GET', 'ktgg.jhtml', [
                'query' => [
                    'lmdm' => 'ktgg',
                    'ds_p' => $i,
                ],
            ]);

            $ql = QueryList::html(strval($firstResponse->getBody()));

            $tableRows = $ql->find('tr:gt(0)')->map(function($row){
                return $row->find('td')->texts()->all();
            });

            static::$storage = array_merge(static::$storage,$tableRows->map(function ($item) use ($table){
                return array_combine($table,$item);
            })->toArray());
        }

//        var_dump($firstData);
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
            $report->report_url = self::SPIDER_HTTP_HOST."data//front/fyggFront!ktggDetail.action?id=".$item['KTZSFG'];
            $report->save();
            $this->show_status($count,count(static::$storage),'正在存储至数据库','本次共保存开庭报告数据'.$this->totalCount.'条。');
        }

        return $this;
    }
}
