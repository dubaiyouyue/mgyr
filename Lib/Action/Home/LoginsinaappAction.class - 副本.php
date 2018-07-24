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
class LoginsinaappAction extends BaseAction
{
    
    public function index(){
        $hurl='http://guangyuren.com/libweibo-master/';
		$appqqurl=$_GET['appqqurl'];
		if($appqqurl=='qq'){
			$hurl='http://guangyuren.com/Connect/example/oauth/index.php';
		}
        $now_token_sina=$this->get_passwordssss(18);
		$now_token_sina=$now_token_sina.md5(time().rand(1,9));
		$hurl=$hurl.'?loginapp=m&loginapptoken='.$now_token_sina;
		$_SESSION['login_app_token']=$now_token_sina;
		header('Location:'.$hurl);
		exit;
    }
	public function sinahave(){
		$w['msinatoken']=$_SESSION['login_app_token'];
		$fuid=M('Huiyuan','gz_',otherdbs())->where($w)->find();
		$uid=$fuid['id'];
		if($uid){
			//登录成功
			$ldata['loginsalt']=md5(md5($this->get_passwordssss(18)).$uid);
			$lw['id']=$uid;
			$lok=M('Huiyuan','gz_',otherdbs())->where($lw)->limit(1)->save($ldata);
		
			setcookie("user", $uid, time()+3600000,'/');
			setcookie("lsalt", $ldata['loginsalt'], time()+3600000,'/');
				header('Location:/index.php/Home/Member/index.html');
				exit();
		}
	}
	public function sinano(){
		$appid=$_GET['appid']+0;
		$w['id']=$appid;
		if($appid){
			$f=M('loginapptoken','gz_',otherdbs())->where($w)->find();
			if($f['token']==$_SESSION['login_app_token']){
				if($f['sinauid']){
					$_SESSION['sinauid']=$f['sinauid'];
					$_SESSION['sinatx']=$f['sinatx'];
					$_SESSION['sinaname']=$f['sinaname'];
				}else if($f['qquid']){
					$_SESSION['qquid']=$f['qquid'];
					$_SESSION['qqname']=$f['qqname'];
					$_SESSION['qqtx']=$f['qqtx'];
				}
				header('Location:/index.php/Home/Register/bdsian');
				exit();
			}
		}
	}
	//随机字符串
	public function get_passwordssss( $length = 8 ){
		$str = substr(md5(time()), 0, 6);
		return $str;
	}
}
?>