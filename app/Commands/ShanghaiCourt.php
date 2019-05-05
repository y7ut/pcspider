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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShanghaiCourt extends Command
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent:: __construct();
    }

    protected function configure()
    {
        $this
            ->setName('spider:shanghaispider')
            ->setDescription('Shanghai Intellectual Property Court')
            ->setHelp('获取上海知识产权法院的开庭报告信息')
            ->addOption(
                'dump',
                'd',
                InputOption::VALUE_NONE,
                '是否输出结果在命令行中'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $is_dd = $input->getOption('dump');
        if($is_dd){
            ShanghaiSpider::setup()->run()->save()->dd($output);
        }else{
            ShanghaiSpider::setup()->run()->save();
        }
    }
}
