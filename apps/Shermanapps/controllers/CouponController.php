<?php

namespace Sherman\Shermanapps\Controllers;

use PHPExcel;
use Sherman\Shermanapps\Models\ShermanAppCards;
use Sherman\Shermanapps\Models\ActAppCouponMores;
use Sherman\Shermanapps\Models\ShermanAppCardsWechat;
use Sherman\Shermanapps\Models\ActCouponLists;
use Sherman\Shermanapps\Models\ActProcessApps;
use Sherman\Shermanapps\Models\ActProcessCouponApps;
use Sherman\Shermanapps\Models\ActAppPays;
use Sherman\Shermanapps\Models\ActAccountAddresss;
use Sherman\User\Models\ShermanUsers;

class CardController extends ControllerBase
{
    const MARK_COUPON = 'coupon';

    public function notFoundAction()
    {
        $this->view->disable();
        return parent::getReturnMsg(404, '', 'This is crazy, no api support!');
    }

    /**
     * 单张卡券查询
     * @return
     */
    public function findAction($id)
    {
        $ShermanAppCards = ShermanAppCards::findFirstById(decode($id));
        if (!$ShermanAppCards) {
            return parent::getReturnMsg(200, '', '没有对应卡券信息');
        }

        $ShermanAppCards->id = encode($ShermanAppCards->id);
        $ShermanAppCards->account_id = encode($ShermanAppCards->account_id);
        return parent::getReturnMsg(200, $ShermanAppCards, 'ok');
    }


    /**
     * Creates a coupon
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            return parent::getReturnMsg(400, '', '参数异常');
        }

        $ShermanAppCards = new ShermanAppCards();
        $ShermanAppCards->account_id = parent::getAccountId();

        $paramArr = [
            'name',
            'pic',
            'start_time',
            'end_time',
            'content',
            'qr_code',
            'exchange_limit',
            'stock_num',
        ];
        $requestData = $this->request->getJsonRawBody();
        foreach ($paramArr as $v) {
            if (isset($requestData->$v) && !empty($requestData->$v)) {
                $ShermanAppCards->$v = $requestData->$v;
            }
        }

        if (!$ShermanAppCards->create()) {
            return parent::processDbError($ShermanAppCards);
        }

        $ShermanAppCards->id = encode($ShermanAppCards->id);
        $ShermanAppCards->account_id = encode($ShermanAppCards->account_id);
        return parent::getReturnMsg(201, $ShermanAppCards, 'ok');
    }

    /**
     * Edit the coupon
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function editAction()
    {
        if ($this->request->isPost()) {
            $requestData = $this->request->getJsonRawBody();

            $id = decode($requestData->id);

            $ShermanAppCards = ShermanAppCards::findFirstById($id);
            if (!$ShermanAppCards) {
                return parent::getReturnMsg(200, '', '没有对应卡券信息');
            }

            if (strtotime($requestData->end_time) != strtotime($ShermanAppCards->end_time)) {
                $ShermanAppCards->end_time = date('Y-m-d 23:59:00', strtotime($requestData->end_time));
            }

            $paramArr = [
                'name',
                'pic',
                'start_time',
                'content',
                'qr_code',
                'exchange_limit',
                'stock_num',
            ];
            foreach ($paramArr as $v) {
                if (isset($requestData->$v) && !empty($requestData->$v)) {
                    $ShermanAppCards->$v = $requestData->$v;
                }
            }

            if (!$ShermanAppCards->save()) {
                return parent::processDbError($ShermanAppCards);
            }

            $ShermanAppCards->id = encode($ShermanAppCards->id);
            $ShermanAppCards->account_id = encode($ShermanAppCards->account_id);
            return parent::getReturnMsg(201, $ShermanAppCards, 'ok');
        }
    }

    /**
     * @func 卡券开启/关闭
     */
    public function statusAction()
    {
        if (!$this->request->isPost()) {
            return parent::getReturnMsg(400, '', '参数异常');
        }

        $requestData = $this->request->getJsonRawBody();
        $ShermanAppCards = ShermanAppCards::findFirstById(decode($requestData->id));
        if (!$ShermanAppCards) {
            return parent::getReturnMsg(200, '', '没有对应卡券信息');
        }

        $ShermanAppCards->status = $requestData->status;
        if (!$ShermanAppCards->save()) {
            return parent::processDbError($ShermanAppCards);
        }

        return parent::getReturnMsg(201, $ShermanAppCards, 'ok');
    }


