<?php

namespace Sherman\Account\Models;

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\InclusionIn;

class ShermanAccounts extends Model
{
    const DELETED = 2;

    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $password;

    /**
     * @var integer
     */
    public $status;

    /**
     * @var timestamp
     */
    public $create_at;

    public function initialize()
    {
        $this->addBehavior(
            //软删除
            new SoftDelete(
                array(
                    'field' => 'status',
                    'value' => ShermanAccounts::DELETED
                )
            )
        );
    }

    public function beforeValidationOnCreate()
    {
        // Set create_at
        $this->create_at = date('Y-m-d H:i:s');
        // Set password
        $this->password = $this->getDI()->getSecurity()->hash($this->password);
        // Set status
        $this->status = 1;
    }

    public function beforeUpdate()
    {
        // Set password
        //$this->password = $this->getDI()->getSecurity()->hash($this->password);
    }

    public function validation()
    {
        $validator = new Validation();
        $validator->add('name', new PresenceOf([
            "message" => "请输入账号名称"
        ]));
        $validator->add('name', new Uniqueness([
            "message" => "此账号名称已被使用，请更换账号用户名"
        ]));
        $validator->add('password', new PresenceOf([
            "message" => "请输入登录密码"
        ]));
        $validator->add('status', new InclusionIn(array(
            'message' => '账号状态不正确',
            'domain' => array('1', '2')
        )));
        return $this->validate($validator);
    }

}
