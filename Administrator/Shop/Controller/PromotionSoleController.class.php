<?php
/**
 * 手机专享管理
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
use Common\Lib\Cache;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;
use Common\Lib\Validate;


class PromotionSoleController extends SystemController {
    public function __construct() {
        parent::__construct();
        //检查审核功能是否开启
        if (intval($_GET['promotion_allow']) !== 1 && intval(C('promotion_allow')) !== 1){
            $url = array(
                array(
                    'url'=> $GLOBALS['_PAGE_URL'] . '&c=Setting',
                    'msg'=>L('close'),
                ),
                array(
                    'url'=>$GLOBALS['_PAGE_URL'] . '&c=PromotionSole&promotion_allow=1',
                    'msg'=>L('open'),
                ),
            );
            showMessage('商品促销功能尚未开启', $url, 'html', 'succ', 1, 6000);
        }
    }

    /**
     * 默认Op
     */
    public function index() {
        //自动开启优惠套装
        if (intval($_GET['promotion_allow']) === 1){
            $model_setting = Model('setting');
            $update_array = array();
            $update_array['promotion_allow'] = 1;
            $model_setting->updateSetting($update_array);
        }
        $this->goods_list();
    }

    public function goods_list() {
        if ($_REQUEST['store_id'] > 0) {
            $store_info = Model('store')->getStoreInfoByID($_REQUEST['store_id']);
            $this->assign('store_info', $store_info);
        }

        $this->assign('store_id', $_REQUEST['store_id']);
		$this->setDirquna('shop');
        $this->render('promotion_sole.goods_list');
    }

    /**
     * 活动商品管理XML
     */
    public function goods_list_xml() {
        $condition = array();
        if (strlen($q = trim($_REQUEST['query']))) {
            switch ($_REQUEST['qtype']) {
                case 'goods_id':
                    $condition['goods_id'] = array('like', '%'.$q.'%');
                    break;
            }
        }
        if (($storeId = (int) $_REQUEST['store_id']) > 0) {
            $condition['store_id'] = $storeId;
        }

        $model_sole = Model('p_sole');
        $goods_list = (array) $model_sole->getSoleGoodsList($condition, 'goods_id', $_REQUEST['rp']);

        $data = array();
        $data['now_page'] = $model_sole->shownowpage();
        $data['total_num'] = $model_sole->gettotalnum();

        if (!empty($goods_list)) {
            $goodsid_array = array();
            foreach ($goods_list as $val) {
                $goodsid_array[] = $val['goods_id'];
            }
            $goods_list = Model('goods')->getGoodsList(array(
                'goods_id' => array('in', $goodsid_array),
            ));
        }

        $flippedOwnShopIds = array_flip(Model('store')->getOwnShopIds());

        $allGc = Model('goods_class')->getGoodsClassForCacheModel();

        foreach ($goods_list as $val) {
            $o = '<a class="btn red confirm-del-on-click" href="javascript:;" data-href="' . urlAdminShop('promotion_sole', 'del_goods', array('goods_id' => $val['goods_id'])) . '"><i class="fa fa-trash"></i>删除</a>';

            $o .= '<a class="btn green" target="_blank" href="' . urlShop('goods', 'index', array('goods_id' => $val['goods_id'])) . '"><i class="fa fa-list-alt"></i>查看</a>';

            $i = array();
            $i['operation'] = $o;
            $i['goods_id'] = $val['goods_id'];
            $i['goods_name'] = $val['goods_name'];

            $i['store_name'] = '<a target="_blank" href="' . urlShop('show_store', 'index', array('store_id' => $val['store_id'])) . '">' . $val['store_name'] . '</a>';

            if (isset($flippedOwnShopIds[$val['store_id']])) {
                $i['store_name'] .= '<span class="ownshop">[自营]</span>';
            }

            $i['gc_name'] = $allGc[$val['gc_id']]['gc_name'];

            $gi = thumb($val, 'small');
            $i['goods_img_url'] = <<<EOB
<a href="javascript:;" class="pic-thumb-tip" onMouseOut="toolTip()" onMouseOver="toolTip('<img src=\'{$gi}\'>')">
<i class='fa fa-picture-o'></i></a>
EOB;

            $i['goods_price'] = $val['goods_price'];

            $data['list'][$val['goods_id']] = $i;
        }

        echo $this->flexigridXML($data);
        exit;
    }

    /**
     * 套餐列表
     */
    public function sole_quota_list() {
		$this->setDirquna('shop');
        $this->render('promotion_sole.quota_list');
    }

    /**
     * 套餐列表XML
     */
    public function sole_quota_list_xml() {
        $condition = array();
        if (strlen($q = trim($_REQUEST['query']))) {
            switch ($_REQUEST['qtype']) {
                case 'store_name':
                    $condition['store_name'] = array('like', '%'.$q.'%');
                    break;
            }
        }

        $model_sole = Model('p_sole');
        $list = (array) $model_sole->getSoleQuotaList($condition, '*', $_REQUEST['rp']);

        $data = array();
        $data['now_page'] = $model_sole->shownowpage();
        $data['total_num'] = $model_sole->gettotalnum();

        foreach ($list as $val) {
            $i = array();

            $i['operation'] = '<a class="btn green" href="' . urlAdminShop('promotion_sole', 'goods_list', array('store_id' => $val['store_id'])) . '"><i class="fa fa-list-alt"></i>查看商品</a>';

            $i['store_name'] = '<a target="_blank" href="' . urlShop('show_store', 'index', array('store_id' => $val['store_id'])) . '">' . $val['store_name'] . '</a>';

            $i['start_time_text'] = date("Y-m-d", $val['sole_quota_starttime']);
            $i['end_time_text'] = date("Y-m-d", $val['sole_quota_endtime']);

            $i['state_text'] = $val['sole_state'] == '1' ? '开启' : '关闭';

            $data['list'][$val['sole_quota_id']] = $i;
        }

        echo $this->flexigridXML($data);
        exit;
    }

    /**
     * 删除推荐商品
     */
    public function del_goods() {
        $goodsIds = array();
        foreach (explode(',', (string) $_REQUEST['goods_id']) as $i) {
            $goodsIds[(int) $i] = null;
        }
        unset($goodsIds[0]);

        if ($goodsIds) {
            $goodsIds = array_keys($goodsIds);
            $rs = Model('p_sole')->delSoleGoods(array('goods_id' => array('in', $goodsIds)));

            if ($rs) {
                $this->jsonOutput();
                return;
            }
        }

        $this->jsonOutput('操作失败');
    }

    /**
     * 设置
     */
    public function sole_setting() {
        // 实例化模型
        $model_setting = Model('setting');

        if (chksubmit()){
            // 验证
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["promotion_sole_price"], "require"=>"true", 'validator'=>'Number', "message"=>'请填写手机专享套餐价格'),
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }

            $data['promotion_sole_price'] = intval($_POST['promotion_sole_price']);

            $return = $model_setting->updateSetting($data);
            if($return){
                $this->log(L('spd_set').'手机专享套餐');
                showMessage(L('spd_common_op_succ'));
            }else{
                showMessage(L('spd_common_op_fail'));
            }
        }

        // 查询setting列表
        $setting = $model_setting->GetListSetting();
        $this->assign('setting',$setting);
		$this->setDirquna('shop');

        $this->render('promotion_sole.setting');
    }
}
