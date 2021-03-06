<?php
/**
 * 北京产权法院抓取Command
 * User: YiChu
 * Date: 2019/4/23
 * Time: 17:12
 */

namespace App\Commands;

use App\Spider\BeijingSpider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BeijingCourt extends Command
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent:: __construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('spider:beijingspider')
            ->setDescription('Beijing Intellectual Property Court')
            ->setHelp('获取北京知识产权法院的开庭报告信息 ')
            ->addOption(
                'dump',
                'd',
                InputOption::VALUE_NONE,
                '是否输出结果在命令行中'
            )->addOption(
                'log',
                'l',
                InputOption::VALUE_NONE,
                '是否输出结果在日志中'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $is_dd = $input->getOption('dump');
        $is_log = $input->getOption('log');
        if ($is_dd) {
            BeijingSpider::setup()->run()->save()->dd($output);
        } elseif ($is_log) {
            BeijingSpider::setup()->run()->save()->dd($output)->log();
        } else {
            BeijingSpider::setup()->run()->save();
        }
    }
}
