<?php
/**
 * 北京知识产权法院的抓取爬虫
 * User: YiChu
 * Date: 2019/4/23
 * Time: 15:03
 */

namespace App\Spider;

use App\Common\BaseSpider;
use QL\QueryList;

class BeijingSpider extends BaseSpider
{
    public const SPIDER_HTTP_HOST = 'http://bjzcfy.chinacourt.gov.cn';

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
        $firstResponse = static::$httpClient->request('GET', '/article/index/id/M7Q0NDAwBiPCAAA/page/1.shtml');


        $ql = QueryList::html(strval($firstResponse->getBody()));

        //获取页码
        $pageData = $ql->rules([
            'link'=>array('.paginationControl>a:eq(5)','href'),
//            'content'=>array('.left>a','href')
        ])->queryData();
        $maxPageNumber = $pageData[0]['link'];

        preg_match_all('/\d+/', $maxPageNumber, $pageFromUrl);

        $maxPageNumber = $pageFromUrl[0][count($pageFromUrl[0])-1];


        $contentCollection = [];

        //获取内容
        $pageData = $ql->rules([
            'content'=>array('.left>a','href')
        ])->query(function ($item) use ($contentCollection) {
            //            $this->show_status($i, $maxPageNumber, '获取公告目录成功，开始尝试读取', '');
            $Response = static::$httpClient->request('GET', $item['content']);
            $ql = QueryList::html(strval($Response->getBody()));
            $content = $ql->rules(['contents'=>['.text>p','text']])->queryData();
            $courtString = $content[3]['contents'];
            $court['case_number'] = $content[2]['contents'];
            $court['court_content'] = $courtString;
            return $court;
        })->getData();
        var_dump($pageData);





//        for ($i = 2; $i <= $maxPageNumber; ++$i) {
//            $this->show_status($i, $maxPageNumber, '获取公告目录成功，开始尝试读取', '');
//            $firstResponse = static::$httpClient->request('GET', 'article/index/id/M7Q0NDAwBiPCAAA/page/'.$i.'.shtml');
//
//            $ql = QueryList::html(strval($firstResponse->getBody()));
//
//            $tableRows = $ql->find('tr:gt(0)')->map(function ($row) {
//                return $row->find('td')->texts()->all();
//            });
//
//            static::$storage = array_merge(static::$storage, $tableRows->map(function ($item) use ($table) {
//                return array_combine($table, $item);
//            })->toArray());
//        }

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
