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
use Symfony\Component\Console\Output\OutputInterface;

class GuangzhouCourt extends Command
{
    protected $spider;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent:: __construct();
        $this->spider = GuangzhouSpider::setup();
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
        $this->spider->run()->save();
    }
}
