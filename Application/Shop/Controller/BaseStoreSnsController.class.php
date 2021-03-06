<?php
namespace Shop\Controller;
use Shop\Controller\BaseController;
use Common\Lib\Language;
use Common\Lib\Model;


class BaseStoreSnsController extends BaseController {
	
	const MAX_RECORDNUM = 20;   // 允许插入新记录的最大次数，sns页面该常量是一样的。
	public function __construct(){
		Language::read('common,store_layout');
		$this->assign('max_recordnum', self::MAX_RECORDNUM);
		$this->setDir('store');
		$this->setLayout('store_layout');
		// 自定义导航条
		$this->getStoreNavigation();
		//输出头部的公用信息
		$this->showLayout();
		//查询会员信息
		$this->getMemberAndGradeInfo(false);

	}

	// 自定义导航条
	protected function getStoreNavigation() {
		$model_store_navigation = Model('store_navigation');
		$store_navigation_list = $model_store_navigation->getStoreNavigationList(array('sn_store_id' => intval($_GET['sid'])));
		$this->assign('store_navigation_list', $store_navigation_list);
	}

	protected function getStoreInfo($store_id) {
		//得到店铺等级信息
		$store_info = Model('store')->getStoreInfoByID($store_id);
		if (empty($store_info)) {
			showMessage(Language::get('store_sns_store_not_exists'),'','html','error');
		}
		//处理地区信息
		$area_array = array();
		$area_array = explode("\t",$store_info["area_info"]);
		$map_city   = Language::get('store_sns_city');
		$city   = '';
		if(strpos($area_array[0], $map_city) !== false){
			$city   = $area_array[0];
		}else {
			$city   = $area_array[1];
		}
		$store_info['city'] = $city;

		$this->assign('store_theme', $store_info['store_theme']);
		$this->assign('store_info', $store_info);


		//店铺分类
		$goodsclass_model = Model('store_goods_class');
		$goods_class_list = $goodsclass_model->getShowTreeList($store_id);
		$this->assign('goods_class_list', $goods_class_list);
	}
}

