<?php
/**
 * 运单打印
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;

class StoreWaybillController extends BaseSellerController{
    public function __construct() {
        parent::__construct() ;
    }

    /**
     * 模板管理
     */
    public function waybill_manage() {
        $model_store_extend = Model('store_extend');
        $model_express = Model('express');
        $model_store_waybill = Model('store_waybill');

        $store_extend_info = $model_store_extend->getStoreExtendInfo(array('store_id' => $_SESSION['store_id']), 'express');
        $store_express = $store_extend_info['express'];

        $express_list = $model_express->getExpressListByID($store_express);

        $store_waybill_list = $model_store_waybill->getStoreWaybillListWithWaybillInfo($_SESSION['store_id'], $store_express);
        $store_waybill_list = array_under_reset($store_waybill_list, 'express_id');

        if(!empty($express_list)) {
            foreach ($express_list as $key => $value) {
                if(!empty($store_waybill_list[$value['id']])) {
                    $express_list[$key]['waybill_name'] = $store_waybill_list[$value['id']]['waybill_name'];
                    $express_list[$key]['store_waybill_id'] = $store_waybill_list[$value['id']]['store_waybill_id'];
                    $express_list[$key]['is_default_text'] =  $store_waybill_list[$value['id']]['is_default'] ? '是' : '否';
                    $express_list[$key]['waybill_image_url'] = getWaybillImageUrl($store_waybill_list[$value['id']]['waybill_image']);
                    $express_list[$key]['waybill_width'] = $store_waybill_list[$value['id']]['waybill_width'];
                    $express_list[$key]['waybill_height'] = $store_waybill_list[$value['id']]['waybill_height'];
                    $express_list[$key]['bind'] = true;
                } else {
                    $express_list[$key]['waybill_name'] = '未绑定';
                    $express_list[$key]['bind'] = false;
                }
            }
        }

        $this->assign('express_list', $express_list);

        $this->profile_menu('waybill_manage');
        $this->render('store_waybill.manage');
    }

    /**
     * 绑定运单打印模板
     */
    public function waybill_bind() {
        $express_id = intval($_GET['express_id']);

        $model_express = Model('express');
        $model_waybill = Model('waybill');

        $express_info = $model_express->getExpressInfo($express_id);
        if(empty($express_info)) {
            showDialog('快递公司不存在');
        }
        $this->assign('express_info', $express_info);

        $waybill_list = $model_waybill->getWaybillUsableList($express_id, $_SESSION['store_id']);
        $this->assign('waybill_list', $waybill_list);


        $this->profile_menu('waybill_bind');
        $this->render('store_waybill.bind');
    }

    /**
     * 绑定运单打印模板保存
     */
    public function waybill_bind_save() {
        $express_id = intval($_POST['express_id']);

        $model_waybill = Model('waybill');
        $model_store_waybill = Model('store_waybill');

        $waybill_info = $model_waybill->getWaybillInfoByID($_POST['waybill_id']);
        if(!$waybill_info) {
            showMessage('运单模板不存在');
        }

        $param = array();
        $param['store_id'] = $_SESSION['store_id'];
        $param['express_id'] = $express_id;

        //删除已有绑定
        $model_store_waybill->delStoreWaybill($param);

        //保存绑定
        $param['waybill_id'] = $waybill_info['waybill_id'];
        $param['waybill_name'] = $waybill_info['waybill_name'];
        $param['store_waybill_left'] = $waybill_info['waybill_left'];
        $param['store_waybill_top'] = $waybill_info['waybill_top'];
        $result = $model_store_waybill->addStoreWaybill($param);
        if($result) {
            showMessage('绑定成功', urlShop('store_waybill', 'waybill_manage'));
        } else {
            showMessage('绑定失败', '', '', 'error');
        }
    }

    /**
     * 解绑运单打印模板
     */
    public function waybill_unbind() {
        $store_waybill_id = intval($_POST['store_waybill_id']);

        $model_store_waybill = Model('store_waybill');

        $condition = array();
        $condition['store_waybill_id'] = $store_waybill_id;
        $condition['store_id'] = $_SESSION['store_id'];

        $result = $model_store_waybill->delStoreWaybill($condition);
        if($result) {
            showMessage('解绑成功', '');
        } else {
            showMessage('解绑失败', '', '', 'error');
        }
    }

    /**
     * 运单模板设置
     */
    public function waybill_setting() {
        $store_waybill_id = intval($_GET['store_waybill_id']);

        $model_store_waybill = Model('store_waybill');

        $store_waybill_info = $model_store_waybill->getStoreWaybillInfo(array('store_waybill_id' => $store_waybill_id));
        $this->assign('store_waybill_id', $store_waybill_info['store_waybill_id']);
        $this->assign('store_waybill_left', $store_waybill_info['store_waybill_left']);
        $this->assign('store_waybill_top', $store_waybill_info['store_waybill_top']);
        $this->assign('store_waybill_data', $store_waybill_info['store_waybill_data']);

        $this->profile_menu('waybill_setting');
        $this->render('store_waybill.setting');
    }

    /**
     * 运单模板设置保存
     */
    public function waybill_setting_save() {
        $store_waybill_id = intval($_POST['store_waybill_id']);
        if($store_waybill_id <= 0) {
            showMessage(L('param_error'), '', '', 'error');
        }

        $model_store_waybill = Model('store_waybill');

        $condition = array();
        $condition['store_waybill_id'] = $store_waybill_id;
        $condition['store_id'] = $_SESSION['store_id'];

        $update = array();
        $update['store_waybill_left'] = $_POST['store_waybill_left'];
        $update['store_waybill_top'] = $_POST['store_waybill_top'];

        $result = $model_store_waybill->editStoreWaybill($update, $condition, $_POST['data']);
        if($result) {
            showDialog(L('spd_common_save_succ'), urlShop('store_waybill', 'waybill_manage'), 'succ');
        } else {
            showDialog(L('spd_common_save_fail'), '', 'error');
        }
    }

    /**
     * 运单打印测试
     */
    public function waybill_test() {
        $model_waybill = Model('waybill');

        $waybill_info = $model_waybill->getWaybillInfoByID($_GET['waybill_id']);
        if(!$waybill_info) {
            showMessage('运单模板不存在');
        }

        $this->assign('waybill_info', $waybill_info);
        $this->render('store_waybill.test', 'null_layout');
    }

    /**
     * 设置默认打印模板
     */
    public function waybill_set_default() {
        $store_waybill_id = intval($_POST['store_waybill_id']);

        $model_store_waybill = Model('store_waybill');

        $result = $model_store_waybill->editStoreWaybillDefault($store_waybill_id, $_SESSION['store_id']);

        if($result) {
            showMessage(L('spd_common_save_succ'), '');
        } else {
            showMessage(L('spd_common_save_fail'), '', '', 'error');
        }
    }

    /**
     * 模板列表
     */
    public function waybill_list() {
        $model_waybill = Model('waybill');

        $waybill_list = $model_waybill->getWaybillSellerList($_SESSION['store_id']);
        $this->assign('waybill_list', $waybill_list);

        $this->profile_menu('waybill_list');
        $this->render('store_waybill.list');
    }

    /**
     * 添加运单模板
     */
    public function waybill_add() {
        $model_express = Model('express');

        $this->assign('express_list', $model_express->getExpressList());
        $this->profile_menu('waybill_add');
        $this->render('store_waybill.add');
    }

    /**
     * 保存运单模板
     */
    public function waybill_save() {
        $model_waybill = Model('waybill');
        $result = $model_waybill->saveWaybill($_POST, $_SESSION['store_id']);

        if($result) {
            showDialog(L('spd_common_save_succ'), urlShop('store_waybill', 'waybill_list'), 'succ');
        } else {
            showDialog(L('spd_common_save_fail'), urlShop('store_waybill', 'waybill_list'));
        }
    }

    /**
     * 删除运单模板
     */
    public function waybill_del() {
        $waybill_id = intval($_POST['waybill_id']);
        if($waybill_id <= 0) {
            showMessage(L('param_error'));
        }

        $model_waybill = Model('waybill');

        $condition = array();
        $condition['waybill_id'] = $_POST['waybill_id'];
        $condition['store_id'] = $_SESSION['store_id'];
        $result = $model_waybill->delWaybill($condition);
        if($result) {
            showMessage(L('spd_common_del_succ'));
        } else {
            showMessage(L('spd_common_del_fail'));
        }
    }

    /**
     * 编辑运单模板
     */
    public function waybill_edit() {
        $model_express = Model('express');
        $model_waybill = Model('waybill');

        $waybill_info = $model_waybill->getWaybillInfoByID($_GET['waybill_id']);
        if(!$waybill_info || $waybill_info['store_id'] != $_SESSION['store_id']) {
            showMessage('运单模板不存在');
        }
        $this->assign('waybill_info', $waybill_info);

        $express_list = $model_express->getExpressList();
        foreach ($express_list as $key => $value) {
            if($value['id'] == $waybill_info['express_id']) {
                $express_list[$key]['selected'] = true;
            }
        }
        $this->assign('express_list', $express_list);

        $this->profile_menu('waybill_edit');
        $this->render('store_waybill.add');
    }

    /**
     * 设计运单模板
     */
    public function waybill_design() {
        $model_waybill = Model('waybill');

        $result = $model_waybill->getWaybillDesignInfo($_GET['waybill_id']);
        if(isset($result['error'])) {
            showMessage($result['error'], '', '', 'error');
        }

        $this->assign('waybill_info', $result['waybill_info']);
        $this->assign('waybill_info_data', $result['waybill_info_data']);
        $this->assign('waybill_item_list', $result['waybill_item_list']);

        $this->profile_menu('waybill_design');
        $this->render('store_waybill.design');
    }

    /**
     * 设计运单模板保存
     */
    public function waybill_design_save() {
        $model_waybill = Model('waybill');

        $result = $model_waybill->editWaybillDataByID($_POST['waybill_data'], $_POST['waybill_id'], $_SESSION['store_id']);

        if($result) {
            showDialog(L('spd_common_save_succ'), urlShop('store_waybill', 'waybill_list'), 'succ');
        } else {
            showDialog(L('spd_common_save_fail'), urlShop('store_waybill', 'waybill_list'));
        }
    }

    /**
     * 页面菜单
     *
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key = '') {
        $menu_array = array();
        $menu_array[] = array(
            'menu_key' => 'waybill_manage',
            'menu_name' => '模板绑定',
            'menu_url' => urlShop('store_waybill', 'waybill_manage')
        );
        $menu_array[] = array(
            'menu_key' => 'waybill_list',
            'menu_name' => '自建模板',
            'menu_url' => urlShop('store_waybill', 'waybill_list')
        );
        if($menu_key == 'waybill_bind') {
            $menu_array[] = array(
                'menu_key' => 'waybill_bind',
                'menu_name' => '选择模板',
                'menu_url' => urlShop('store_waybill', 'waybill_bind')
            );
        }
        if($menu_key == 'waybill_setting') {
            $menu_array[] = array(
                'menu_key' => 'waybill_setting',
                'menu_name' => '模板设置',
                'menu_url' => urlShop('store_waybill', 'waybill_setting')
            );
        }
        if($menu_key == 'waybill_add') {
            $menu_array[] = array(
                'menu_key' => 'waybill_add',
                'menu_name' => '添加模板',
                'menu_url' => urlShop('store_waybill', 'waybill_add')
            );
        }
        if($menu_key == 'waybill_edit') {
            $menu_array[] = array(
                'menu_key' => 'waybill_edit',
                'menu_name' => '编辑模板',
                'menu_url' => urlShop('store_waybill', 'waybill_edit')
            );
        }
        if($menu_key == 'waybill_design') {
            $menu_array[] = array(
                'menu_key' => 'waybill_design',
                'menu_name' => '设计模板',
                'menu_url' => urlShop('store_waybill', 'waybill_design')
            );
        }
        $this->assign('member_menu', $menu_array);
        $this->assign('menu_key', $menu_key);
    }

}