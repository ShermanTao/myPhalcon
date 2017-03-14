<?php

namespace Sherman\Shermanapps\Models;

use Phalcon\Mvc\Model;

class ShermanCardLists extends Model
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $account_id;

    /**
     * @var integer
     */
    public $user_id;

    /**
     * @var integer
     */
    public $card_id;

    /**
     * @var string
     */
    public $sn_number;

    /**
     * @var string
     */
    public $status;

    /**
     * @var integer
     */
    public $send_time;

    /**
     * @var integer
     */
    public $use_time;

    /**
     * @var string
     */
    public $explain_info;

}