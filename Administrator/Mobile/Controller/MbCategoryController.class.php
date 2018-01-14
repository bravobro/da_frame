<?php
/**
 * 手机端分类图片设置
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Mobile\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Cache;
use Common\Lib\Model;
use Common\Lib\UploadFile;


class MbCategoryController extends SystemController{
    public function __construct(){
        parent::__construct();
        Language::read('mobile', 'Home');
    }

    public function index() {
        $this->mb_category_list();
    }
    /**
     *
     */
    public function mb_category_list(){
        $lang   = Language::getLangContent();
        $model_link = Model('mb_category');
        /**
         * 删除
         */
        if ($_POST['form_submit'] == 'ok'){
            if (is_array($_POST['del_id']) && !empty($_POST['del_id'])){
                foreach ($_POST['del_id'] as $k => $v){
                    /**
                     * 删除图片
                     */
                    $v = intval($v);
                    $tmp = $model_link->getOneLink($v);
                    if (!empty($tmp['gc_thumb'])){
                        @unlink(BASE_UPLOAD_PATH . DS.ATTACH_MOBILE.'/category/'.$tmp['gc_thumb']);
                    }
                    unset($tmp);
                    $model_link->del($v);
                }
                showMessage($lang['link_index_del_succ']);
            }else {
                showMessage($lang['link_index_choose_del']);
            }
        }

        $link_list = $model_link->getLinkList(array());
        /**
         * 整理图片链接
         */
        if (is_array($link_list)){
            foreach ($link_list as $k => $v){
                if (!empty($v['gc_thumb'])){
                    $link_list[$k]['gc_thumb'] = UPLOAD_SITE_URL.'/'.ATTACH_MOBILE.'/category'.'/'.$v['gc_thumb'];
                }
            }
        }

        /**
         * 商品分类
         */
        $goods_class = Model('goods_class')->getGoodsClassForCacheModel();
        $this->assign('goods_class',$goods_class);

        $this->assign('link_list',$link_list);
        $this->setDirquna('mobile');
$this->render('mb_category.list');
    }

    /**
     * 删除
     */
    public function mb_category_del(){
        $lang   = Language::getLangContent();
        if (intval($_GET['gc_id']) > 0){
            $model_link = Model('mb_category');

            /**
             * 删除图片
             */
            $tmp = $model_link->getOneLink(intval($_GET['gc_id']));
            if (!empty($tmp['gc_thumb'])){
                @unlink(BASE_UPLOAD_PATH.DS.ATTACH_MOBILE.'/category/'.$tmp['gc_thumb']);
            }
            $model_link->del($tmp['gc_id']);
            showMessage($lang['link_index_del_succ'],$GLOBALS['_PAGE_URL'] . '&c=MbCategory&a=mb_category_list');
        }else {
            showMessage($lang['link_index_choose_del'],$GLOBALS['_PAGE_URL'] . '&c=MbCategory&a=mb_category_list');
        }
    }

    /**
     * 添加
     */
    public function mb_category_add(){

        $lang   = Language::getLangContent();
        $model_link = Model('mb_category');

        if ($_POST['form_submit'] == 'ok'){
            $category = $model_link->getOneLink(intval($_POST['link_category']));
            if (!empty($category)){
                showMessage($lang['link_add_category_exist']);
            }

            /**
             * 上传图片
             */
            if ($_FILES['link_pic']['name'] != ''){
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_MOBILE.'/category');

                $result = $upload->upfile('link_pic');
                if ($result){
                    $_POST['link_pic'] = $upload->file_name;
                }else {
                    showMessage($upload->error);
                }
            }

            $insert_array = array();
            $insert_array['gc_id'] = trim($_POST['link_category']);
            $insert_array['gc_thumb'] = trim($_POST['link_pic']);

            $result = $model_link->add($insert_array);
            if ($result){
                $url = array(
                    array(
                        'url'=>$GLOBALS['_PAGE_URL'] . '&c=MbCategory&a=mb_category_add',
                        'msg'=>$lang['link_add_again'],
                    ),
                    array(
                        'url'=>$GLOBALS['_PAGE_URL'] . '&c=MbCategory&a=mb_category_list',
                        'msg'=>$lang['link_add_back_to_list'],
                    )
                );
                showMessage($lang['link_add_succ'],$url);
            }else {
                showMessage($lang['link_add_fail']);
            }
        }

        /**
         * 商品分类
         */
        $goods_class = Model('goods_class')->getGoodsClassForCacheModel();
        $this->assign('goods_class',$goods_class);

        $this->setDirquna('mobile');
$this->render('mb_category.add');
    }

    /**
     * 编辑
     */
    public function mb_category_edit(){
        $lang   = Language::getLangContent();
        $model_link = Model('mb_category');
        if ($_POST['form_submit'] == 'ok'){

            /**
             * 上传图片
             */
            if ($_FILES['link_pic']['name'] != ''){
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_MOBILE.'/category');

                $result = $upload->upfile('link_pic');
                if ($result){
                    $_POST['gc_thumb'] = $upload->file_name;
                }else {
                    showMessage($upload->error);
                }
            }
            $link_array = $model_link->getOneLink(intval($_POST['gc_id']));
            $update_array = array();
            $update_array['gc_id'] = intval($_POST['gc_id']);
            if ($_POST['gc_thumb']){
                $update_array['gc_thumb'] = $_POST['gc_thumb'];
            }
            $result = $model_link->updates($update_array);
            if ($result){
                /**
                 * 删除图片
                 */
                if (!empty($_POST['gc_thumb']) && !empty($link_array['gc_thumb'])){
                    @unlink(BASE_UPLOAD_PATH.DS.ATTACH_MOBILE.'/category/'.$link_array['gc_thumb']);
                }
                $url = array(
                    array(
                        'url'=>$GLOBALS['_PAGE_URL'] . '&c=MbCategory&a=mb_category_edit&gc_id='.intval($_POST['gc_id']),
                        'msg'=>$lang['link_edit_again']
                    ),
                    array(
                        'url'=>$GLOBALS['_PAGE_URL'] . '&c=MbCategory&a=mb_category_list',
                        'msg'=>$lang['link_add_back_to_list'],
                    )
                );
                showMessage($lang['link_edit_succ'],$url);
            }else {
                showMessage($lang['link_edit_fail']);
            }
        }

        $link_array = $model_link->getOneLink(intval($_GET['gc_id']));
        if (empty($link_array)){
            showMessage($lang['wrong_argument']);
        }

        /**
         * 商品分类
         */
        $goods_class = Model('goods_class')->getGoodsClassForCacheModel();
        $this->assign('goods_class',$goods_class);

        $this->assign('link_array',$link_array);
        $this->setDirquna('mobile');
$this->render('mb_category.edit');
    }
    /**
     * ajax操作
     */
    public function ajax(){
        switch ($_GET['branch']){
            /**
             * 合作伙伴 排序
             */
            case 'link_sort':
                $model_link = Model('link');
                $update_array = array();
                $update_array[$_GET['column']] = trim($_GET['value']);
                $result = $model_link->where(array('link_id'=>intval($_GET['id'])))->update($update_array);

                echo 'true';exit;
                break;
        }
    }
}
