<?php

namespace App\Commands;

use App\Common\Model\Report;
use App\Spider\DemoSpider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: YiChu
 * Date: 2019/4/22
 * Time: 11:12
 */
class GuangZhouCourt extends Command
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
            ->setDescription('广州知识产权法院')
            ->setHelp('获取广州知识产权法院的开庭报告信息');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        DemoSpider::setup()->run()->save()->dd($output);
    }
}
