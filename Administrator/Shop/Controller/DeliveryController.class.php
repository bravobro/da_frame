<?php
/**
 * 物流自提服务站管理
 *
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;


class DeliveryController extends SystemController {
    public function __construct() {
        parent::__construct();
    }

    /**
     * 物流自提服务站列表
     */
    public function index()
    {
        if ($_GET['sign'] == 'verify') {
            $this->assign('sign', 'verify');
        }
				
		$this->setDirquna('shop');
        $this->render('delivery.index');
    }

    /**
     * 物流自提服务站列表XML
     */
    public function index_xml()
    {
        $condition = array();
        if (strlen($q = trim($_REQUEST['query']))) {
            switch ($_REQUEST['qtype']) {
                case 'dlyp_name':
                case 'dlyp_truename':
                case 'dlyp_address_name':
                case 'dlyp_area_info':
                case 'dlyp_address':
                    $condition[$_REQUEST['qtype']] = array('like', '%' . $q . '%');
                    break;
            }
        }

        switch ($_REQUEST['sortname']) {
            case 'dlyp_addtime':
                $sort = $_REQUEST['sortname'];
                break;
            default:
                $sort = 'dlyp_id';
                break;
        }
        if ($_REQUEST['sortorder'] != 'asc') {
            $sort .= ' desc';
        }

        $model_dp = Model('delivery_point');
        if ($_GET['sign'] == 'verify') {
            $list = (array) $model_dp->getDeliveryPointWaitVerifyList($condition, $_REQUEST['rp'], $sort);
        } else {
            $list = (array) $model_dp->getDeliveryPointList($condition, $_REQUEST['rp'], $sort);
        }

        $states = $model_dp->getDeliveryState();

        $data = array();
        $data['now_page'] = $model_dp->shownowpage();
        $data['total_num'] = $model_dp->gettotalnum();

        foreach ($list as $val) {
            $i = array();

            $i['operation'] = <<<EOB
<a class="btn green" href="{$GLOBALS['_PAGE_URL']}&c=Delivery&a=order_list&dlyp_id={$val['dlyp_id']}"><i class="fa fa-list-alt"></i>查看订单</a>
<a class="btn blue" href="{$GLOBALS['_PAGE_URL']}&c=Delivery&a=edit_delivery&d_id={$val['dlyp_id']}"><i class="fa fa-pencil-square-o"></i>编辑</a>
EOB;

            $i['dlyp_name'] = $val['dlyp_name'];
            $i['dlyp_truename'] = $val['dlyp_truename'];
            $i['dlyp_address_name'] = $val['dlyp_address_name'];
            $i['dlyp_area_info'] = $val['dlyp_area_info'];
            $i['dlyp_address'] = $val['dlyp_address'];

            $i['dlyp_state'] = $states[$val['dlyp_state']];

            $i['dlyp_addtime'] = date('Y-m-d H:i:s', $val['dlyp_addtime']);

            $data['list'][$val['dlyp_id']] = $i;
        }

        echo $this->flexigridXML($data);
        exit;
    }

    /**
     * 物流自提服务站设置
     */
    public function setting() {
        $list_setting = Model('setting')->getListSetting();
        $this->assign('list_setting',$list_setting);
						
		$this->setDirquna('shop');
        $this->render('delivery.setting');
    }
    /**
     * 提说站设置保存
     */
    public function save_setting() {
        if (!chksubmit()){
            showMessage(L('spd_common_save_fail'));
        }
        $update_array = array();
        $update_array['delivery_isuse'] = intval($_POST['delivery_isuse']);
        $result = Model('setting')->updateSetting($update_array);
        if ($result === true){
            $log = '开启';
            if ($update_array['delivery_isuse'] == 0) {
                $log = '关闭';
                // 删除相关联的收货地址
                Model('address')->delAddress(array('dlyp_id' => array('neq', 0)));
            }
            $this->log($log.'物流自提服务站功能', 1);
            showMessage(L('spd_common_save_succ'));
        }else {
            $this->log($log.'物流自提服务站功能', 0);
            showMessage(L('spd_common_save_fail'));
        }
    }
    /**
     * 编辑物流自提服务站信息
     */
    public function edit_delivery() {
        $dlyp_id = intval($_GET['d_id']);
        if ($dlyp_id <= 0) {
            showMessage(L('param_error'));
        }
        $dlyp_info = Model('delivery_point')->getDeliveryPointInfo(array('dlyp_id' => $dlyp_id));
        if (empty($dlyp_info)) {
            showMessage(L('param_error'));
        }
        $this->assign('dlyp_info', $dlyp_info);
						
		$this->setDirquna('shop');
        $this->render('delivery.edit');
    }
    /**
     * 编辑保存
     */
    public function save_edit() {
        $dlyp_id = intval($_POST['did']);
        if (!chksubmit() || $dlyp_id <= 0) {
            showMessage(L('param_error'));
        }
        $where = array('dlyp_id' => $dlyp_id);
        $update = array();
        $update['dlyp_mobile']          = $_POST['dmobile'];
        $update['dlyp_telephony']       = $_POST['dtelephony'];
        $update['dlyp_address_name']    = $_POST['daddressname'];
        $update['dlyp_address']         = $_POST['daddress'];
        if ($_POST['dpasswd'] != '') {
            $update['dlyp_passwd']      = md5($_POST['dpasswd']);
        }
        $update['dlyp_area_1']          = intval($_POST['area_id_1']);
        $update['dlyp_area_2']          = intval($_POST['area_id_2']);
        $update['dlyp_area_3']          = intval($_POST['area_id_3']);
        $update['dlyp_area_4']          = intval($_POST['area_id_4']);
        $update['dlyp_area']            = intval($_POST['area_id']);
        $update['dlyp_area_info']       = $_POST['region'];
        $update['dlyp_state']           = intval($_POST['dstate']);
        $update['dlyp_fail_reason']     = $_POST['fail_reason'];
        $result = Model('delivery_point')->editDeliveryPoint($update, $where);
        if ($result) {
            // 删除相关联的收货地址
            Model('address')->delAddress(array('dlyp_id' => $dlyp_id));
            $this->log('编辑物流自提服务站功能，ID：'.$dlyp_id, 1);
            showMessage(L('spd_common_op_succ'), urlAdminShop('delivery', 'index'));
        } else {
            $this->log('编辑物流自提服务站功能，ID：'.$dlyp_id, 0);
            showMessage(L('spd_common_op_fail'));
        }
    }

    /**
     * 订单列表
     */
    public function order_list()
    {
        $dlyp_id = intval($_GET['dlyp_id']);
        if ($dlyp_id <= 0) {
            showMessage(L('param_error'));
        }

        $dlyp_info = Model('delivery_point')->getDeliveryPointInfo(array('dlyp_id' => $dlyp_id));
        if (empty($dlyp_info)) {
            showMessage(L('param_error'));
        }
        $this->assign('dlyp_info', $dlyp_info);

        $this->assign('dlyp_id', $dlyp_id);
						
		$this->setDirquna('shop');
        $this->render('delivery.order_list');
    }

    /**
     * 订单列表
     */
    public function order_list_xml()
    {
        $condition = array();
        if (strlen($q = trim($_REQUEST['query']))) {
            switch ($_REQUEST['qtype']) {
                case 'order_sn':
                case 'shipping_code':
                case 'reciver_name':
                case 'reciver_mobphone':
                case 'reciver_telphone':
                    $condition[$_REQUEST['qtype']] = array('like', '%' . $q . '%');
                    break;
            }
        }
        $condition['dlyp_id'] = (int) $_GET['dlyp_id'];

        $model_do = Model('delivery_order');
        $list = (array) $model_do->getDeliveryOrderList($condition, '*', $_REQUEST['rp']);
        $states = $model_do->getDeliveryOrderState();

        $data = array();
        $data['now_page'] = $model_do->shownowpage();
        $data['total_num'] = $model_do->gettotalnum();

        foreach ($list as $val) {
            $i = array();
            $i['operation'] = '<span>--</span>';

            $i['order_sn'] = $val['order_sn'];
            $i['shipping_code'] = $val['shipping_code'];
            $i['reciver_name'] = $val['reciver_name'];
            $i['reciver_mobphone'] = $val['reciver_mobphone'];
            $i['reciver_telphone'] = $val['reciver_telphone'];

            $i['dlyo_state'] = $states[$val['dlyo_state']];

            $data['list'][$val['order_id']] = $i;
        }

        echo $this->flexigridXML($data);
        exit;
    }
}
