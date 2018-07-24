<?php
/**
 * 
 * IndexAction.class.php (前台首页)
 *
 * @package      	GZPHP
 * @author          wen QQ:52009619 <admin@resonance.com.cn>
 * @copyright     	Copyright (c) 2008-2011  (http://www.resonance.com.cn)
 * @license         http://www.resonance.com.cn/license.txt
 * @version        	GzPHP企业网站管理系统 v2.1 2011-03-01 resonance.com.cn $
 */
if(!defined("GZPHP")) exit("Access Denied"); 
class RegisterappAction extends BaseAction
{
    
    public function index(){
		$uid=$this->loginid();
        if(!$uid){
            Header("Location:/index.php/Home/Register/login");
            exit;
        }
		$w['id']=$uid;
		$userinfoall=M('Huiyuan','gz_',otherdbs())->where($w)->find();
		//dump($userinfoall);
		$this->assign('userinfoall',$userinfoall);
        $this->display();
    }
	
	//随机字符串
	public function get_passwordssss( $length = 8 ){
		$str = substr(md5(time()), 0, 6);
		return $str;
	}
}
?>