    /**
     * @func 根据帐号拉取卡券
     */
    public function listAction()
    {
        $page = $this->request->get('page', 'int', 1);//页码
        $number = $this->request->get('number', 'int', 10);//每页条数
        $offset = ($page - 1) * $number;

        if ($page < 0 || $number > 50) {
            return parent::getReturnMsg(401, '', '页码参数格式不正确');
        }

        $conditions = 'account_id = :account_id:';
        $binds = ['account_id' => parent::getAccountId()];
        $requestData = $this->request->get();
        //筛选条件
        $paramArr = ['name', 'status', 'start_time', 'end_time'];
        foreach ($paramArr as $v) {
            if (isset($requestData[$v]) && !empty($requestData[$v])) {
                switch ($v) {
                    case 'name' :
                        $conditions .= ' AND name like :name:';
                        $binds['name'] = $requestData->$v.'%';
                        break;
                    case 'start_time' :
                        $conditions .= ' AND start_time >= :start_time:';
                        $binds['start_time'] = $requestData->start_date;
                        break;
                    case 'end_time' :
                        $conditions .= ' AND end_time <= :end_time:';
                        $binds['end_time'] = $requestData->end_date;
                        break;
                    default :
                        $conditions .= ' AND '.$v.' = :'.$v.':';
                        $binds[$v] = $requestData->$v;
                }
            }
        }

        $maxcount = ShermanAppCards::count([
            'conditions' => $conditions,
            'bind'       => $binds,
        ]);
        if ($maxcount == 0) {
            return parent::getReturnMsg(200, '', '没有找到任何卡券信息');
        }

        $ShermanAppCards = ShermanAppCards::find([
            'conditions' => $conditions,
            'bind'       => $binds,
            'limit'      => $number,
            'offset'     => $offset,
            'order'      => 'create_at desc',
        ]);
        if (count($ShermanAppCards) == 0) {
            return parent::getReturnMsg(200, '', '没有找到任何卡券信息');
        }

        $ShermanAppCards = $ShermanAppCards->toArray();
        foreach ($ShermanAppCards as $k => $v) {
            $ShermanAppCards[$k]['is_end'] = 1;//正常

            if (strtotime($v['end_time']) < time()) {
                $ShermanAppCards[$k]['is_end'] = 2;//已过期
            }
        }

        $returnData = [
            "list"    => $ShermanAppCards,
            "page"    => $page,
            "number"  => $number,
            "maxcount"=> $maxcount,
        ];
        return parent::getReturnMsg(200, $returnData, 'ok');
    }


    /**
     *
     * @param int $id
     * @func 删除卡券
     */
    public function deleteAction()
    {
        $id = decode($this->request->get('id'));
        if (!$id) {
            return parent::getReturnMsg(400, '', '缺少关键参数');
        }

        $actAppCoupons = ShermanAppCards::findFirstById($id);
        if (!$actAppCoupons) {
            return parent::getReturnMsg(200, '', '没有对应卡券信息');
        }

        // start a transaction
        $this->db->begin();

        if (!$actAppCoupons->delete()) {
            $this->db->rollback();
            return parent::processDbError($actAppCoupons);
        }

        $sql = 'DELETE FROM sherman_card_lists WHERE card_id = :card_id';
        $result = $this->db->execute($sql, ['card_id' => $id]);
        if (!$result) {
            $this->db->rollback();
            return parent::getReturnMsg(500, '', '系统繁忙:)');
        }

        // Commit the transaction
        $this->db->commit();

        return parent::getReturnMsg(204, '', 'ok');
    }

    /**
     * 卡券订单
     * @return
     */
    public function ordersAction()
    {
        $page = $this->request->get('page', 'int', 1);//页码
        $number = $this->request->get('number', 'int', 10);//每页条数
        $offset = ($page - 1) * $number;

        if ($page < 0 || $number > 50) {
            return parent::getReturnMsg(401, '', '页码参数格式不正确');
        }

        $conditions = ' WHERE cardList.account_id = :account_id:';
        $binds = ['account_id' => parent::getAccountId()];

        $requestData = $this->request->getQuery();
        //筛选条件
        $paramArr = ['query_name', 'status','name'];
        foreach ($paramArr as $v) {
            if (isset($requestData[$v]) && !empty($requestData[$v])) {
                switch ($v) {
                    case 'status' :
                        $conditions .= ' AND cardList.status = :'.$v.':';
                        $binds[$v] = $requestData[$v];
                        break;
                    case 'name' :
                        $conditions .= ' AND card.name like :'.$v.':';
                        $binds[$v] = $requestData[$v].'%';
                        break;
                    case 'query_name' :
                        $conditions .= ' AND (nickname LIKE :nickname: OR mobile LIKE :mobile:)';
                        $binds['mobile'] = $requestData[$v].'%';
                        $binds['nickname'] = $requestData[$v].'%';
                        break;
                    default :
                        $conditions .= ' AND '.$v.' = :'.$v.':';
                        $binds[$v] = $requestData[$v];
                }
            }
        }

        // ShermanCardLists 和 ShermanAppCards, ShermanUsers 联表查询
        $field = 'nickname, mobile, sn_number, cardList.status, send_time, use_time, name';
        $table1 = 'Sherman\Shermanapps\Models\ShermanCardLists AS cardList';
        $table2 = 'Sherman\Shermanapps\Models\ShermanAppCards as card';
        $table3 = 'Sherman\User\Models\ShermanUsers as users';
        $phql = 'SELECT %s FROM %s LEFT JOIN %s ON card_id = card.id LEFT JOIN %s ON user_id = users.id';
        $phql = sprintf($phql, $field, $table1, $table2, $table3);
        $phql .= $conditions;

        $allCardList = $this->modelsManager->executeQuery($phql, $binds);
        $maxcount = count($allCardList);
        if ($maxcount == 0) {
            return parent::getReturnMsg(200, '', '没有找到任何卡券订单记录:)');
        }

        $phql .= sprintf(' ORDER BY cardList.id desc LIMIT %s OFFSET %s ', $number, $offset);
        $cardList = $this->modelsManager->executeQuery($phql, $binds);
        if (count($cardList) == 0) {
            return parent::getReturnMsg(200, '', $page > 1 ? '没有更多卡券订单记录:)' : '没有找到任何卡券订单记录:)');
        }

        $returnData = [
            "list"    => $cardList,
            "page"    => $page,
            "number"  => $number,
            "maxcount"=> $maxcount
        ];
        return parent::getReturnMsg(200, $returnData, 'ok');
    }

