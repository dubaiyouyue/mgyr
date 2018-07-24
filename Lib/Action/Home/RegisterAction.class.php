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
class RegisterAction extends BaseAction{

	 function _initialize(){	
		parent::_initialize();
        $this->huiyuanpc   = M('Huiyuan','gz_',otherdbs()); 
    }
 
    public function login() {

        $this->display();
    }
    public function register(){
        
        $this->display();
    }
    public function regggs(){

        $data['tel']=$_POST['tel']+0;
        $data['email']=$_POST['email'];
		
			$code=md5($_POST['code']);
			if($code!=$_SESSION['verify'] || !$_SESSION['verify']){
				$_SESSION['verify']='';
				exit('e1'); //验证码错误
			}
		
		
        if($this->huiyuanpc->where(array('tel'=>$data['tel']))->getField('tel')){
           exit('a1');

        }
        if($this->huiyuanpc->where(array('email'=>$data['email']))->getField('email')){
           exit('a2');
        }
			
		//start
		$fsssyem=$_POST['smssendok'];
		if($fsssyem=='ok'){
			//发送短信手机号
			$_SESSION['dxyzmtelzhuimim']=0;
			$_SESSION['verifysms']=$_SESSION['verify'];
			$_SESSION['dxyzmtel']=$data['tel'];//2016.11.18
			$_SESSION['dxyzmssss']=$this->GetRandStrssss(6);
			exit('smssend');
		}
		//end
		
		if($_POST['namecxhqdxyzmidd']!=$_SESSION['dxyzmssss'] || !$_SESSION['dxyzmssss']) exit('e4'); //短信验证码错误
		
        $data['createtime'] = time();
        $data['salt'] = $this->get_passwordssss(8);
        $data['lsalt']=$this->get_passwordssss(18);
        $data['pass'] = md5(md5($_POST['pass']).$data['salt']);
		
				if($_SESSION['login_app_token']){
					if($_SESSION['sinauid']){
						$data['sinauid']=$_SESSION['sinauid'];
						$data['sinatx']=$_SESSION['sinatx'];
						$data['sinaname']=$_SESSION['sinaname'];
					}else if($_SESSION['qquid']){
						$data['qquid']=$_SESSION['qquid'];
						$data['qqname']=$_SESSION['qqname'];
						$data['qqtx']=$_SESSION['qqtx'];
					}
				}
                $data['loginsalt']=md5(md5($this->get_passwordssss(18)));
                
                
            

		
        $rc=$this->huiyuanpc->add($data);
		if($rc){
			setcookie("user", $rc, time()+3600000,'/');
			setcookie("lsalt", $data['loginsalt'], time()+3600000,'/');
			exit('ok');
			
		}
		//header('Location:/index.php/Home/Member/index.html');
		//exit;
    }
    public function is_login(){

        $tel=($_POST['tel'])+0;
        $pass=$_POST['pass'];
       
        if($tel && $pass){

            $r=$this->huiyuanpc->where(array('tel'=>$tel))->find();
            if(!$r) exit('e2'); //手机号没有注册
            $salt=$r['salt'];
            $rpass=md5(md5($pass).$salt);
            if($r['pass']==$rpass){
                //登录成功
				
				if($_SESSION['login_app_token']){
					if($_SESSION['sinauid']){
						$ldata['sinauid']=$_SESSION['sinauid'];
						$ldata['sinatx']=$_SESSION['sinatx'];
						$ldata['sinaname']=$_SESSION['sinaname'];
					}else if($_SESSION['qquid']){
						$ldata['qquid']=$_SESSION['qquid'];
						$ldata['qqname']=$_SESSION['qqname'];
						$ldata['qqtx']=$_SESSION['qqtx'];
					}
				}

				
				
				
                $ldata['loginsalt']=md5(md5($this->get_passwordssss(18)).$r['id']);
                $lw['id']=$r['id'];
                $this->huiyuanpc->where($lw)->limit(1)->save($ldata);
                $_SESSION['order_ok_app_uid']=$r['id'];
                setcookie("user", $r['id'], time()+3600000,'/');
                setcookie("lsalt", $ldata['loginsalt'], time()+3600000,'/');
                    //header('Location:/index.php/Home/PersonalProfile/');
                    exit('lok');
            }else{
                exit('e3');
            }
        }
        exit;
    }
    function get_passwordssss( $length = 8 ){
        $str = substr(md5(time()), 0, 6);
        return $str;
    }
    //忘记密码
    function wangjpass(){
        $this->display();
    }
    //验证帐号
    function yanzhenghzhao(){
        $get_zhanghao=$_POST['tels']+0;
        if($this->huiyuanpc->where(array('tel'=>$get_zhanghao))->getField('id')){
            $data['yeszh']=1;
        }else{
            $data['yeszh']=2;
        }
        $this->ajaxReturn($data);
    }
    // 验证密码
    function passyanzhen(){
        $get_tel=$_GET['zhh']+0;
        $get_pass=$_POST['mima']+0;
        $get_zhao=$_POST['zhao']+0;
        if($get_pass&&$get_zhao){
            $r=$this->huiyuanpc->where(array('tel'=>$get_zhao))->find();
            $salt=$r['salt'];
            $ylpass=$r['pass'];
            $rpass=md5(md5($get_pass).$salt);
            if($ylpass==$rpass){
                $data['psyz']=1;
            }else{

                $data['psyz']=2;
            }
         $this->ajaxReturn($data);   
        }
        $this->assign('zhanghaos',$get_tel);
        $this->display();
    }
	
