<?php
/**
 * 我的购物车
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Mobile\Controller;
use Mobile\Controller\MobileMemberController;
use Common\Lib\Language;
use Common\Lib\Db;
use Common\Lib\Log;
use Common\Lib\Model;


class MemberCartController extends MobileMemberController {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 购物车列表
     */
    public function cart_list() {

    	$cart_list = $this->_get_cart_info();
        output_data($cart_list);
    }
    
    public function _get_cart_info()
    {
    	$merge_store_goods = $_GET['merge_store_goods'];
    	
    	$model_cart = Model('cart');
    	
    	$condition = array('buyer_id' => $this->member_info['member_id']);
    	$cart_list  = $model_cart->listCart('db', $condition);
    	
    	// 购物车列表 [得到最新商品属性及促销信息]
    	$logic_buy_1 = logic('buy_1');
    	$cart_list = $logic_buy_1->getGoodsCartList($cart_list);
    	
    	//购物车商品以店铺ID分组显示,并计算商品小计,店铺小计与总价由JS计算得出
    	$store_cart_list = array();
    	$sum = 0;
    	foreach ($cart_list as $cart) {
    		if (!empty($cart['gift_list'])) {
    			foreach ($cart['gift_list'] as $key => $val) {
    				$cart['gift_list'][$key]['goods_image_url'] = cthumb($val['gift_goodsimage'], $cart['store_id']);
    			}
    			$cart['gift_list'] = array_values($cart['gift_list']);
    		}
    		$store_cart_list[$cart['store_id']]['store_id'] = $cart['store_id'];
    		$store_cart_list[$cart['store_id']]['store_name'] = $cart['store_name'];
    		$cart['goods_image_url'] = cthumb($cart['goods_image'], $cart['store_id']);
    		$cart['goods_total'] = daPriceFormat($cart['goods_price'] * $cart['goods_num']);
    		$cart['xianshi_info'] = $cart['xianshi_info'] ? $cart['xianshi_info'] : array();
    		$cart['groupbuy_info'] = $cart['groupbuy_info'] ? $cart['groupbuy_info'] : array();
    		$store_cart_list[$cart['store_id']]['goods'][] = $cart;
    		$sum += $cart['goods_total'];
    	}
    	
    	// 店铺优惠券
    	$condition = array();
    	$condition['voucher_t_gettype'] = 3;
    	$condition['voucher_t_state'] = 1;
    	$condition['voucher_t_end_date'] = array('gt', time());
    	$condition['voucher_t_mgradelimit'] = array('elt', $this->member_info['level']);
    	$condition['voucher_t_store_id'] = array('in', array_keys($store_cart_list));
    	$voucher_template = Model('voucher')->getVoucherTemplateList($condition);
    	if (!empty($voucher_template)) {
    		foreach ($voucher_template as $val) {
    			$param = array();
    			$param['voucher_t_id'] = $val['voucher_t_id'];
    			$param['voucher_t_price'] = $val['voucher_t_price'];
    			$param['voucher_t_limit'] = $val['voucher_t_limit'];
    			$param['voucher_t_end_date'] = date('Y年m月d日', $val['voucher_t_end_date']);
    			$store_cart_list[$val['voucher_t_store_id']]['voucher'][] = $param;
    		}
    	}
    	
    	//取得店铺级活动 - 可用的满即送活动
    	$mansong_rule_list = $logic_buy_1->getMansongRuleList(array_keys($store_cart_list));
    	if (!empty($mansong_rule_list)) {
    		foreach ($mansong_rule_list as $key => $val) {
    			$store_cart_list[$key]['mansong'] = $val;
    		}
    	}
    	
    	//取得哪些店铺有满免运费活动
    	$free_freight_list = $logic_buy_1->getFreeFreightActiveList(array_keys($store_cart_list));
    	if (!empty($free_freight_list)) {
    		foreach ($free_freight_list as $key => $val) {
    			$store_cart_list[$key]['free_freight'] = $val;
    		}
    	}
    	$ret = array('cart_list' => array_values($store_cart_list), 'sum' => daPriceFormat($sum), 'cart_count' => count($cart_list));
    	
    	if ($merge_store_goods == 1)
    	{
    		//微信小程序 不区分店铺
    		$ret['cart_list'] = $this->_merge_store_goods($ret['cart_list']);
    	}
    	
    	return $ret;
    }
    
    public function _merge_store_goods($cart_list)
    {
    	$ret = array();
    	if (empty($cart_list))
    	{
    		return $ret;
    	}
    	foreach ($cart_list as $cart_item)
    	{
    		$goods_list = $cart_item['goods'];
    		foreach ($goods_list as $goods_item)
    		{
    			$ret[] = $goods_item;
    		}
    	}
    	return $ret;
    }

    /**
     * 购物车列表
     */
    public function cart_list_old() {
        $model_cart = Model('cart');
    
        $condition = array('buyer_id' => $this->member_info['member_id']);
        $cart_list  = $model_cart->listCart('db', $condition);
    
        // 购物车列表 [得到最新商品属性及促销信息]
        $cart_list = logic('buy_1')->getGoodsCartList($cart_list, $jjgObj);
        $sum = 0;
        foreach ($cart_list as $key => $value) {
            $cart_list[$key]['goods_image_url'] = cthumb($value['goods_image'], $value['store_id']);
            $cart_list[$key]['goods_sum'] = daPriceFormat($value['goods_price'] * $value['goods_num']);
            $sum += $cart_list[$key]['goods_sum'];
        }
    
        output_data(array('cart_list' => $cart_list, 'sum' => daPriceFormat($sum)));
    }

    /**
     * 购物车添加
     */
    public function cart_add() {
        if(!$this->member_info['is_buy']) output_error('您没有商品购买的权限,如有疑问请联系客服人员');
        $goods_id = intval($_POST['goods_id']);
        $quantity = intval($_POST['quantity']);
        $is_with_cart_info = intval($_POST['is_with_cart_info']);
        
        if($goods_id <= 0 || $quantity <= 0) {
            output_error('参数错误');
        }

        $model_goods = Model('goods');
        $model_cart = Model('cart');
        $logic_buy_1 = Logic('buy_1');

        $goods_info = $model_goods->getGoodsOnlineInfoAndPromotionById($goods_id);

        //验证是否可以购买
        if(empty($goods_info)) {
            output_error('商品已下架或不存在');
        }

        //抢购
        $logic_buy_1->getGroupbuyInfo($goods_info);
        if ($goods_info['ifgroupbuy']) {
            if ($goods_info['upper_limit'] && $quantity > $goods_info['upper_limit']) {
                output_error('抢购商品购买超限，最多可购买'.$goods_info['upper_limit']."个");
            }
        }

        //限时折扣
        $logic_buy_1->getXianshiInfo($goods_info,$quantity);

        if ($goods_info['store_id'] == $this->member_info['store_id']) {
            output_error('不能购买自己发布的商品');
        }
        if(intval($goods_info['goods_storage']) < 1 || intval($goods_info['goods_storage']) < $quantity) {
            output_error('库存不足');
        }

        if ($goods_info['is_virtual'] || $goods_info['is_fcode'] || $goods_info['is_book']) {
            output_error('该商品不允许加入购物车，请直接购买');
        }

        $param = array();
        $param['buyer_id']  = $this->member_info['member_id'];
        $param['store_id']  = $goods_info['store_id'];
        $param['goods_id']  = $goods_info['goods_id'];
        $param['goods_name'] = $goods_info['goods_name'];
        $param['goods_price'] = $goods_info['goods_price'];
        $param['goods_image'] = $goods_info['goods_image'];
        $param['store_name'] = $goods_info['store_name'];
 
        $result = $model_cart->addCart($param, 'db', $quantity);
 
        $ret = array("add_res" => 0, "cart_info" => array());
        if ($is_with_cart_info)
        {
        	$ret['cart_info'] = $this->_get_cart_info();
        }
        
        if($result) {
        	$ret['add_res'] = 1;
            output_data($ret);
        } else {
            output_error('购物车加入失败');
        }
    }

    /**
     * 购物车删除
     */
    public function cart_del() {
        $cart_id = intval($_POST['cart_id']);

        $model_cart = Model('cart');

        if($cart_id > 0) {
            $condition = array();
            $condition['buyer_id'] = $this->member_info['member_id'];
            $condition['cart_id'] = $cart_id;

            $model_cart->delCart('db', $condition);
        }

        output_data('1');
    }

    /**
     * 更新购物车购买数量
     */
    public function cart_edit_quantity() {
        $cart_id = intval(abs($_POST['cart_id']));
        $quantity = intval(abs($_POST['quantity']));
        if(empty($cart_id) || empty($quantity)) {
            output_error('参数错误');
        }

        $model_cart = Model('cart');

        $cart_info = $model_cart->getCartInfo(array('cart_id'=>$cart_id, 'buyer_id' => $this->member_info['member_id']));

        //检查是否为本人购物车
        if($cart_info['buyer_id'] != $this->member_info['member_id']) {
            output_error('参数错误');
        }

        //检查库存是否充足
        if(!$this->_check_goods_storage($cart_info, $quantity, $this->member_info['member_id'])) {
            output_error('超出限购数或库存不足');
        }

        $data = array();
        $data['goods_num'] = $quantity;
        $update = $model_cart->editCart($data, array('cart_id'=>$cart_id));
        if ($update) {
            $return = array();
            $return['quantity'] = $quantity;
            $return['goods_price'] = daPriceFormat($cart_info['goods_price']);
            $return['total_price'] = daPriceFormat($cart_info['goods_price'] * $quantity);
            output_data($return);
        } else {
            output_error('修改失败');
        }
    }

    /**
     * 检查库存是否充足
     */
    private function _check_goods_storage(& $cart_info, $quantity, $member_id) {
        $model_goods= Model('goods');
        $model_bl = Model('p_bundling');
        $logic_buy_1 = Logic('buy_1');

        if ($cart_info['bl_id'] == '0') {
            //普通商品
            $goods_info = $model_goods->getGoodsOnlineInfoAndPromotionById($cart_info['goods_id']);

            //手机专享
            $logic_buy_1->getMbSoleInfo($goods_info);
            
            //抢购
            $logic_buy_1->getGroupbuyInfo($goods_info);
            if ($goods_info['ifgroupbuy']) {
                if ($goods_info['upper_limit'] && $quantity > $goods_info['upper_limit']) {
                    return false;
                }
            }

            //限时折扣
            $logic_buy_1->getXianshiInfo($goods_info,$quantity);

            if(intval($goods_info['goods_storage']) < $quantity) {
                return false;
            }
            $goods_info['cart_id'] = $cart_info['cart_id'];
            $cart_info = $goods_info;
        } else {
            //优惠套装商品
            $bl_goods_list = $model_bl->getBundlingGoodsList(array('bl_id' => $cart_info['bl_id']));
            $goods_id_array = array();
            foreach ($bl_goods_list as $goods) {
                $goods_id_array[] = $goods['goods_id'];
            }
            $bl_goods_list = $model_goods->getGoodsOnlineListAndPromotionByIdArray($goods_id_array);

            //如果有商品库存不足，更新购买数量到目前最大库存
            foreach ($bl_goods_list as $goods_info) {
                if (intval($goods_info['goods_storage']) < $quantity) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * 查询购物车商品数量
     */
    function cart_count() {
        $param['cart_count'] = Model('cart')->countCartByMemberId($this->member_info['member_id']);
        output_data($param);
    }

    /**
     * 批量添加购物车
     * cartlist 格式为goods_id1,num1|goods_id2,num2
     */
    public function cart_batchadd(){
        $param = $_POST;
        $cartlist_str = trim($param['cartlist']);
        $cartlist_arr = $cartlist_str?explode('|',$cartlist_str):array();
        if(!$cartlist_arr) {
            output_error('参数错误');
        }

        $cartlist_new =  array();
        foreach($cartlist_arr as $k=>$v){
            $tmp = $v?explode(',',$v):array();
            if (!$tmp) {
                continue;
            }
            $cartlist_new[$tmp[0]]['goods_num'] = $tmp[1];
        }
        Model('cart')->batchAddCart($cartlist_new, $this->member_info['member_id'], $this->member_info['store_id']);
        output_data('1');
    }
}
