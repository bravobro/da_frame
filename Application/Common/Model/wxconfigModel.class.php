<?php
/**
 * 微信管理
 *
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Common\Model;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Db;
use Common\Lib\Chat;


class wxconfigModel extends Model{
    public function __construct() {
        parent::__construct('wechat_config');
    }
}