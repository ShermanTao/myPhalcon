<?php

namespace Sherman\Shermanapps\Models;

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\StringLength;
use Sherman\Lib\Validators\NumberCompareValidator;
use Sherman\Lib\Validators\DateCompareValidator;

class ShermanAppCards extends Model
{
    const DELETE = 3;

    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $account_id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var integer
     */
    public $status;

    /**
     * @var timestamp
     */
    public $create_at;

    /**
     * @var integer
     */
    public $start_time;

    /**
     * @var integer
     */
    public $end_time;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $qr_code;

    /**
     * @var string
     */
    public $pic;

    /**
     * @var integer
     */
    public $exchange_limit;

    /**
     * @var integer
     */
    public $stock_num;

    /**
     * @var integer
     */
    public $total_num;


    public function initialize()
    {
        $this->addBehavior(
            //软删除
            new SoftDelete(
                array(
                    'field' => 'status',
                    'value' => ShermanAppCards::DELETE
                )
            )
        );
    }

    public function beforeValidationOnCreate()
    {
        $this->status = 1;
        $this->create_at = date('Y-m-d H:i:s');
        if (!empty($this->end_time)) {
            $this->end_time = date('Y-m-d 23:59:00', strtotime($this->end_time));
        }
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(['name', 'start_time', 'end_time'], new PresenceOf([
            'message' => [
                'name' => '请输入卡券名',
                'start_time' => '请输入卡券有效期开始时间',
                'end_time' => '请输入卡券有效期结束时间',
            ]
        ]));

        $validator->add(['name', 'content'], new StringLength([
            'max' => [
                'name' => 12,
                'content' => 100,
            ],
            'min' => [
                'name' => 1,
                'content' => 1,
            ],
            'messageMaximum' => [
                'name' => '卡券名称不得超过12个字',
                'content' => '卡券描述不得超过30个字',
            ],
            'messageMinimum' => [
                'name' => '请输入卡券名称',
                'content' => '请输入卡券描述',
            ],
        ]));

        $validator->add('coupon_type', new InclusionIn(array(
            'message' => '状态不正确',
            'domain' => array('1', '2','3')
        )));

        $validator->add('start_time', new DateCompareValidator([
            'compare' => $this->end_time,
            'message' => '开始时间不得早于或等于结束时间'
        ]));

        $validator->add('stock_num', new NumberCompareValidator([
            'compare' => $this->exchange_limit,
            'order'   => 'lt',
            'message' => '每人领取上限不得超过卡券库存'
        ]));

        return $this->validate($validator);
    }
}