	//手机找回密码
	function wangjpasschh(){
		$tel=$_POST['tel'];
		
			$code=md5($_POST['code']);
			if($code!=$_SESSION['verify'] || !$_SESSION['verify']){
				$_SESSION['verify']='';
				exit('e1'); //验证码错误
			}
            $r=$this->huiyuanpc->where(array('tel'=>$tel))->find();
            if(!$r) exit('e2'); //手机号没有注册
			
			$get_pass=$this->get_passwordssss(8);
			
			$_SESSION['dxyzmtel']=$tel;
			$_SESSION['dxyzmssss']=$get_pass;
			$_SESSION['verifysms']=$_SESSION['verify'];
			$_SESSION['dxyzmtelzhuimim']=1;
			
            $xgmm['salt'] = $this->get_passwordssss(8);
            $xgmm['pass'] = md5(md5($get_pass).$xgmm['salt']); 
			$this->huiyuanpc->where(array('tel'=>$tel))->save($xgmm);
			
	}
	
    //修改密码
    function eidtpss(){
        $get_tel=$_GET['zhh']+0;
        $get_pass=$_POST['mima']+0;
        $get_zhao=$_POST['zhao']+0;
        if($get_pass&&$get_zhao){
            $xgmm['salt'] = $this->get_passwordssss(8);
            $xgmm['pass'] = md5(md5($get_pass).$xgmm['salt']); 
			
				if($_SESSION['login_app_token']){
					if($_SESSION['sinauid']){
						$xgmm['sinauid']=$_SESSION['sinauid'];
						$xgmm['sinatx']=$_SESSION['sinatx'];
						$xgmm['sinaname']=$_SESSION['sinaname'];
					}else if($_SESSION['qquid']){
						$xgmm['qquid']=$_SESSION['qquid'];
						$xgmm['qqname']=$_SESSION['qqname'];
						$xgmm['qqtx']=$_SESSION['qqtx'];
					}
				}
			
            if($this->huiyuanpc->where(array('tel'=>$get_zhao))->save($xgmm)){
                $data['xg']=1;
            }else{
                $data['xg']=2;
            }
            $this->ajaxReturn($data);   
        }
        $this->assign('zhanghaos',$get_tel);
        $this->display();
    }
	//绑定新浪微博
	function bdsian(){
		$this->display();
	}
	function bdqq(){
		$this->display();
	}
	function GetRandStrssss($len){ 
		$chars = array("0", "1", "2","3", "4", "5", "6", "7", "8", "9"); 
		$charsLen = count($chars) - 1; 
		shuffle($chars);   
		$output = ""; 
		for ($i=0; $i<$len; $i++) 
		{ 
			$output .= $chars[mt_rand(0, $charsLen)]; 
		}  
		return $output;  
	}
	function eidtmm(){
		$this->display();
	}
	// 20170118 修改密码
	function ajaxmm(){
		$loginid=$this->loginid();
		$get_oldpass=$_POST['oldpass']+0;
		$get_newpass=$_POST['newpass']+0;
        $get_newpass2=$_POST['newpass2']+0;
        if($get_newpass2!=$get_newpass){$data['xg']=4; $this->ajaxReturn($data); return false;}
        if($loginid){
            $r=$this->huiyuanpc->where(array('id'=>$loginid))->find();
            $salt=$r['salt'];
            $ylpass=$r['pass'];
            $rpass=md5(md5($get_oldpass).$salt);
            if($ylpass==$rpass){
	             $xgmm['salt'] = $this->get_passwordssss(8);
            	$xgmm['pass'] = md5(md5($get_newpass).$xgmm['salt']); 
			
				if($_SESSION['login_app_token']){
					if($_SESSION['sinauid']){
						$xgmm['sinauid']=$_SESSION['sinauid'];
						$xgmm['sinatx']=$_SESSION['sinatx'];
						$xgmm['sinaname']=$_SESSION['sinaname'];
					}else if($_SESSION['qquid']){
						$xgmm['qquid']=$_SESSION['qquid'];
						$xgmm['qqname']=$_SESSION['qqname'];
						$xgmm['qqtx']=$_SESSION['qqtx'];
					}
				}
            if($this->huiyuanpc->where(array('id'=>$loginid))->save($xgmm)){
                $data['xg']=1;
            }else{
                $data['xg']=2;
            }
        }else{
        	$data['xg']=5;   //旧密码不对
        }
          
        }else{

        	$data['xg']=3;
        }

         $this->ajaxReturn($data);   
	} 
}
?>