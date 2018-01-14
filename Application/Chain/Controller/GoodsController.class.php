<?php
/**
 * 物流自提服务站首页
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Chain\Controller;
use Chain\Controller\BaseChainCenterController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;


class GoodsController extends BaseChainCenterController {
    public function __construct(){
        parent::__construct();
    }
    
    public function index() {
        $model_goods = Model('goods');
        $where = array();
        $where['store_id'] = $_SESSION['chain_store_id'];
        if (trim($_GET['keyword']) != '') {
            switch ($_GET['search_type']) {
                case 0:
                    $where['goods_name'] = array('like', '%' . trim($_GET['keyword']) . '%');
                    break;
                case 1:
                    $where['goods_serial'] = array('like', '%' . trim($_GET['keyword']) . '%');
                    break;
                case 2:
                    $where['goods_commonid'] = intval($_GET['keyword']);
                    break;
            }
        }
        
        $goods_list = $model_goods->getGeneralGoodsCommonList($where, '*', 10);
        $stock_list = array();
        if (!empty($goods_list)) {
            $commonid_array = array();
            foreach ($goods_list as $val) {
                $commonid_array[] = $val['goods_commonid'];
            }
            $goodsid_array = $model_goods->getGoodsOnlineList(array('goods_commonid' => array('in', $commonid_array)), 'min(goods_id) goods_id,goods_commonid', 0, 'goods_id desc', 0, 'goods_commonid');
            $goodsid_array = array_under_reset($goodsid_array, 'goods_commonid');
            $this->assign('goodsid_array', $goodsid_array);
            $stock_array = Model('chain_stock')->getChainStockList(array('chain_id' => $_SESSION['chain_id'], 'goods_commonid' => array('in', $commonid_array)));
            if (!empty($stock_array)) {
                foreach ($stock_array as $val) {
                    if (!isset($stock_list[$val['goods_commonid']])) {
                        $stock_list[$val['goods_commonid']]['stock'] = 0;
                    }
                    $stock_list[$val['goods_commonid']]['stock'] += intval($val['stock']);
                    $stock_list[$val['goods_commonid']]['goods_id'] = $val['goods_id'];
                }
            }
        }
        $this->assign('stock_list', $stock_list);
        $this->assign('show_page', $model_goods->showpage());
        $this->assign('goods_list', $goods_list);
        
        $this->profile_menu('goods_list', 'goods_list');
        $this->render('goods.list');
    }
    
    /**
     * 设置库存
     */
    public function set_stock() {
        $model_chain_stock = Model('chain_stock');
        if (chksubmit()) {
            foreach ($_POST['stock'] as $key => $val) {
                $insert = array();
                $insert['chain_id']         = $_SESSION['chain_id'];
                $insert['goods_id']         = intval($key);
                $insert['goods_commonid']   = intval($_POST['goods_commonid']);
                $insert['stock']            = intval($val);
                $model_chain_stock->addChainStock($insert);
            }
            showDialog('操作成功', 'reload', 'succ');
        }
        
        $common_id = intval($_GET['common_id']);
        $model_goods = Model('goods');
        $goodscommon_info = $model_goods->getGoodsCommonInfoByID($common_id);
        if ($goodscommon_info['store_id'] != $_SESSION['chain_store_id']) {
            $this->assign('error', true);
        }
        $this->assign('goodscommon_info', $goodscommon_info);
        $spec_name = array_values((array)unserialize($goodscommon_info['spec_name']));
        $this->assign('spec_name', $spec_name);
        
        $goods_info = $model_goods->getGeneralGoodsOnlineList(array('goods_commonid' => $common_id), 'goods_id,goods_spec,goods_serial,goods_price');
        
        $stock_info = $model_chain_stock->getChainStockList(array('chain_id' => $_SESSION['chain_id'], 'goods_commonid' => $common_id));
        $stock_info = array_under_reset($stock_info, 'goods_id');
        $this->assign('stock_info', $stock_info);

        $goods_array = array();
        if (!empty($goods_info)) {
            foreach ($goods_info as $val) {
                $goods_spec = array_values((array)unserialize($val['goods_spec']));
                $goods_array[$val['goods_id']]['goods_spec'] = $goods_spec;
                $goods_array[$val['goods_id']]['goods_serial'] = $val['goods_serial'];
                $goods_array[$val['goods_id']]['goods_price'] = $val['goods_price'];
            }
        }

        $this->assign('goods_array', $goods_array);
        $this->render('goods.set_stock', 'null_layout');
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type,$menu_key) {
        $menu_array = array();
        switch ($menu_type) {
            case 'goods_list':
                $menu_array = array(
                array('menu_key' => 'goods_list',    'menu_name' => '商品列表', 'menu_url' => urlChain('goods', 'index'))
                );
                break;
        }
        $this->assign ( 'chain_menu', $menu_array );
        $this->assign ( 'menu_key', $menu_key );
    }
}
