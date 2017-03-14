<?php

namespace Sherman\Common\Controllers;

class IndexController extends ControllerBase
{
    public function notFoundAction()
    {
        $this->view->disable();
        return parent::getReturnMsg(404, '', 'This is crazy, no api support!');
    }

    /**
     * 默认首页显示页面
     */
    public function indexAction()
    {
        echo 'welcome to myPhalcon api';
    }

    /**
     * 获取七牛上传token
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function getUploadTokenAction()
    {
        $qiniuUploadToken = getQiniuToken();
        if (!$qiniuUploadToken) {
            $this->response->setJsonContent(['uptoken' => '']);
        } else {
            $this->response->setJsonContent(['uptoken' => $qiniuUploadToken]);
        }
        return $this->response;
    }

    /**
     * 获取微信全局access_token
     */
    public function getWechatTokenAction()
    {
        $app = $this->di->get('easywechat');
        $accessToken = $app->access_token;
        $token = $accessToken->getToken();
        echo $token;
        exit;
    }

    /**
     * 未登录跳转url
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function noLoginAction()
    {
        return parent::getReturnMsg(409, '', '请登录');
    }

}
