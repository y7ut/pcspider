<?php
/**
 * 浙江法院抓取Command
 * User: Yichu
 * Date: 2019/9/23
 * Time: 10:13
 */

namespace App\Commands;

use App\Spider\ZhejiangSpider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ZhejiangCourt extends Command
{

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent:: __construct();
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('spider:zhejiangspider')
            ->setDescription('Zhejiang Intellectual Property Court')
            ->setHelp('获取浙江法院的开庭报告信息 ')
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
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $is_dd = $input->getOption('dump');
        $is_log = $input->getOption('log');
        if ($is_dd) {
            ZhejiangSpider::setup()->run()->save()->dd($output);
        } elseif ($is_log) {
            ZhejiangSpider::setup()->run()->save()->dd($output)->log();
        } else {
            ZhejiangSpider::setup()->run()->save();
        }
    }
}
