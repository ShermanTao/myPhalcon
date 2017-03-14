<?php


namespace Sherman\User\Models;

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation\Validator\InclusionIn;

class ShermanUsers extends Model
{
    const DELETED = 3;

    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $nickname;

    /**
     * @var integer
     */
    public $status;

    /**
     * @var string
     */
    public $mobile;

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
                    'value' => ShermanUsers::DELETED
                )
            )
        );
    }

    public function beforeValidationOnCreate()
    {
        // Set create_at
        $this->create_at = date('Y-m-d H:i:s');
        // Set status
        $this->status = 1;
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add('status', new InclusionIn([
            'message' => '用户状态类型不正确',
            'domain' => ['1', '2', '3'],
        ]));
        return $this->validate($validator);
    }

}
