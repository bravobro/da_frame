<?php
/**
 * 微商城
 *
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Microshop\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Tpl;
use Common\Lib\Model;
use Common\Lib\Page;


class GoodsController extends SystemController {

    const MICROSHOP_CLASS_LIST = 'admin.php?m=Microshop&c=GoodsClass&a=goodsclass_list';
    const GOODS_FLAG = 1;
    const PERSONAL_FLAG = 2;
    const ALBUM_FLAG = 3;
    const STORE_FLAG = 4;

    public function __construct(){
        parent::__construct();
        Language::read('store', 'Home');
        Language::read('microshop', 'Home');
    }

    public function index() {
       $this->goods_manage();
    }

    /**
     * 随心看管理
     */
    public function goods_manage()
    {
        $this->setDirquna('microshop');
		$this->render('microshop_goods.manage');
    }

    /**
     * 随心看管理XML
     */
    public function goods_manage_xml()
    {
        $condition = array();

        if ($_REQUEST['advanced']) {
            if (strlen($q = trim((string) $_REQUEST['commend_id']))) {
                $condition['commend_id'] = (int) $q;
            }
            if (strlen($q = trim((string) $_REQUEST['member_name']))) {
                $condition['member_name'] = $q;
            }
            if (strlen($q = trim((string) $_REQUEST['commend_goods_name']))) {
                $condition['commend_goods_name'] = array('like', '%' . $q . '%');
            }
            if (strlen($q = trim((string) $_REQUEST['microshop_commend']))) {
                $condition['microshop_commend'] = (int) $q;
            }

            $sdate = (int) strtotime($_GET['sdate']);
            $edate = (int) strtotime($_GET['edate']);
            if ($sdate > 0 || $edate > 0) {
                $condition['commend_time'] = array(
                    'time',
                    array($sdate, $edate, ),
                );
            }

        } else {
            if (strlen($q = trim($_REQUEST['query'])) > 0) {
                switch ($_REQUEST['qtype']) {
                    case 'commend_id':
                        $condition['commend_id'] = (int) $q;
                        break;
                    case 'member_name':
                        $condition['member_name'] = $q;
                        break;
                    case 'commend_goods_name':
                        $condition['commend_goods_name'] = array('like', '%' . $q . '%');
                        break;
                }
            }
        }
        
        //$_POST['rp'] = 1;

        $model_microshop_goods = Model('micro_goods');
        $field = 'micro_goods.*,member.member_name,member.member_avatar';
        $list = (array) $model_microshop_goods->getListWithUserInfo($condition, $_REQUEST['rp'],
            'commend_time desc', $field);

        
        $data = array();
        $data['now_page'] = $model_microshop_goods->shownowpage();
        $data['total_num'] = $model_microshop_goods->gettotalnum();

        //var_dump($data);

        foreach ($list as $val) {
            $o = '<a class="btn red confirm-del-on-click" href="' . $GLOBALS['_PAGE_URL'] . '&c=Goods&a=goods_drop&commend_id=' .
                $val['commend_id'] .
                '"><i class="fa fa-trash-o"></i>删除</a>';

            $o .= '<span class="btn"><em><i class="fa fa-cog"></i>设置<i class="arrow"></i></em><ul>';

            if ($val['microshop_commend'] == '1') {
                $o .= '<li><a href="javascript:;" data-ie-column="microshop_commend" data-ie-value="0">取消推荐</a></li>';
            } else {
                $o .= '<li><a href="javascript:;" data-ie-column="microshop_commend" data-ie-value="1">推荐商品</a></li>';
            }

            $o .= '<li><a target="_blank" href="' .
                    MICROSHOP_SITE_URL . '&c=Goods&a=detail&goods_id=' .
                    $val['commend_id'] .
                    '">查看商品</a></li>';

            $o .= '</ul></span>';

            $i = array();
            $i['operation'] = $o;
            $i['commend_id'] = $val['commend_id'];

            $i['microshop_sort'] = '<span class="editable" title="可编辑" style="width:50px;" data-live-inline-edit="microshop_sort">' .
                $val['microshop_sort'] . '</span>';

            $i['commend_state'] = $val['microshop_commend'] == 1
                ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>'
                : '<span class="no"><i class="fa fa-ban"></i>否</span>';

            $i['member_name'] = '<a target="_blank" href="' .
                MICROSHOP_SITE_URL . '&c=Home&member_id='.$val['commend_member_id'] .
                '">' .
                $val['member_name'] .
                '</a>';

            $img = cthumb($val['commend_goods_image'], 240, $val['commend_goods_store_id']);
            $i['commend_goods_image'] = <<<EOB
<a href="javascript:;" class="pic-thumb-tip" onMouseOut="toolTip()" onMouseOver="toolTip('<img src=\'{$img}\'>')">
<i class='fa fa-picture-o'></i></a>
EOB;

            $i['commend_goods_name'] = $val['commend_goods_name'];

            $i['commend_message'] = $val['commend_message'];
            $i['commend_time_text'] = date('Y-m-d', $val['commend_time']);

            //var_dump($val);
            
            $data['list'][$val['commend_id']] = $i;
        }

//         var_dump($data);
//         exit;
        
        echo $this->flexigridXML($data);
        exit;
    }

    /**
     * 随心看删除
     */
    public function goods_drop()
    {
        $model = Model('micro_goods');
        $condition = array();
        $condition['commend_id'] = array('in',trim($_REQUEST['commend_id']));

        //删除随心看图片
        $list = $model->getList($condition);
        if(!empty($list)) {
            foreach ($list as $info) {
                //计数
                $model_micro_member_info = Model('micro_member_info');
                $model_micro_member_info->updateMemberGoodsCount($info['commend_member_id'],'-');
            }
        }
        $result = $model->drop($condition);
        if($result) {
            showMessage(Language::get('spd_common_del_succ'),'');
        } else {
            showMessage(Language::get('spd_common_del_fail'),'','','error');
        }
    }

    /**
     * 更新微商城随心看排序
     */
    public function goods_sort_update() {
        if(intval($_GET['id']) <= 0) {
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('param_error')));
            die;
        }
        $new_sort = intval($_GET['value']);
        if ($new_sort > 255){
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('microshop_sort_error')));
            die;
        } else {
            $model_class = Model('micro_goods');
            $result = $model_class->modify(array('microshop_sort'=>$new_sort),array('commend_id'=>$_GET['id']));
            if($result) {
                echo json_encode(array('result'=>TRUE,'message'=>'spd_common_op_succ'));
                die;
            } else {
                echo json_encode(array('result'=>FALSE,'message'=>Language::get('spd_common_op_fail')));
                die;
            }
        }
    }
    /**
     * ajax操作
     */
    public function ajax(){
        //随心看推荐
        if($_GET['branch'] == 'goods_commend') {
            if(intval($_GET['id']) > 0) {
                $model= Model('micro_goods');
                $condition['commend_id'] = intval($_GET['id']);
                $update[$_GET['column']] = trim($_GET['value']);
                $model->modify($update,$condition);
                echo 'true';die;
            } else {
                echo 'false';die;
            }
        }
    }
}
