<?php

namespace Sherman\Common\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Http\Request;
use Phalcon\Http\Response;

class ControllerBase extends Controller
{
    /**
     * 免登录方法
     * 支持三种方式
     * $module 整个模块免登录
     * $module.$controller 整个控制器免登录
     * $module.$controller.$action 方法免登录
     */
    public $noLoginRoute = [
        'AccountIndexcreate',
        'AccountIndexlogin',
        'AccountIndexcheck',
        'AccountIndexdelete',
        'CommonIndex',
        'CommonTest',
        //'Shermanapps',
    ];

    public function initialize()
    {
        //设置允许跨域请求
        $domain = $this->getDomain();
        $this->response->setHeader('Access-Control-Allow-Origin', $domain);
        $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
    }

    /**
     * 添加登录验证
     * @param $dispatcher
     * @return bool
     */
    public function beforeExecuteRoute($dispatcher)
    {
        $module = $dispatcher->getModuleName();
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        $flag = in_array($module, $this->noLoginRoute) || in_array($module.$controller, $this->noLoginRoute) || in_array($module.$controller.$action, $this->noLoginRoute);

        if (!$flag && !$this->loginCheck()) {
            $domain = $this->getDomain();
            $this->response->setHeader('Access-Control-Allow-Origin', $domain);
            $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
            $this->response->redirect("Common/Index/noLogin");
            return false;
        }
    }

    /**
     * @param int $code  返回状态码,200表示成功
     * @param array $data  领域业务数据，由接口自定义
     * @param string $msg  msg为错误的提示信息
     * @return Response|\Phalcon\Http\ResponseInterface
     */
    public function getReturnMsg($code, $data, $msg = '')
    {
        $this->response->setJsonContent(['ret' => $code, 'data' => $data, 'msg' => $msg]);
        return $this->response;
    }

    /**
     * 登录验证
     * todo 后续加入更多的判断条件
     * @return bool
     */
    protected function loginCheck() {
        $sid = $this->session->getId();
        error_log(json_encode($this->session->getId())."\r\n".json_encode($this->session->get('name'),JSON_UNESCAPED_UNICODE)."\r\n".'===>日志日期:'.date('Y-m-d H:i:s')."\r\n",3,'/var/log/saas/check.log');
        if (empty($sid)) {
            return false;
        } else {
            $username = $this->session->get('name');
            if (empty($username)) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * 从session中获取account_id
     * @return int|mixed
     */
    protected function getAccountId()
    {
        $accountId = $this->session->get('id');
        if (!empty($accountId) && intval($accountId) > 0) {
            return intval($accountId);
        }

        $domain = $this->getDomain();
        $this->response->setHeader('Access-Control-Allow-Origin', $domain);
        $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
        $this->response->redirect("Common/Index/noLogin");
        exit;
    }

    /**
     * 从session中获取account_id对应的权限 ID
     * @return int|mixed
     */
    protected function getAccountRole()
    {
        $accountRoleId = $this->session->get('roleid');
        if (!empty($accountRoleId)) {
            return $accountRoleId;
        }

        $domain = $this->getDomain();
        $this->response->setHeader('Access-Control-Allow-Origin', $domain);
        $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
        $this->response->redirect("Common/Index/noLogin");
        exit;
    }

    /**
     * 从session中获取account_name
     * @return string|mixed
     */
    protected function getAccountName()
    {
        $accountName = $this->session->get('name');
        if (!empty($accountName)) {
            return $accountName;
        }

        $domain = $this->getDomain();
        $this->response->setHeader('Access-Control-Allow-Origin', $domain);
        $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
        $this->response->redirect("Common/Index/noLogin");
        exit;
    }

    protected function getDomain()
    {
        $refer = $this->request->getServer('HTTP_REFERER');
        $ary = explode('/', $refer);
        return $ary[0].'//'.$ary[2];
    }

    /**
     * 处理数据库操作错误
     * @param $obj
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    protected function processDbError($obj)
    {
        if (!empty($obj->getMessages())) {
            $msg = '';
            foreach ($obj->getMessages() as $message) {
                $msg = $message->getMessage();
                break;
            }

            return $this->getReturnMsg(401, '', $msg);
        } else {
            return $this->getReturnMsg(500, '', '系统繁忙');
        }
    }

    protected function createExcel($objPHPExcel, $title)
    {
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle($title);
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        //设置单元格的对齐方式
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//水平居右

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$title.'.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: 0');
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    protected function createNoFindDataAlert()
    {
        echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
					<script type='text/javascript'>
						alert('没有符合条件的记录:)');self.opener=null;self.close();
					</script>";
        exit();
    }
}
