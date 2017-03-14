<?php

namespace Sherman\Account\Controllers;

use Sherman\Account\Models\ShermanAccounts;

class IndexController extends ControllerBase
{
    public function notFoundAction()
    {
        return parent::getReturnMsg(404, '', 'This is crazy, no api support!');
    }


    public function indexAction($id)
    {
        $id = decode($id);
        $accounts = ShermanAccounts::find([
            'conditions' => 'status = 1 AND account_id = :account_id:',
            'bind' => ['account_id' => $id],
        ]);

        if ($accounts) {
            return parent::getReturnMsg(200, $accounts, 'ok');
        } else {
            return parent::getReturnMsg(200, $accounts, 'not found data');
        }
    }


    /**
     * Create a account
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            return parent::getReturnMsg(400, '', '参数异常');
        }

        $requestData = $this->request->getJsonRawBody();
        $accountName = $requestData->name;
        $accountPwd = $requestData->password;

        $accounts = new ShermanAccounts([
            'name'     => $accountName,
            'password' => $accountPwd,
        ]);
        if (!$accounts->create()) {
            return parent::processDbError($accounts);
        }

        return parent::getReturnMsg(200, '', 'ok');
    }


    /**
     * Update password
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function updatePwdAction()
    {
        if (!$this->request->isPost()) {
            return parent::getReturnMsg(200, '', '缺少关键参数:)');
        }

        $requestData = $this->request->getJsonRawBody();
        $oldPassword = $requestData->oldPassword;
        $pwd = $requestData->password;

        if (empty($oldPassword)) {
            return parent::getReturnMsg(401, '', '请输入旧密码:)');
        }
        if (empty($pwd)) {
            return parent::getReturnMsg(401, '', '请输入新密码:)');
        }

        $id = parent::getAccountId();
        $accounts = ShermanAccounts::findFirstById($id);
        if (!$accounts) {
            return parent::getReturnMsg(200, '', '没有对应账号信息:)');
        }

        if (!$this->security->checkHash($oldPassword, $accounts->password)) {
            return parent::getReturnMsg(401, '', '旧密码错误，请重新输入:)');
        }

        $accounts->password = $this->security->hash($pwd);
        if (!$accounts->save()) {
            return parent::processDbError($accounts);
        }

        $ret = [
            'id'   => encode($accounts->id),
            'name' => $accounts->name,
        ];
        return parent::getReturnMsg(201, $ret, 'ok');
    }

    /**
     * Deletes a account(Soft delete)
     * @param int $id
     */
    public function deleteAction($id)
    {
        $accounts = ShermanAccounts::findFirstById(decode($id));
        if (!$accounts) {
            return parent::getReturnMsg(404, '', 'not found data');
        }

        if (!$accounts->delete()) {
            return parent::processDbError($accounts);
        }

        return parent::getReturnMsg(200, '', '');
    }

    /**
     * Login action
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function loginAction()
    {
        if (!$this->request->isPost()) {
            return parent::getReturnMsg(401, '', '请输入账号和密码');
        }
        $requestData = $this->request->getJsonRawBody();
        $accounts = ShermanAccounts::findFirstByName($requestData->name);

        if (!$accounts) {
            return parent::getReturnMsg(401, '', 'not found data');
        }

        if ($accounts->status != 1) {
            return parent::getReturnMsg(403, '', '该账号已被禁用');
        }

        $isLogin = parent::loginCheck();
        if (!$isLogin) {
            if (!$this->security->checkHash($requestData->password, $accounts->password)) {
                return parent::getReturnMsg(401, '', '用户名或密码错误，请重新输入');
            }

            $this->session->set('id', $accounts->id);
            $this->session->set('name', $requestData->name);
        }

        $ret = [
            'id'   => encode($accounts->id),
            'name' => $accounts->name,
        ];
        return parent::getReturnMsg(200, $ret, 'ok');
    }

    /**
     * Check login action
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function checkAction()
    {
        $isLogin = parent::loginCheck();
        if ($isLogin) {
            $ret = [
                'id'   => encode($this->session->get('id')),
                'name' => $this->session->get('name'),
            ];
            return parent::getReturnMsg(200, $ret, 'login');
        } else {
            return parent::getReturnMsg(409, '', 'not login');
        }
    }

    /*
     * Logout action
     */
    public function logoutAction()
    {
        session_destroy();
        return parent::getReturnMsg(200, '', 'ok');
    }

}