<?php
/**
 * 北京知识产权法院的抓取爬虫
 *
 * 北京法院的爬虫会因为访问过于频繁 针对爬虫返回重定向的304规则，目前不去针对这个问题做处理，只是调大时间间隔来解决问题。
 *
 * User: YiChu
 * Date: 2019/4/23
 * Time: 15:03
 */

namespace App\Spider;

use App\Common\BaseSpider;
use App\Common\Model\Report;
use QL\QueryList;

class BeijingSpider extends BaseSpider
{
    /**
     * 爬虫域名
     *
     * @var string
     */
    public const SPIDER_HTTP_HOST = 'http://bjzcfy.chinacourt.gov.cn';
    /**
     * 爬虫URI
     *
     * @var string
     */
    public const SPIDER_HTTP_URI = 'article/index/id/M7Q0NDAwBiPCAAA/page/%s.shtml';
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
    public const SPIDER_NAME = 'BeijingSpider';

    /**
     * 日期字典
     *
     * @var array
     */
    public const NUMBER_DICTIONARY = ['〇' => 0, '一' => 1, '二' => 2, '三' => 3, '四' => 4, '五' => 5, '六' => 6, '七' => 7, '八' => 8, '九' => 9, '十' => 10];

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

        if (null === static::$httpClient) {
            throw new \RuntimeException('Please setup Spider first');
        }
        $firstResponse = static::$httpClient->request('GET', sprintf(self::SPIDER_HTTP_URI, 1));

        //先获取首页的信息
        $qlFirst = $ql->html(strval($firstResponse->getBody()));

        //从首页获取页码
        $pageData = $qlFirst->rules([
            'link' => ['.paginationControl>a:eq(5)', 'href'],
        ])->queryData();

        if (empty($pageData)) {
            throw new \RuntimeException('访问过于频繁，请降低频率');
        }
        // 获取最大的页码
        $maxPageNumber = $pageData[0]['link'];
        preg_match_all('/\d+/', $maxPageNumber, $pageFromUrl);
        $maxPageNumber = $pageFromUrl[0][count($pageFromUrl[0]) - 1];
        $this->show_status(1, $maxPageNumber, '获取公告目录中', '');



