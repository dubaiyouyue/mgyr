<?php

/**
 * 
 * AreaAction.class.php (ajax 获取地址)
 *
 * @package      	GZPHP
 * @author          wen QQ:52009619 <admin@resonance.com.cn>
 * @copyright     	Copyright (c) 2008-2011  (http://www.resonance.com.cn)
 * @license         http://www.resonance.com.cn/license.txt
 * @version        	GzPHP企业网站管理系统 v2.1 2011-03-01 resonance.com.cn $
 */
if (!defined("GZPHP"))
    exit("Access Denied");

class UnbindingqqAction extends BaseAction {

    public function index() {
		$uid=$this->loginid();
        if(!$uid){
            Header("Location:/index.php/Home/Register/login");
            exit;
        }
		$w['id']=$uid;
		$s['qquid']='';
		$s['qqtx']='';
		$s['qqname']='';
		$c=M('Huiyuan','gz_',otherdbs())->where($w)->save($s);
		header('Location:/index.php/Home/Registerapp/index.html');
		exit;
	}
	
}

?>