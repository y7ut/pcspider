<?php
/**
 * 浙江知识产权法院的抓取爬虫
 * User: Yichu
 * Date: 2019/9/23
 * Time: 10:40
 */

namespace App\Spider;

use App\Common\BaseSpider;
use QL\QueryList;

class ZhejiangSpider extends BaseSpider{

    /**
     * 爬虫域名
     *
     * @var string
     */
    public const SPIDER_HTTP_HOST = 'http://www.zjsfgkw.cn';
    /**
     * 爬虫URI
     *
     * @var string
     */
    public const SPIDER_HTTP_URI = '/ZJSGY-jkts/search/ktgglist.do';
    /**
     * 头部设备信息
     *
     * @var string
     */
    public const SPIDER_HEADER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36';
    /**
     * 抓取等待时间，防止过于频繁被封禁
     *
     * @var int
     */
    public const SPIDER_WAIT_TIME = 1;
    /**
     * 抓取爬虫名字
     *
     * @var string
     */
    public const SPIDER_NAME = 'ZhejiangSpider';
    /**
     * 执行抓取运行操作，将每个爬虫特有的逻辑，在这个方法中体现
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return BaseSpider
     */
    public function run(): BaseSpider
    {
        //生成QL实例
        $ql = QueryList::getInstance();

        // TODO: Implement run() method.
        if (null === static::$httpClient) {
            throw new \RuntimeException('Please setup Spider first');
        }

        $firstResponse = static::$httpClient->request('GET', self::SPIDER_HTTP_URI, [
            'query' => [
                'fybh' => '',
                'bg' => '',
                'pageNo' => 1,
            ],
        ]);

        $qlFirst = $ql->html(strval($firstResponse->getBody()));


        //从首页获取页码
        $pageData = $qlFirst->find('#pagination>span')->texts()->all();
        $link = $pageData[0];
        preg_match_all('/\d+/', $link, $number);
        $caseCount = $number[0][0];
        $pageCount = $number[0][1];
        $this->show_status(1, $pageCount, '获取公告目录中', '');

        // 获取表格内容
        $tableHeader = $qlFirst->find('table tr:gt(1)')->map(function($row){
            return $row->find('td')->texts()->all();
        })->first();
        $tableContent = $qlFirst->find('table tr:gt(1)')->map(function($row) use ($tableHeader){

            $collection = collect($tableHeader)->combine($row->find('td')->texts()->all())->all();

            return $collection;
        })->toArray();
        array_shift($tableContent);
        //加入結果集
        static::$storage = array_merge(static::$storage, $tableContent);

        // 對其與的頁進行抓取
        for ($i = 2; $i <= $pageCount; ++$i) {
            //显示进度
            $this->show_status($i, $pageCount, '获取公告目录成功，开始尝试读取', '');
            $Response = static::$httpClient->request('GET', self::SPIDER_HTTP_URI, [
                'query' => [
                    'fybh' => '',
                    'bg' => '',
                    'pageNo' => $i,
                ],
            ]);

            $qlNext = $ql->html(strval($Response->getBody()));
            // 获取表格内容
            $tableHeader = $qlNext->find('table tr:gt(1)')->map(function($row){
                return $row->find('td')->texts()->all();
            })->first();
            $tableContent = $qlNext->find('table tr:gt(1)')->map(function($row) use ($tableHeader){

                $collection = collect($tableHeader)->combine($row->find('td')->texts()->all())->all();

                return $collection;
            })->toArray();
            array_shift($tableContent);
            //加入結果集
            static::$storage = array_merge(static::$storage, $tableContent);
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
        // TODO: Implement save() method.
        return $this;
    }
}