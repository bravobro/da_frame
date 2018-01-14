<?php
/**
 * 圈子分类管理
 *
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Circle\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\UploadFile;
use Common\Lib\Log;
use Common\Lib\Model;


class CircleAdvController extends SystemController {
    public function __construct(){
        parent::__construct();
        Language::read('circle', 'Home');
    }
    public function index() {
        $this->adv_manage();
    }
    /**
     * 圈子幻灯
     */
    public function adv_manage(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $input = array();
            //上传图片
            $upload = new UploadFile();
            $upload->set('default_dir',ATTACH_CIRCLE);
            $upload->set('thumb_ext',   '');
            $upload->set('file_name','1.jpg');
            $upload->set('ifremove',false);
            if (!empty($_FILES['adv_pic1']['name'])){
                $result = $upload->upfile('adv_pic1');
                if (!$result){
                    showMessage($upload->error,'','','error');
                }else{
                    $input[1]['pic'] = $upload->file_name;
                    $input[1]['url'] = $_POST['adv_url1'];
                }
            }elseif ($_POST['old_adv_pic1'] != ''){
                $input[1]['pic'] = $_POST['old_adv_pic1'];
                $input[1]['url'] = $_POST['adv_url1'];
            }

            $upload->set('default_dir',ATTACH_CIRCLE);
            $upload->set('thumb_ext',   '');
            $upload->set('file_name','2.jpg');
            $upload->set('ifremove',false);
            if (!empty($_FILES['adv_pic2']['name'])){
                $result = $upload->upfile('adv_pic2');
                if (!$result){
                    showMessage($upload->error,'','','error');
                }else{
                    $input[2]['pic'] = $upload->file_name;
                    $input[2]['url'] = $_POST['adv_url2'];
                }
            }elseif ($_POST['old_adv_pic2'] != ''){
                $input[2]['pic'] = $_POST['old_adv_pic2'];
                $input[2]['url'] = $_POST['adv_url2'];
            }

            $upload->set('default_dir',ATTACH_CIRCLE);
            $upload->set('thumb_ext', '');
            $upload->set('file_name', '3.jpg');
            $upload->set('ifremove', false);
            if (!empty($_FILES['adv_pic3']['name'])){
                $result = $upload->upfile('adv_pic3');
                if (!$result){
                    showMessage($upload->error,'','','error');
                }else{
                    $input[3]['pic'] = $upload->file_name;
                    $input[3]['url'] = $_POST['adv_url3'];
                }
            }elseif ($_POST['old_adv_pic3'] != ''){
                $input[3]['pic'] = $_POST['old_adv_pic3'];
                $input[3]['url'] = $_POST['adv_url3'];
            }

            $upload->set('default_dir',ATTACH_CIRCLE);
            $upload->set('thumb_ext',   '');
            $upload->set('file_name','4.jpg');
            $upload->set('ifremove',false);
            if (!empty($_FILES['adv_pic4']['name'])){
                $result = $upload->upfile('adv_pic4');
                if (!$result){
                    showMessage($upload->error,'','','error');
                }else{
                    $input[4]['pic'] = $upload->file_name;
                    $input[4]['url'] = $_POST['adv_url4'];
                }
            }elseif ($_POST['old_adv_pic4'] != ''){
                $input[4]['pic'] = $_POST['old_adv_pic4'];
                $input[4]['url'] = $_POST['adv_url4'];
            }

            $update_array = array();
            if (count($input) > 0){
                $update_array['circle_loginpic'] = serialize($input);
            }

            $result = $model_setting->updateSetting($update_array);
            if ($result === true){
                $this->log(L('spd_edit,loginSettings'),1);
                showMessage(L('spd_common_save_succ'));
            }else {
                $this->log(L('spd_edit,loginSettings'),0);
                showMessage(L('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        if ($list_setting['circle_loginpic'] != ''){
            $list = unserialize($list_setting['circle_loginpic']);
        }
        $this->assign('list', $list);
         
$this->setDirquna('circle');
$this->render('circle_adv.setting');
    }
}
