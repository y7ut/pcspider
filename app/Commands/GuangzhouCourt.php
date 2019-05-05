<?php
/**
 * 广州产权法院抓取Command
 * User: YiChu
 * Date: 2019/4/22
 * Time: 11:12
 */

namespace App\Commands;

use App\Spider\GuangzhouSpider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GuangzhouCourt extends Command
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
            ->setName('spider:guangzhoucourt')
            ->setDescription('Guangzhou Intellectual Property Court')
            ->setHelp('获取广州知识产权法院的开庭报告信息')
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
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $is_dd = $input->getOption('dump');
        if($is_dd){
            GuangzhouSpider::setup()->run()->save()->dd($output);
        }else{
            GuangzhouSpider::setup()->run()->save();
        }
    }
}
