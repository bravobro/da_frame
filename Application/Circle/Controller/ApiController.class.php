<?php
/**
 * 圈子api
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Circle\Controller;
use Circle\Controller\BaseCircleController;
use Common\Lib\Language;
use Common\Lib\Model;



class ApiController extends BaseCircleController {

    private $data_type = 'html';

    public function __construct() {
        parent::__construct();
        if(!empty($_GET['data_type']) && $_GET['data_type'] === 'json') {
            $this->data_type = 'json';
        }
    }

    // 推荐话题
    public function get_theme_list() {
        $result = '';
        $data_count = intval($_GET['data_count']);
        if($data_count <= 0) {
            $data_count = 2;
        }

        $model = Model();
        $theme_list = $model->table('circle_theme,circle')
        ->field('circle_theme.*, (circle_theme.is_recommend*rand()*10000 + circle_theme.has_affix*rand()) as `rand`')
        ->join('left')
        ->on("circle_theme.circle_id = circle.circle_id")
        
        ->where(array('circle.circle_status'=>1, 'circle.is_closed'=>0))
        ->where(array('circle_theme.has_affix'=>1))
        
        ->order('`rand` desc')
        ->limit($data_count)
        ->select();

        if(!empty($theme_list)){
            $theme_list = array_under_reset($theme_list, 'theme_id'); $themeid_array = array_keys($theme_list);

            // 附件
            $affix_list = $model->table('circle_affix')->where(array('theme_id'=>array('in', $themeid_array), 'affix_type'=>1))->group('theme_id')->select();
            if(!empty($affix_list)) $affix_list = array_under_reset($affix_list, 'theme_id');


            foreach ($theme_list as $key=>$val){
                if(isset($affix_list[$val['theme_id']])) $theme_list[$key]['affix'] = themeImageUrl($affix_list[$val['theme_id']]['affix_filethumb']);
            }
        }

        if($this->data_type === 'json') {
            $result = json_encode($theme_list);
        } else {
            $this->assign('theme_list', $theme_list);
            ob_start();
            $this->render('api_theme_list', 'null_layout');
            $result = ob_get_clean();
        }

        $this->return_result($result);
    }

    // 人气主题
    public function get_reply_themelist() {
        $result = '';
        $data_count = intval($_GET['data_count']);
        if($data_count <= 0) {
            $data_count = 3;
        }

        $model = Model();
        $reply_themelist = $model->table('circle_theme')->where(array('is_closed'=>0))->order('theme_commentcount desc')->limit($data_count)->select();

        if($this->data_type === 'json') {
            $result = json_encode($reply_themelist);
        } else {
            $this->assign('reply_themelist', $reply_themelist);
            ob_start();
            $this->render('api_reply_themelist', 'null_layout');
            $result = ob_get_clean();
        }

        $this->return_result($result);
    }

    // 优秀成员
    public function get_more_membertheme(){
        $result = '';
        $data_count = intval($_GET['data_count']);
        if($data_count <= 0) {
            $data_count = 4;
        }

        $model = Model();
        $more_membertheme = $model->table('circle_member,circle_theme')->field('min(circle_member.member_id) member_id,min(circle_member.member_name) member_name,min(circle_theme.circle_id) circle_id,min(circle_theme.theme_id) theme_id,min(circle_theme.theme_name) theme_name, min(circle_member.is_recommend*10000*rand()+(circle_member.cm_thcount)/10000) rand')
            ->order('rand desc')
            ->join('inner')->on('circle_member.member_id = circle_theme.member_id and circle_member.circle_id = circle_theme.circle_id')
            ->group('circle_member.member_id,circle_member.circle_id')->limit($data_count)->select();

        if($this->data_type === 'json') {
            $result = json_encode($more_membertheme);
        } else {
            $this->assign("more_membertheme", $more_membertheme);
            ob_start();
            $this->render('api_more_membertheme', 'null_layout');
            $result = ob_get_clean();

            $this->return_result($result);
        }
    }

    private function return_result($result) {
        $result = str_replace("\n", "", $result);
        $result = str_replace("\r", "", $result);
        echo empty($_GET['callback']) ? $result : $_GET['callback']."('".$result."')";
    }

}
