<?php
/**
 * 商户中心-满就送
 *
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;

class StorePromotionMansongController extends BaseSellerController {

    public function __construct() {

        parent::__construct() ;

        Language::read('member_layout,promotion_mansong');

        //检查满就送是否开启
        if (intval(C('promotion_allow')) !== 1) {
            showMessage("商品促销功能尚未开启", urlShop('seller_center', 'index'),'','error');
        }

    }

    public function index() {
        $this->mansong_list();
    }

    /**
     * 发布的满就送活动列表
     **/
    public function mansong_list() {

        $model_mansong_quota = Model('p_mansong_quota');
        $model_mansong = Model('p_mansong');

        if (checkPlatformStore()) {
            $this->assign('isOwnShop', true);
        } else {
            $current_mansong_quota = $model_mansong_quota->getMansongQuotaCurrent($_SESSION['store_id']);
            $this->assign('current_mansong_quota', $current_mansong_quota);
        }

        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        if(!empty($_GET['mansong_name'])) {
            $condition['mansong_name'] = array('like', '%'.$_GET['mansong_name'].'%');
        }
        if(!empty($_GET['state'])) {
            $condition['state'] = intval($_GET['state']);
        }
        $mansong_list = $model_mansong->getMansongList($condition, 10, 'state desc, end_time desc');
        $this->assign('list', $mansong_list);
        $this->assign('show_page',$model_mansong->showpage());
        $this->assign('mansong_state_array', $model_mansong->getMansongStateArray());

        self::profile_menu('mansong_list');
        $this->render('store_promotion_mansong.list');
    }

    /**
     * 添加满就送活动
     **/
    public function mansong_add() {
        $model_mansong_quota = Model('p_mansong_quota');
        $model_mansong = Model('p_mansong');

        $start_time = $model_mansong->getMansongNewStartTime($_SESSION['store_id']);

        if (checkPlatformStore()) {
            $this->assign('isOwnShop', true);
        } else {
            //检查当前套餐是否可用
            $current_mansong_quota = $model_mansong_quota->getMansongQuotaCurrent($_SESSION['store_id']);
            if(empty($current_mansong_quota)) {
                showMessage(Language::get('mansong_quota_current_error'),'','','error');
            }

            if(empty($start_time)) {
                $start_time = $current_mansong_quota['start_time'];
            }
            $end_time = $current_mansong_quota['end_time'];
        }

        if (empty($start_time))
            $start_time = time();

        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);

        //输出导航
        self::profile_menu('mansong_add');
        $this->render('store_promotion_mansong.add');
    }

    /**
     * 保存添加的满就送活动
     **/
    public function mansong_save() {
        $mansong_name = trim($_POST['mansong_name']);
        $start_time = strtotime($_POST['start_time']);
        $end_time = strtotime($_POST['end_time']);

        $model_mansong_quota = Model('p_mansong_quota');
        $model_mansong = Model('p_mansong');
        $model_mansong_rule = Model('p_mansong_rule');

        if($start_time >= $end_time) {
            showDialog(Language::get('greater_than_start_time'));
        }
        if(empty($mansong_name)) {
            showDialog(Language::get('mansong_name_error'));
        }

        $start_time_limit = $model_mansong->getMansongNewStartTime($_SESSION['store_id']);
        if(!empty($start_time_limit) && $start_time_limit > $start_time) {
            $start_time = $start_time_limit;
        }

        if (!checkPlatformStore()) {
            //检查当前套餐是否可用
            $current_mansong_quota = $model_mansong_quota->getMansongQuotaCurrent($_SESSION['store_id']);
            if(empty($current_mansong_quota)) {
                showDialog(Language::get('mansong_quota_current_error'),'reload','error');
            }

            //验证输入
            $quota_start_time = intval($current_mansong_quota['start_time']);
            $quota_end_time = intval($current_mansong_quota['end_time']);

            if($start_time < $quota_start_time) {
                showDialog(sprintf(Language::get('mansong_add_start_time_explain'),date('Y-m-d',$current_mansong_quota['start_time'])));
            }
            if($end_time > $quota_end_time) {
                showDialog(sprintf(Language::get('mansong_add_end_time_explain'),date('Y-m-d',$current_mansong_quota['end_time'])));
            }
        }

        if(empty($_POST['mansong_rule'])) {
            showDialog('满即送规则不能为空');
        }

        $param = array();
        $param['mansong_name'] = $mansong_name;
        $param['start_time'] = $start_time;
        $param['end_time'] = $end_time;
        $param['store_id'] = $_SESSION['store_id'];
        $param['store_name'] = $_SESSION['store_name'];
        $param['member_id'] = $_SESSION['member_id'];
        $param['member_name'] = $_SESSION['member_name'];
        $param['quota_id'] = $current_mansong_quota['quota_id'] ? $current_mansong_quota['quota_id'] : 0;
        $param['remark'] = trim($_POST['remark']);
        $mansong_id = $model_mansong->addMansong($param);
        if($mansong_id) {
            $mansong_rule_array = array();
            foreach ($_POST['mansong_rule'] as $value) {
                list($price, $discount, $goods_id) = explode(',', $value);
                $mansong_rule = array();
                $mansong_rule['mansong_id'] = $mansong_id;
                $mansong_rule['price'] = $price;
                $mansong_rule['discount'] = $discount;
                $mansong_rule['goods_id'] = $goods_id;
                $mansong_rule_array[] = $mansong_rule;
            }
            //生成规则
            $result = $model_mansong_rule->addMansongRuleArray($mansong_rule_array);

            $this->recordSellerLog('添加满即送活动，活动名称：'.$mansong_name);

            // 自动发布动态
            // mansong_name,start_time,end_time,store_id
            $data_array = array();
            $data_array['mansong_name'] = $param['mansong_name'];
            $data_array['start_time']   = $param['start_time'];
            $data_array['end_time']     = $param['end_time'];
            $data_array['store_id']     = $_SESSION['store_id'];
            $this->storeAutoShare($data_array, 'mansong');

            showDialog(Language::get('mansong_add_success'), urlShop('store_promotion_mansong', 'mansong_list'), 'succ');
        } else {
            showDialog(Language::get('mansong_add_fail'));
        }
    }

    /**
     * 满就送活动详细信息
     **/
    public function mansong_detail() {
        $mansong_id = intval($_GET['mansong_id']);

        $model_mansong = Model('p_mansong');
        $model_mansong_rule = Model('p_mansong_rule');

        $mansong_info = $model_mansong->getMansongInfoByID($mansong_id, $_SESSION['store_id']);
        if(empty($mansong_info)) {
            showDialog(L('param_error'));
        }
        $this->assign('mansong_info', $mansong_info);

        $param = array();
        $param['mansong_id'] = $mansong_id;
        $rule_list = $model_mansong_rule->getMansongRuleListByID($mansong_id);
        $this->assign('list',$rule_list);

        //输出导航
        self::profile_menu('mansong_detail');
        $this->render('store_promotion_mansong.detail');
    }

    /**
     * 满就送活动删除
     **/
    public function mansong_del() {
        $mansong_id = intval($_POST['mansong_id']);

        $model_mansong = Model('p_mansong');

        $mansong_info = $model_mansong->getMansongInfoByID($mansong_id, $_SESSION['store_id']);
        if(empty($mansong_info)) {
            showDialog(L('param_error'));
        }

        $condition = array();
        $condition['mansong_id'] = $mansong_id;
        $result = $model_mansong->delMansong($condition);

        if($result) {
            $this->recordSellerLog('删除满即送活动，活动名称：'.$mansong_rule['mansong_name']);
            showDialog(L('spd_common_op_succ'), urlShop('store_promotion_mansong', 'mansong_list'), 'succ');
        } else {
            showDialog(L('spd_common_op_fail'));
        }
    }

    /**
     * 满就送套餐购买
     **/
    public function mansong_quota_add() {
        self::profile_menu('mansong_quota_add');
        $this->render('store_promotion_mansong_quota.add');
    }

    /**
     * 满就送套餐购买保存
     **/
    public function mansong_quota_add_save() {
        $mansong_quota_quantity = intval($_POST['mansong_quota_quantity']);

        if($mansong_quota_quantity <= 0 || $mansong_quota_quantity > 12) {
            showDialog(Language::get('mansong_quota_quantity_error'));
        }

        //获取当前价格
        $current_price = intval(C('promotion_mansong_price'));

        //获取该用户已有套餐
        $model_mansong_quota = Model('p_mansong_quota');
        $current_mansong_quota= $model_mansong_quota->getMansongQuotaCurrent($_SESSION['store_id']);
        $add_time = 86400 * 30 * $mansong_quota_quantity;
        if(empty($current_mansong_quota)) {
            //生成套餐
            $param = array();
            $param['member_id'] = $_SESSION['member_id'];
            $param['member_name'] = $_SESSION['member_name'];
            $param['store_id'] = $_SESSION['store_id'];
            $param['store_name'] = $_SESSION['store_name'];
            $param['start_time'] = TIMESTAMP;
            $param['end_time'] = TIMESTAMP + $add_time;
            $model_mansong_quota->addMansongQuota($param);
        } else {
            $param = array();
            $param['end_time'] = array('exp', 'end_time + ' . $add_time);
            $model_mansong_quota->editMansongQuota($param, array('quota_id' => $current_mansong_quota['quota_id']));
        }

        //记录店铺费用
        $this->recordStoreCost($current_price * $mansong_quota_quantity, '购买满即送');

        $this->recordSellerLog('购买'.$mansong_quota_quantity.'份满即送套餐，单价'.$current_price.L('spd_yuan'));

        showDialog(Language::get('mansong_quota_add_success'), urlShop('store_promotion_mansong', 'mansong_list'), 'succ');
    }

    /**
     * 选择活动商品
     **/
    public function search_goods() {
        $model_goods = Model('goods');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        $goods_list = $model_goods->getGeneralGoodsList($condition, '*', 8);

        $this->assign('goods_list', $goods_list);
        $this->assign('show_page', $model_goods->showpage());
        $this->render('store_promotion_mansong.goods', 'null_layout');
    }


    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function profile_menu($menu_key='') {
        $menu_array = array(
            1=>array('menu_key'=>'mansong_list','menu_name'=>Language::get('promotion_active_list'),'menu_url'=>urlShop('store_promotion_mansong', 'mansong_list')),
        );
        switch ($menu_key){
            case 'mansong_add':
                $menu_array[] = array('menu_key'=>'mansong_add','menu_name'=>Language::get('promotion_join_active'),'menu_url'=>urlShop('store_promotion_mansong', 'mansong_add'));
                break;
            case 'mansong_quota_add':
                $menu_array[] = array('menu_key'=>'mansong_quota_add','menu_name'=>Language::get('promotion_buy_product'),'menu_url'=>urlShop('store_promotion_mansong', 'mansong_quota_add'));
                break;
            case 'mansong_detail':
                $menu_array[] = array('menu_key'=>'mansong_detail','menu_name'=>Language::get('mansong_active_content'),'menu_url'=>urlShop('store_promotion_mansong', 'mansong_detail', array('mansong_id' => $_GET['mansong_id'])));
                break;
        }
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }

}
