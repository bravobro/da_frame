<?php
/**
 * 卖家商品咨询管理
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
use Common\Lib\Cache;
use Common\Lib\Db;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;
use Common\Lib\QueueClient;

class StoreConsultController extends BaseSellerController {
    public function __construct() {
        parent::__construct();
        Language::read('member_store_consult_index');
    }

    /**
     * 商品咨询首页
     */
    public function index(){
        $this->consult_list();
    }

    /**
     * 商品咨询列表页
     */
    public function consult_list(){
        $consult    = Model('consult');
        $list_consult   = array();
        $where = array();
        if (trim($_GET['type']) == 'to_reply') {
            if (C('dbdriver') == 'mysqli') {
                $where['consult_reply'] = array('eq', '');
            } else {
                $where['consult_reply'] = array('exp', 'consult_reply IS NULL');
            }
        } elseif (trim($_GET['type'] == 'replied')) {
            if (C('dbdriver') == 'mysqli') {
                $where['consult_reply'] = array('neq', '');
            } else {
                $where['consult_reply'] = array('exp', 'consult_reply IS NOT NULL');
            }
        }
        if (intval($_GET['ctid']) > 0) {
            $where['ct_id'] = intval($_GET['ctid']);
        }
        $where['store_id'] = $_SESSION['store_id'];
        $list_consult   = $consult->getConsultList($where,'*', 0, 10);
        $this->assign('show_page',$consult->showpage());
        $this->assign('list_consult',$list_consult);

        // 咨询类型
        $consult_type = rkcache('consult_type', true);
        $this->assign('consult_type', $consult_type);

        $_GET['type']   = empty($_GET['type'])?'consult_list':$_GET['type'];
        self::profile_menu('consult',$_GET['type']);
        $this->render('store_consult_manage');
    }

    /**
     * 商品咨询删除处理
     */
    public function drop_consult(){
        $ids = trim($_GET['id']);
        if (!preg_match('/^[\d,]+$/i', $ids)) {
            showDialog(L('para_error'), '', 'error');
        }
        $consult = Model('consult');
        $id_array = explode(',',trim($_GET['id']));
        $where = array();
        $where['store_id'] = $_SESSION['store_id'];
        $where['consult_id'] = array('in', $id_array);
        $state = $consult->delConsult($where);
        if($state) {
            showDialog(Language::get('store_consult_drop_success'),'reload','succ');
        } else {
            showDialog(Language::get('store_consult_drop_fail'));
        }
    }

    /**
     * 回复商品咨询表单页
     */
    public function reply_consult(){
        $consult = Model('consult');
        $list_consult = array();
        $search_array = array();
        $search_array['consult_id'] = intval($_GET['id']);
        $search_array['store_id']   = $_SESSION['store_id'];
        $consult_info = $consult->getConsultInfo($search_array);
        $this->assign('consult',$consult_info);
        $this->render('store_consult_form','null_layout');
    }

    /**
     * 商品咨询回复内容的保存处理
     */
    public function reply_save(){
        $consult_id = intval($_POST['consult_id']);
        if ($consult_id <= 0) {
            showDialog(L('wrong_argument'));
        }
        $consult = Model('consult');
        $update = array();
        $update['consult_reply'] = $_POST['content'];
        $condtion = array();
        $condtion['store_id'] = $_SESSION['store_id'];
        $condtion['consult_id'] = $consult_id;
        $state = $consult->editConsult($condtion, $update);
        if($state){
            $consult_info = $consult->getConsultInfo(array('consult_id' => $consult_id));
            // 发送用户消息
            $param = array();
            $param['code'] = 'consult_goods_reply';
            $param['member_id'] = $consult_info['member_id'];
            $param['param'] = array(
                'goods_name' => $consult_info['goods_name'],
                'consult_url' => urlShop('member_consult', 'my_consult')
            );
            QueueClient::push('sendMemberMsg', $param);

            showDialog(Language::get('spd_common_op_succ'),'reload','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
        } else {
            showDialog(Language::get('spd_common_op_fail'));
        }
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function profile_menu($menu_type,$menu_key='',$array=array()) {
        Language::read('member_layout');
        $menu_array     = array();
        switch ($menu_type) {
            case 'consult':
                $menu_array = array(
                1=>array('menu_key'=>'consult_list',    'menu_name'=>Language::get('spd_member_path_all_consult'),           'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=StoreConsult&a=consult_list'),
                2=>array('menu_key'=>'to_reply',    'menu_name'=>Language::get('spd_member_path_unreplied_consult'),         'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=StoreConsult&a=consult_list&type=to_reply'),
                3=>array('menu_key'=>'replied', 'menu_name'=>Language::get('spd_member_path_replied_consult'),           'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=StoreConsult&a=consult_list&type=replied'));
                break;
        }
        if(!empty($array)) {
            $menu_array[] = $array;
        }
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }
}