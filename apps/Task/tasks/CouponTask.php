<?php
/**
 * Created by PhpStorm.
 * User: sherman
 * Date: 2016/9/6
 * Time: 20:26
 */
namespace Sherman\Task\Tasks;

use Sherman\Shermanapps\Models\ShermanAppCards;
use Sherman\Shermanapps\Models\ShermanCardLists;

class CouponTask extends \Phalcon\Cli\Task
{
    public function indexAction()
    {
        taskLog(['\nThis is the default task and the default action \n']);
    }

    /**
     * 生成卡券总数量
     */
    public function createTotalNumAction()
    {
        $appCards = ShermanAppCards::find([
            'columns' => 'id, stock_num, total_num',
        ]);
        foreach ($appCards as $v) {
            if ($v->total_num == 0) {
                $countArr = $this->queryCouponCount($v->id);
                $totalNum = $v->stock_num + $countArr['exchange_count'];
                $this->updateCouponCount($v->id, $totalNum);
            }
        }
    }

    /**
     * 查询卡券已领取数量和已核销数量
     * @param $cardId
     */
    protected function queryCouponCount($cardId)
    {
        //查询数据库
        $cardLists = ShermanCardLists::find([
            'columns'    => 'id, status',
            'conditions' => 'card_id = :card_id:',
            'bind'       => ['card_id' => $cardId],
        ]);

        $verifyCount = 0;
        $exchangeCount = count($cardLists);
        if ($exchangeCount != 0) {
            $cardLists = $cardLists->toArray();
            foreach ($cardLists as $k => $v) {
                //已核销
                if ($v['status'] == 2) {
                    $verifyCount += 1;
                }
            }
        }

        return [
            'exchange_count' => $exchangeCount,
            'verify_count'   => $verifyCount,
        ];
    }

    protected function updateCouponCount($cardId, $totalNum)
    {
        $phql = "UPDATE Sherman\Shermanapps\Models\AShermanAppCards SET total_num = %s WHERE id = %s";
        $phql = sprintf($phql, $totalNum, $cardId);
        $result = $this->modelsManager->executeQuery($phql);
        if ($result->success() == false) {
            $data = [
                'note'        => '========= UpdateCardCountError =========',
                'card_id'     => $cardId,
                'total_num'   => $totalNum,
            ];
            taskLog($data);
            return;
        }
    }
}