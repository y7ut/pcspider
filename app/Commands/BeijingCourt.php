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
            ->setHelp('获取北京知识产权法院的开庭报告信息 ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        BeijingSpider::setup()->run()->save();
    }
}
