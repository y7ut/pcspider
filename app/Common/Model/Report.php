<?php

namespace App\Common\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * 开庭报告数据模型
 * User: YiChu
 * Date: 2019/4/22
 * Time: 14:16
 */
class Report extends Model
{
    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = ['case_number', 'case_account', 'court', 'court_time', 'court_address', 'court_judge', 'report_url', 'create_time', 'update_time'];
}