        //获取这个页面全部的开庭信息的匿名方法
        $doQuery = function ($item) use ($ql) {
            $Response = static::$httpClient->request('GET', $item['content'], [
                'headers' => [
                    'User-Agent' => self::SPIDER_HEADER_AGENT,
                ],
            ]);

            //执行QueryList ,获取详细的页面内容
            $content = $ql->html(strval($Response->getBody()))->rules(['contents' => ['.text>p', 'text']])->queryData();
            //获取案号
            if(isset($content[2]['contents'])){
                $court['case_number'] = $content[2]['contents'];
            }else{
                $court['case_number'] = null;
            }

            //拼装每个公告的信息
            if(isset($content[3]['contents'])){
                $courtString = $content[3]['contents'];
                $court['court_time'] = $this->makeTimeFormat(substr($courtString, strpos($courtString, '定于') + 6, 55));
                $court['court_address'] = substr($courtString, strpos($courtString, '本院') + 6, (strpos($courtString, '依法') - strpos($courtString, '本院') - 6));
                $court['indicter'] = substr($courtString, strpos($courtString, '审理') + 6, (strpos($courtString, '与') - strpos($courtString, '审理') - 6));
                $court['accused'] = substr($courtString, strpos($courtString, '与') + 3, (strpos($courtString, '一案') - strpos($courtString, '与') - 3));

                //对不同·案由进行切割字符串 ，这个地方有待优化
                if(substr($court['accused'],-6) == '其他'){
                    $court['case_account'] = substr($court['accused'],-6);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'其他'));
                }elseif (substr($court['accused'],-33) == '侵害实用新型专利权纠纷'){
                    $court['case_account'] = substr($court['accused'],-33);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }elseif (substr($court['accused'],-33) == '侵害外观设计专利权纠纷'){
                    $court['case_account'] = substr($court['accused'],-33);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }elseif (substr($court['accused'],-27) == '侵害发明专利权纠纷'){
                    $court['case_account'] = substr($court['accused'],-27);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }elseif (substr($court['accused'],-36) == '侵害计算机软件著作权纠纷'){
                    $court['case_account'] = substr($court['accused'],-36);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }elseif (substr($court['accused'],-21) == '侵害商标权纠纷'){
                    $court['case_account'] = substr($court['accused'],-21);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }elseif (substr($court['accused'],-33) == '计算机软件开发合同纠纷'){
                    $court['case_account'] = substr($court['accused'],-33);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }elseif (substr($court['accused'],-33) == '商标权撤销复审行政纠纷'){
                    $court['case_account'] = substr($court['accused'],-33);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }elseif (substr($court['accused'],-39) == '商标权无效宣告请求行政纠纷'){
                    $court['case_account'] = substr($court['accused'],-39);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }elseif (substr($court['accused'],-24) == '技术转化合同纠纷'){
                    $court['case_account'] = substr($court['accused'],-24);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }elseif (substr($court['accused'],-21) == '不正当竞争纠纷'){
                    $court['case_account'] = substr($court['accused'],-21);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }elseif (substr($court['accused'],-21) == '专利权权属纠纷'){
                    $court['case_account'] = substr($court['accused'],-21);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }elseif (substr($court['accused'],-24) == '特许经营合同纠纷'){
                    $court['case_account'] = substr($court['accused'],-24);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }elseif (substr($court['accused'],-24) == '专利代理合同纠纷'){
                    $court['case_account'] = substr($court['accused'],-24);
                    $court['accused'] = substr($court['accused'],0,strpos($court['accused'],'侵害'));
                }else{
                    $court['case_account'] = '未提供案由';
                }
            }else{
                $courtString = '源页面信息格式有误';
                $court['case_account'] = $courtString;
                $court['accused'] = $courtString;
                $court['court_time'] = null;
                $court['court_address'] = $courtString;
                $court['indicter'] = $courtString;
                $court['accused'] = $courtString;

            }

            // 太快会被封啊！ 随缘睡觉法
            if (rand(1, 20000) > 10000) {
                sleep(self::SPIDER_WAIT_TIME);
            }

            $court['report_url'] = self::SPIDER_HTTP_HOST.$item['content'];
            return $court;
        };

        //获取内容，传入匿名方法
        $pageData = $qlFirst->rules([
            'content' => ['.left>a', 'href'],
        ])->query($doQuery)->getData()->toArray();

        //加入結果集
        static::$storage = array_merge(static::$storage, $pageData);

        // 對其與的頁進行抓取
        for ($i = 2; $i <= $maxPageNumber; ++$i) {
            //显示进度
            $this->show_status($i, $maxPageNumber, '获取公告目录成功，开始尝试读取', '');
            $firstResponse = static::$httpClient->request('GET', sprintf(self::SPIDER_HTTP_URI, $i));

            $qlNext = $ql->html(strval($firstResponse->getBody()));

            $pageData = $qlNext->rules([
                'content' => ['.left>a', 'href'],
            ])->query($doQuery)->getData()->toArray();
            static::$storage = array_merge(static::$storage, $pageData);
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
            if (is_null($item['case_number'])){
                $this->show_status($count, count(static::$storage), '正在存储至数据库', '本次共保存开庭报告数据'.$this->totalCount.'条。');
                continue;
            }
            $report = Report::firstOrNew([
                'case_number' => $item['case_number'],
            ]);
            if (!$report->id) {
                ++$this->totalCount;
            }
            $report->case_account = $item['case_account'];
            $report->court = '北京知识产权法院';
            $report->court_time = $item['court_time'];
            $report->court_address = $item['court_address'];
            $report->court_judge = '北京法院未提供主审人';
            $report->indicter = $item['indicter'];
            $report->accused = $item['accused'];
            $report->report_url = $item['report_url'];
            try{
                $report->save();
            }catch (\Exception $e){
                --$this->totalCount;
                $this->show_status($count, count(static::$storage), '正在存储至数据库', '本次共保存开庭报告数据'.$this->totalCount.'条。');
                continue;
            }

            $this->show_status($count, count(static::$storage), '正在存储至数据库', '本次共保存开庭报告数据'.$this->totalCount.'条。');
        }
        return $this;
    }

    /**
     * 格式化显示的时间
     *
     * '二〇一九年四月十九日 上午九时三十分'-> '2019-04-19 10:00:00'
     *
     * @param $timeString
     *
     * @return false|string
     */
    public function makeTimeFormat($timeString): ?string
    {
        //取到各时间单位的值
        $year = substr($timeString, 0, 12);
        $month = substr($timeString, strpos($timeString, '年') + 3, (strpos($timeString, '月') - strpos($timeString, '年') - 3));
        $day = substr($timeString, strpos($timeString, '月') + 3, (strpos($timeString, '日') - strpos($timeString, '月') - 3));
        $hour = substr($timeString, strpos($timeString, '午') + 3, (strpos($timeString, '时') - strpos($timeString, '午') - 3));
        $minute = substr($timeString, strpos($timeString, '时') + 3, (strpos($timeString, '分') - strpos($timeString, '时') - 3));
        $date = '';
        //处理年，直接读取
        foreach (str_split($year, 3) as $item) {
            $date .= self::NUMBER_DICTIONARY[$item];
        }
        //处理月份 直接读取相加
        $date .= '00';
        foreach (str_split($month, 3) as $item) {
            $date += self::NUMBER_DICTIONARY[$item];
        }
        //处理日份 直接读取相加，但是类似三十一、二十一特殊处理
        $date .= '00';
        if (false === strpos($day, '十') || 0 === strpos($day, '十')) {
            foreach (str_split($day, 3) as $item) {
                $date += self::NUMBER_DICTIONARY[$item];
            }
        } else {
            $son = array_slice(str_split($day, 3), 1, 2);
            foreach ($son as $item) {
                $date += self::NUMBER_DICTIONARY[$item];
            }
            $date += (self::NUMBER_DICTIONARY[str_split($day, 3)[0]] - 1) * 10;
        }
        //处理小时 直接读取相加，但是类似三十一、二十一 、五十五 特殊处理
        $date .= '00';
        if (false === strpos($hour, '十') || 0 === strpos($hour, '十')) {
            foreach (str_split($hour, 3) as $item) {
                $date += self::NUMBER_DICTIONARY[$item];
            }
        } else {
            $son = array_slice(str_split($hour, 3), 1, 2);
            foreach ($son as $item) {
                $date += self::NUMBER_DICTIONARY[$item];
            }
            $date += (self::NUMBER_DICTIONARY[str_split($hour, 3)[0]] - 1) * 10;
        }
        $date .= '00';
        //处理分钟 直接读取相加，但是类似三十一、二十一 、五十五 特殊处理 但是若是 什么十七时整则直接返回00
        if (false !== $minute) {
            if (false === strpos($minute, '十') || 0 === strpos($minute, '十')) {
                foreach (str_split($minute, 3) as $item) {
                    $date += self::NUMBER_DICTIONARY[$item];
                }
            } else {
                $son = array_slice(str_split($minute, 3), 1, 2);
                foreach ($son as $item) {
                    $date += self::NUMBER_DICTIONARY[$item];
                }
                $date += (self::NUMBER_DICTIONARY[str_split($minute, 3)[0]] - 1) * 10;
            }
        }
        //格式化显示
        return date('Y-m-d H:i:s', strtotime($date));
    }
}
