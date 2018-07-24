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
class ZhifunewAction extends BaseAction
{

	 function _initialize()
    {	
		parent::_initialize();
		$this->category        = D('Category');
		$this->product         = M('product');
		$this->page            = M('page');
        $this->slide_data      = M('slide_data');
        $this->article         = M('article');
        $this->link            = M('link');
        $this->message         = M('message');
		$this->order         = M('order');
        $this->mendianbiaozhu  = M('Mendianbiaozhu','gz_',otherdbs()); 
    }
    public function is_login(){
        $loginid=$this->loginid();
         if($loginid){          
            return $loginid;
        }else{
            Header("Location:/index.php/Home/Register/login");
            return false;
        } 
    }
    function confll(){
        return $this->Config = F('Config_cn');
        
    }
    public function index()
    {
    	$allcof=$this->confll();   //获取积分比例 邮费
    	// print_r($allcof);die;
    	$fkfs=$_GET['fkfs']+0;
    	// print_r($fkfs);die;
		//$je=$_GET['je']+0;
		$uid=$this->is_login();
		if(!$uid || !$fkfs) exit;
		
		$userinfo=M('Huiyuan','gz_',otherdbs())->where(array('id'=>$uid))->find();
		$this->assign('userinfoall',$userinfo);
		//dump($userinfo);exit;
		
		//$sdingdan=$uid;
		
		$a['userid']=$uid;
		$a['pay_status']='1';
		$scartlist=$this->order->where($a)->select();
		//dump($scartlist);exit;
		
		foreach($scartlist as $v){
			if($spid) $spid.=','.$v['shipping_id'];
			else $spid=$v['shipping_id'];
			
			if($scartid) $scartid.=','.$v['id'];
			else $scartid=$v['id'];
			
		}
		$sw['id']=array('in',$spid);
		
		$slist=M('product')->field('id,title,url,price,cprice,thumb,jifen')->where($sw)->select();
		
		foreach($scartlist as &$v){
			foreach($slist as $sv){
				if($sv['id']==$v['shipping_id']){
					$v['price']=$sv['price'];
					$v['cprice']=$sv['cprice'];
					$v['title']=$sv['title'];
					$v['url']=$sv['url'];
					$v['thumb']=$sv['thumb'];
					if($sv['thumb']) $scartlistthumb=$sv['thumb'];
					$sjifen+=$v['number']*$sv['jifen'];//送积分
					$jifen=$v['jifen'];
					$zje=$zje+$sv['price']*$v['number'];
					$znum=$znum+$v['number'];
					
					$sinfo=$sinfo.$sv['title'].' '.$v['yanse'].$v['chicun'].' 单价'.$sv['price'].' 数量'.$v['number'].' 小计'.$sv['price']*$v['number'].' , ';
				}
			}
		}
			//dump($scartlist);exit;
			//echo $zje;exit;
			// $sjifen=0;
			// $jifen=0;
		$jifendk=$allcof['jifendk'];                  //积分比例
		$youfei=$allcof['youfei'];                  // 邮费
		// 获取积分退换到的钱
		if($jifen){
			$jifenq=$jifen/$jifendk;
		}else{
			$jifenq=0;
		}
		$zje=$zje+$youfei-$jifenq;
		if($jifen){$sjifen=0;}
		if($fkfs==3){
			//余额支付
			$yue=$userinfo['yue'];
			if($yue<$zje){
				$this->display('./GZphp/Tpl/Home/Default/SubmitOrderok_yue.html');
			}else{
				//更新会员余额
				$yuew['id']=$uid;
				$yues['yue']=array('exp','yue-'.$zje);
				//更新积分 增加
				if($sjifen){
					//增加
					$yues['jifen']=array('exp','jifen+'.$sjifen);
				}else if($jifen){
					$yues['jifen']=array('exp','jifen-'.$jifen);
				}
				$yuemm=M('huiyuan')->where($yuew)->save($yues);
				
				//添加会员消费记录
				$hyxfjla['uid']=$uid;
				$hyxfjla['je']=$zje;
				$hyxfjla['fkfs']=3;
				$hyxfjla['ctime']=time();
				$hyxfjla['info']=1;
				$hyxfjla['gtype']=1;
				$hyxfjla['ctype']='m';
				$hyxfjla['ids']=$scartid;
				$oidsss=time().rand(111111,999999);
				$hyxfjla['oid']=$oidsss;
				$hyxfjlmm=M('hyxfjl')->add($hyxfjla);
				
				//更新订单
				$dingdanw['userid']=$uid;
				$dingdanw['id']=array('in',$scartid);
				//$dingdanw['zje']=$zje;
				$dingdans['pay_status']=2;
				$dingdans['utime']=time();
				$dingdans['oid']=$oidsss;
				//$dingdans['sn']=time().rand(111111,999999);
				//dump($dingdanw);exit;
				
				$dingdanmm=$this->order->where($dingdanw)->save($dingdans);
				
					//更新积分记录
				$jifenjilua['uid']=$uid;
				$jifenjilua['laiyuan']=$scartid;
				$jifenjilua['ctime']=time();
				$jifenjilua['jifen']=$jifen;
				$jifenjilua['jtype']=2;//增加 1增加 2使用
				if($sjifen){
					$jifenjilua['jifen']=$sjifen;
					$jifenjilua['jtype']=1;
				}
				$jifenjilumm=M('jifenjilu')->add($jifenjilua);
				
				header('Location:/index.php/Home/Member/orderlist.html');
				exit;
			}
		}else if($fkfs==2){
				//微信支付
				$url = APP_DEBUGwxxxxx."wxtest/example/native.php";
				$post_data = array (
					"Body" => "广羽人充值",
					"Attach"=>'1#'.$uid.'#'.$scartid.'#m#'.$sjifen.'#'.$jifen,
					'Total_fee'=>$zje,
					'Product_id'=>$scartid
				);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				// post数据
				curl_setopt($ch, CURLOPT_POST, 1);
				// post的变量
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
				$output = curl_exec($ch);
				curl_close($ch);
				//打印获得的数据
				//print_r($output);
				$wximg=APP_DEBUGwxxxxx.'wxtest/example/qrcode.php?data='.$output;
				$this->assign('wximg',$wximg);
				$this->assign('zje',$zje);
				$this->assign('fkfs',$fkfs);
				$this->display('./GZphp/Tpl/Home/Default/SubmitOrderok_wx.html');
			}else{
				$this->display('./GZphp/Tpl/Home/Default/SubmitOrderok_zfb.html');
			}
    }
}
?>