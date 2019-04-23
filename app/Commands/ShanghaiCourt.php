<?php
/**
 * 上海产权法院抓取Command
 * User: YiChu
 * Date: 2019/4/23
 * Time: 17:12
 */

namespace App\Commands;

use App\Spider\ShanghaiSpider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ShanghaiCourt extends Command
{
    protected $spider;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent:: __construct();
        $this->spider = ShanghaiSpider::setup();
    }

    protected function configure()
    {
        $this
            ->setName('spider:shanghaispider')
            ->setDescription('上海知识产权法院')
            ->setHelp('获取上海知识产权法院的开庭报告信息');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->spider->run()->dd($output);
    }
}
