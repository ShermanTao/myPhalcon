<?php

namespace Sherman\User\Controllers;

use PHPExcel;
use Sherman\User\Models\ShermanUsers;

class IndexController extends ControllerBase
{
    public function notFoundAction()
    {
        $this->view->disable();
        return parent::getReturnMsg(404, '', 'This is crazy, no api support!');
    }

    /**
     * User list
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function listAction()
    {
        $page = $this->request->get('page', 'int', 1);//页码
        $number = $this->request->get('number', 'int', 10);//每页条数
        $offset = ($page - 1) * $number;
        if ($page < 0 || $number > 50) {
            return parent::getReturnMsg(401, '', '页码参数格式不正确');
        }

        $conditions = 'status = :status:';
        $binds = ['status' => 1];

        $requestData = $this->request->get();
        //筛选条件
        $paramArr = ['query_name', 'nickname', 'mobile'];
        foreach ($paramArr as $v) {
            if (isset($requestData[$v]) && $requestData[$v]!=NULL ) {
                switch ($v) {
                    case 'nickname' :
                        $conditions .= ' AND nickname like :nickname:';
                        $binds['nickname'] = $requestData[$v].'%';
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

        $maxcount = ShermanUsers::count([
            'conditions' => $conditions,
            'bind'       => $binds,
        ]);
        if ($maxcount == 0) {
            return parent::getReturnMsg(200, '', '没有找到任何数据:)');
        }

        $field = 'id, nickname, mobile, status, create_at';
        $users = ShermanUsers::find([
            'columns'    => $field,
            'conditions' => $conditions,
            'bind'       => $binds,
            'limit'      => $number,
            'offset'     => $offset,
            'order'      => 'id desc',
        ]);
        if (count($users) == 0) {
            return parent::getReturnMsg(200, '', $page > 1 ? '没有更多数据:)' : '没有找到任何数据:)');
        }

        $users = $users->toArray();
        foreach ($users as $k => $v) {
            $users[$k]['id'] = encode($v['id']);
        }

        $returnData = [
            "list"    => $users,
            "page"    => intval($page),
            "number"  => intval($number),
            "maxcount"=> $maxcount,
        ];
        return parent::getReturnMsg(200, $returnData, '');
    }

    /**
     * Export user data
     */
    public function exportListAction()
    {
        $conditions = ' WHERE status = :status:';
        $binds = ['status' => 1];

        $field = 'id, nickname, mobile, status, create_at';
        $table = 'Sherman\User\Models\ShermanUsers';
        $phql = 'SELECT %s FROM %s';
        $phql = sprintf($phql, $field, $table);
        $phql .= $conditions;

        $query = $this->modelsManager->createQuery($phql);
        $query->cache([
            "key"      => '_UserData',
            "lifetime" => 120,
        ]);
        $allUsers = $query->execute($binds);
        if (count($allUsers) == 0) {
            parent::createNoFindDataAlert();
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '昵称')
            ->setCellValue('B1', '手机')
            ->setCellValue('C1', '参与时间');

        $allUsers = $allUsers->toArray();
        foreach ($allUsers as $k => $v) {
            $index = $k + 2;

            $createAt = empty($v['create_at']) ? '--' : date('Y-m-d H:i', strtotime($v['create_at']));
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $index, $v['nickname'])
                ->setCellValue('B' . $index, $v['mobile'])
                ->setCellValue('C' . $index, $createAt);
        }
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);

        parent::createExcel($objPHPExcel, '用户数据导出'.date('Y-m-d'));
    }

    /**
     * Update user status
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function statusAction()
    {
        if (!$this->request->isPost()) {
            return parent::getReturnMsg(400, '', '参数异常');
        }

        $requestData = $this->request->getJsonRawBody();
        $mid = decode($requestData->id);
        $actUsers = ActUsers::findFirstById($mid);
        if (!$actUsers) {
            return parent::getReturnMsg(200, '', '没有对应用户信息:)');
        }

        $actUsers->status = $requestData->status;
        if (!$actUsers->save()) {
            return parent::processDbError($actUsers);
        }

        return parent::getReturnMsg(201, '', '状态更新成功！');
    }

}