    /**
     *
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function exportListAction()
    {
        $accountId = parent::getAccountId();

        $conditions = ' WHERE couponList.account_id = :account_id:';
        $binds = ['account_id' => $accountId];

        // ActCouponLists 和 ShermanAppCards,ActUsers 联表查询
        $field = 'nickname, mobile, sn_number, cardList.status, send_time, use_time, name';
        $table1 = 'Sherman\Shermanapps\Models\ShermanCardLists AS cardList';
        $table2 = 'Sherman\Shermanapps\Models\ShermanAppCards as card';
        $table3 = 'Sherman\User\Models\ShermanUsers as users';
        $phql = 'SELECT %s FROM %s LEFT JOIN %s ON card_id = card.id LEFT JOIN %s ON user_id = users.id';
        $phql = sprintf($phql, $field, $table1, $table2, $table3);
        $phql .= $conditions;

        $query = $this->modelsManager->createQuery($phql);
        $query->cache([
                "key"      => '_CardListAllData_'.$accountId,
                "lifetime" => 120,
        ]);
        $cardList = $query->execute($binds);
        if (count($cardList) == 0) {
            parent::createNoFindDataAlert();
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '昵称')
            ->setCellValue('B1', '手机')
            ->setCellValue('C1', '卡券名称')
            ->setCellValue('D1', 'SN码')
            ->setCellValue('E1', '领取时间')
            ->setCellValue('F1', '核销')
            ->setCellValue('G1', '核销时间');

        $cardList = $cardList->toArray();
        foreach ($cardList as $k => $v) {
            $index = $k + 2;
            $useTime = empty($v['use_time']) ? '--' : date('Y-m-d H:i', strtotime($v['use_time']));
            $sendTime = empty($v['send_time']) ? '--' : date('Y-m-d H:i', strtotime($v['send_time']));
            $status = $v['status'] == 1 ? '未核销' : '已核销';

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $index, $v['nickname'])
                ->setCellValue('B' . $index, $v['mobile'])
                ->setCellValue('C' . $index, $v['name'])
                ->setCellValue('D' . $index, ' '.$v['sn_number'])
                ->setCellValue('E' . $index, $sendTime)
                ->setCellValue('F' . $index, $status)
                ->setCellValue('G' . $index, $useTime);
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        parent::createExcel($objPHPExcel, '卡券数据导出'.date('Y-m-d'));
    }

    /**
     * 卡券明细统计
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function statisticsAction()
    {
        $accountId = parent::getAccountId();

        $conditions = ' WHERE cardList.account_id = :account_id: AND card.status = 1';
        $binds = ['account_id' => $accountId];

        $field = 'cardList.status';
        $table1 = 'Sherman\Shermanapps\Models\ShermanCardLists AS cardList';
        $table2 = 'Sherman\Shermanapps\Models\ShermanAppCards as card';
        $phql = 'SELECT %s FROM %s LEFT JOIN %s ON card_id = card.id';
        $phql = sprintf($phql, $field, $table1, $table2);
        $phql .= $conditions;

        $allCardList = $this->modelsManager->executeQuery($phql, $binds);
        if (count($allCardList) == 0) {
            return parent::getReturnMsg(200, '', '没有找到任何数据:)');
        }

        //未核销张数
        $exchangeOneNum = 0;
        //已核销张数
        $exchangeTwoNum = 0;
        //已领卡券数
        $totalNum = 0;

        $allCardList = $allCardList->toArray();
        foreach ($allCardList as $k => $v) {
            switch ($v['status']) {
                case 1 :
                    $exchangeOneNum += 1;
                    break;
                case 2 :
                    $exchangeTwoNum += 1;
                    break;
            }

            $totalNum += 1;
        }

        //总人数
        $totalUser = ShermanCardLists::count([
            "distinct" => 'user_id',
            'account_id = ?1',
            "bind" => [1 => $accountId]
        ]);

        $returnData = [
            "total_member"    => $totalUser,
            "total_num"       => $totalNum,
            "exchange_1_num"  => $exchangeOneNum,
            "exchange_2_num"  => $exchangeTwoNum,
        ];
        return parent::getReturnMsg(200, $returnData, '');
    }

}
