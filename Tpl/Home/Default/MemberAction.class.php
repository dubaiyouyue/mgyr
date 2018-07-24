<?php
/**
 * 
 * IndexAction.class.php (前台首页)
 *
 * @package         GZPHP
 * @author          wen QQ:52009619 <admin@resonance.com.cn>
 * @copyright       Copyright (c) 2008-2011  (http://www.resonance.com.cn)
 * @license         http://www.resonance.com.cn/license.txt
 * @version         GzPHP企业网站管理系统 v2.1 2011-03-01 resonance.com.cn $
 */
if(!defined("GZPHP")) exit("Access Denied"); 
class MemberAction extends BaseAction{

     function _initialize(){    
        parent::_initialize();
        $this->cart        = M('cart');
        $this->order       = M('order');
        $this->product     = M('product');
        $this->category    = D('Category');
        $this->article     = M('article');
        $this->huiyuanpc   = M('Huiyuan','gz_',otherdbs());
        $this->collectionpc = M('Collection','gz_',otherdbs()); 
        $this->dizhi       = M('dizhi','gz_',otherdbs()); 
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
    //会员中心
    public function index() {
        $getimg=$this->category->where(array('id'=>204))->getField('image');      
        $this->assign('tupian',$getimg);
        $this->display();
    }
    //个人资料
    public function material() {
        $login=$this->is_login();
        $this->assign('users',$this->huiyuanpc->where(array('id'=>$login))->find());
        $this->display();
    }
    public function xggrzl(){
        $login=$this->is_login();
        if($_POST){
            $datab=array(
                'nicheng'=>$_POST['nicheng'],//昵称
                'name'=>$_POST['name'],//真实姓名
                'sex'=>$_POST['sex'],//性别
                'srn'=>$_POST['srn'],//生日年
                'sry'=>$_POST['sry'],//生日月
                'srr'=>$_POST['srr'],//生日日
                'szds'=>$_POST['szds'],//所在地省
                'szdsq'=>$_POST['szdsq'],//所在地市
                'szdq'=>$_POST['szdq'],//所在地区
                'jxs'=>$_POST['jxs'],//家乡省
                'jxss'=>$_POST['jxss'],//家乡市
                'jxq'=>$_POST['jxq']//家乡区
            );
            print_r($datab);
            if($this->huiyuanpc->where(array('id'=>$login))->save($datab)){
                Header("Location:/index.php/Home/Member/material");
            }else{
                Header("Location:/index.php/Home/Member/xggrzl");
            }   
        }
        $this->assign('grzl',$this->huiyuanpc->where(array('id'=>$login))->find());
        $this->display();
    }
    // 订单查询
    public function orderlist(){
        $login=$this->is_login();
        $condition['userid']=array('eq',$login);
        $order_list=$this->order->where($condition)->order('id desc')->select();
        foreach ($order_list as $key => $value) {
            $order_list[$key]['cxprice']=$this->product->where(array('id'=>$value['shipping_id']))->getField('price');
            $order_list[$key]['yprice']=$this->product->where(array('id'=>$value['shipping_id']))->getField('cprice');
            $order_list[$key]['thumb']=$this->product->where(array('id'=>$value['shipping_id']))->getField('thumb');

        }
        $this->assign('ddlists',$order_list);
        $this->display();
    }
    // 会员积分
    public function score(){
        $login=$this->is_login();
        $users=$this->huiyuanpc->where(array('id'=>$login))->find();
        $getjifen= M('jifenjilu','gz_',otherdbs())->where(array('id'=>$login))->order('id desc')->select(); 
        $this->assign('jifenlsit',$getjifen);
        $this->assign('users',$users);
        $this->display();
    }
    // 购物车
    public function mycart(){
        $login=$this->is_login();
        $condition['userid']    = array('eq',$login);
        $condition['type']  = array('eq',0);
        $get_carts=$this->cart->where($condition)->select();
        foreach ($get_carts as $key => $value) {
            $get_carts[$key]['kcs']=$this->thisku($value['product_id'],$value['cm'],$value['ys'])+$value['number'];
            $get_carts[$key]['gcprice']=$this->product->where(array('id'=>$value['product_id']))->getField('cprice');
            $get_carts[$key]['gprice']=$this->product->where(array('id'=>$value['product_id']))->getField('price');
            $sum += ($get_carts[$key]['gprice'] * $value['number']);
        }
        unset($condition);
        $this->assign('sum',sprintf("%.2f", $sum));
        $this->assign('sumcount',count($get_carts));
        $this->assign('get_carts',$get_carts);
        $this->display();
    }
        //删除购物车的商品
    function del_cart(){
        $get_id=$_POST['get_id'];
        $get_cart=$this->cart->where(array('id'=>$get_id))->find();
        $get_good=$this->product->where(array('id'=>$get_cart['product_id']))->find();
        $kus=$this->thisku($get_cart['product_id'],$get_cart['cm'],$get_cart['ys']);
        $vis=$this->kubd($get_cart['ys'],$get_cart['cm'],$get_good['kc'],$kus,$get_cart['number'],$get_good['id'],1);
        $login=$this->loginid();
        if($login){
            $condition['userid']= array('eq',$login);
            $condition['id']= array('eq',$get_id);
                if($this->cart->where($condition)->delete()&&$vis==1){
                    $date['del']=1;
                }else{
                    $date['del']=2;
                }
            
        }else{
            $date['del']=3;
        }
        
        $this->ajaxReturn($date);
    }
    //结算
    function ajax_js(){
        $this->ordertermpc();
        $login=$this->loginid();
        if($login){
            $get_id=$_POST['get_id'];
            $get_mun=$_POST['get_mun'];
            $str_id=explode(',',$get_id);
            $str_mun=explode(',',$get_mun);
            $sliang=count($str_id);
            $condition['type']=array('eq',1);
            $condition['userid']=array('eq',$login);
            $this->cart->where($condition)->setField('type',0);//过滤数据
             unset($condition);
            for ($i=0; $i <$sliang ; $i++) { 
                $condition['userid']=array('eq',$login);
                $condition['id']=array('eq',$str_id[$i]);
                $data['number']=$str_mun[$i];
                $data['type']=1;
                $get_cartxx=$this->cart->where($condition)->find();
                $get_goodkc=$this->product->where(array('id'=>$get_cartxx['product_id']))->getField('kc');
                $kus=$this->thisku($get_cartxx['product_id'],$get_cartxx['cm'],$get_cartxx['ys']);
                if($get_cartxx['number']>$str_mun[$i]){
                    $thismun=$get_cartxx['number']-$str_mun[$i];
                    $jjtype=1;
                }else{
                    $thismun=$str_mun[$i]-$get_cartxx['number'];
                    $jjtype=2;
                }
                $vis=$this->kubd($get_cartxx['ys'],$get_cartxx['cm'],$get_goodkc,$kus,$thismun,$get_cartxx['product_id'],$jjtype);
                $this->cart->where($condition)->save($data);            
            }
            unset($condition);
           
            $condition['type']=array('eq',1);
            $condition['userid']=array('eq',$login);
            $get_mycat=$this->cart->where($condition)->field('ys,number,product_id,product_name,cm')->select();
            $this->order->where(array('pay_status'=>1,'userid'=>$login))->setField('pay_status',0);//过滤数据
            foreach ($get_mycat as $key => $value) {
                    $datadn['cm']=$value['cm'];
                    $datadn['ys']=$value['ys'];
                    $datadn['number']=$value['number'];
                    $datadn['userid']=$login;
                    $datadn['shipping_id']=$value['product_id'];
                    $datadn['shipping_name']=$value['product_name'];
                    $datadn['pay_status']=1;
                    $datadn['sn']=$this->didanhao();
                    $datadn['add_time']=time();
                    $alldata [] = $datadn;
                    unset($datadn); 
            }
            unset($condition);
            
            if($this->order->addAll($alldata)){
                $condition['type']=array('eq',1);
                $condition['userid']=array('eq',$login);
                $xg['type']=2;    //已经提交到订单
                $this->cart->where($condition)->save($xg);
                $date['apjs']=1;
                            
            }else{
                $date['apjs']=2;
                $date['ss']=$alldata;
            }
            unset($condition); 

        }else{
            $date['apjs']=3;  
        }







        $this->ajaxReturn($date);
    }
    // 确认订单
    function qrdd(){
        $login=$this->is_login();
        $condition['pay_status']=array('eq',1);
        $condition['userid']=array('eq',$login);
        $da_list=$this->order->where($condition)->select();
        unset($condition);
        foreach ($da_list as $key => $value) {

             $da_list[$key]['product_thumb']=$this->product->where(array('id'=>$value['shipping_id']))->getField('thumb');
             $da_list[$key]['gprice']=$this->product->where(array('id'=>$value['shipping_id']))->getField('price');
             $sum += ($da_list[$key]['gprice'] * $value['number']);
        }
        $this->assign('user',$this->huiyuanpc->where(array('id'=>$login))->find());
        $this->assign('da_list',$da_list);
        $ddzz=$this->dizhi->where(array('uid'=>$login,'moren'=>1))->find();
        $ddzz['xx']= $ddzz['sf'].$ddzz['shi'].$ddzz['qy'].$ddzz['xx'];
        $this->assign('ddzz',$ddzz);
        $this->assign('sum',sprintf("%.2f", $sum));
        $this->display();
    }
    //成功提交订单
    function cgtjdd(){
        $get_tj=$_POST['tj'];
        $get_bzxx=$_POST['bzxx']; //备注信息
        $get_zf=$_POST['zf'];    //获取积分
        $login=$this->loginid();
        // $condition['type']=array('eq',1);
        // $condition['userid']=array('eq',$login);
        // $da_list=$this->cart->where($condition)->select();
        // $dadan_ids=$this->cart->where($condition)->getField('product_id',true);
        // unset($condition);
        if($get_tj){
            
            $condition['pay_status']=array('eq',1);
            $condition['userid']=array('eq',$login);
            $appxg['bzxx']=$get_bzxx;
            $appxg['add_time']=time();
            // $appxg['get_zf']=$get_zf;
            if($this->order->where($condition)->save($appxg)){
                $date['is']=1;         
            }else{
                $date['is']=2;
            }
            unset($condition);
            $this->ajaxReturn($date);
        }

       
        $condition['userid']=array('eq',$login);
        $condition['pay_status']=array('eq',1);
        $news_dd_list=$this->order->where($condition)->select();
        unset($condition);
        foreach ($news_dd_list as $k => $va) {
             $news_dd_list[$k]['gprice']=$this->product->where(array('id'=>$va['shipping_id']))->getField('price');
             $sum += ($news_dd_list[$k]['gprice'] * $va['number']);
             $timeqx=$va['add_time'];
        }
        $qxsj=$timeqx+60*30;
        $this->assign('qxsj',$qxsj);
        $this->assign('sum',sprintf("%.2f", $sum));
        $ddzz=$this->dizhi->where(array('uid'=>$login,'moren'=>1))->find();
        $ddzz['xx']= $ddzz['sf'].$ddzz['shi'].$ddzz['qy'].$ddzz['xx'];
        $this->assign('ddzz',$ddzz);
        $this->display();
    }
    //立即购买
    function ljgm(){
        $login=$this->loginid();
        if($login){
            $this->ordertermpc();
            $condition['pay_status']=array('eq',1);
            $condition['userid']=array('eq',$login);
            $this->order->where($condition)->setField('pay_status',0);//过滤数据
            unset($condition);
            $get_good_id=$_POST['good_id']+0;
            $get_good=$this->product->where(array('id'=>$get_good_id))->find();
            $datadn['cm']=$_POST['cm'];
            $datadn['pay_status']=1;
            $datadn['ys']=$_POST['ys'];
            $datadn['number']=$_POST['get_mun']+0;
            $datadn['userid']=$login;
            $datadn['shipping_id']=$get_good_id;
            $datadn['add_time']=time();
            $datadn['shipping_name']=$get_good['title'];
            $datadn['sn']=$this->didanhao();
            // $datadn['product_thumb']=$get_good['thumb'];
            // $datadn['product_price']=$get_good['price'];
            $kus=$this->thisku($get_good_id,$_POST['cm'],$_POST['ys']); //库存
            $vis=$this->kubd($datadn['ys'],$datadn['cm'],$get_good['kc'],$kus,$datadn['number'],$get_good_id); //修改库存
            if($this->order->add($datadn)&&$vis==1){
                $date['gm']=1;
            }else{
                $date['gm']=2;
            }
        }else{
            $date['gm']=3;
        }
        $this->ajaxReturn($date);
    }
    //订单号
    function didanhao(){
        $sn=date('YmdHis',time()).rand(10000,99999);
        if($this->order->where(array('sn'=>$sn))->getField('id')){
            $ddhh=date('YmdHis',time()).rand(10000,99999);
        }else{
               $ddhh=$sn;
            }  
        return $ddhh;
    }
    //订单查询
    function orders_qry(){
        $this->ordertermpc();
        $login=json_decode($this->tologin(),true);
        $condition['userid']=array('eq',$login);
        $order_list=$this->order->where($condition)->select();
        $this->assign('orderlist',$order_list);
        $this->display();
    }
    //支付
    function ajax_zhifuapp(){
        $login=$this->loginid();
        $zfid=$_POST['zfid']+0;
        //进行单个支付时更改pay_status状态 避免出现多个数据 以便实现单个支付
        if($login){
            if($zfid){
                $condition['pay_status']=array('neq',2);
                $condition['userid']=array('eq',$login);
                $this->order->where($condition)->setField('pay_status',0);
                unset($condition);
                    $condition['id']=array('eq',$zfid);
                    if($this->order->where($condition)->setField('pay_status',1)){
                        $date['zf']=1;
                    }else{
                        $date['zf']=2;
                    }
                
                
            }else{
                    $user=$this->huiyuanpc->where(array('id'=>$login))->find();
                    $ddzzwx=$this->dizhi->where(array('uid'=>$login,'moren'=>1))->find();
                    $get_dz=$ddzzwx['sf'].$ddzzwx['shi'].$ddzzwx['qy'].$ddzzwx['xx'];
                    $condition['pay_status']=array('eq',1);
                    $condition['userid']=array('eq',$login);
                    $ordxg['addr']=$get_dz;  //收货地址
                    $get_tel=$ddzzwx['tel']?$ddzzwx['tel']:$user['tel'];
                    $get_name=$ddzzwx['xm']?$ddzzwx['xm']:$user['name'];
                    $ordxg['tel']=$get_tel;   //联系电话
                    $ordxg['shrxm']=$get_name; //收货人姓名
                    $ordxg['pay_time']=time(); //支付时间
                    if($this->order->where($condition)->save($ordxg)){
                        $date['zf']=1;
                    }else{
                        $date['zf']=2;
                    }

            }
        }else{
            $date['zf']=3;
        }
        unset($condition);
        $this->ajaxReturn($date);

    }
    //取消订单
    function quxiaodd(){
        $get_id=$_POST['get_id']+0;
        $login=$this->loginid();
        if($login){
            $condition['id']=array('eq',$get_id);
            $condition['userid']=array('eq',$login);
            $get_order=$this->order->where($condition)->field('id,shipping_id,cm,ys,number,pay_status')->find(); //获取订单信息
            if($get_order['pay_status']!=2){
                $kus=$this->thisku($get_order['shipping_id'],$get_order['cm'],$get_order['ys']); //库存
                $get_kc=$this->product->where(array('id'=>$get_order['shipping_id']))->getField('kc'); //获取对应商品库存信息
                $vis=$this->kubd($get_order['ys'],$get_order['cm'],$get_kc,$kus,$get_order['number'],$get_order['shipping_id'],1); //修改库存
                if($vis&&$this->order->where($condition)->delete()){
                    $date['del']=1;
                }else{
                    $date['del']=2;
                }
                
            }else{
                if($this->order->where($condition)->delete()){
                    $date['del']=1;
                }else{
                    $date['del']=2;
                }
            }
            unset($condition);
        }else{
            $date['del']=3;
        }
        
        $this->ajaxReturn($date);   
    }

    // 充值
    public function recharge(){
        $login=$this->is_login();
        $users=$this->huiyuanpc->where(array('id'=>$login))->find();
        $this->assign('users',$users);
		
		//获取明细
		$hyxfjllist=M('hyxfjl','gz_',otherdbs())->where(array('uid'=>$login))->order('id desc')->select();
		//dump($hyxfjllist);exit;
		$this->assign('hyxfjllist',$hyxfjllist);
        $this->display();
    }
    //充值下一步
    public function rechargenext(){
        $login=$this->is_login();
        $users=$this->huiyuanpc->where(array('id'=>$login))->find();
        $this->assign('users',$users);
        $this->display();
    }
    // 收货地址
    public function myaddress(){
        $login=$this->is_login();
      
        $ddzzwx=$this->dizhi->where(array('uid'=>$login,'moren'=>1))->find();
        if($ddzzwx){
            $ddzzwx['xx']=$ddzzwx['sf'].$ddzzwx['shi'].$ddzzwx['qy'].$ddzzwx['xx'];
            $condition['id']=array('neq',$ddzzwx['id']);
        }
        $condition['uid']=array('eq',$login);
        $newdz=$this->dizhi->where($condition)->order('id desc')->select();
        unset($condition);
        foreach ($newdz as $key => $value) {
           $newdz[$key]['xx']=$value['sf'].$value['shi'].$value['qy'].$value['xx'];
        }
        $this->assign('users',$this->huiyuanpc->where(array('id'=>$login))->find());
        $this->assign('ddzzwx',$ddzzwx);
        $this->assign('newdz',$newdz); 
        $this->display();
    }
    //添加新收货地址
    public function xaddress(){
        $dzid=$_GET['dzid'];
        $login=$this->loginid();
        if($_POST){
            if($login){
                if($this->dizhi->where(array('uid'=>$login))->count()<10) {
                    $this->dizhi->where(array('uid'=>$login))->setField('moren',0);
                    $dz['uid']=$login;
                    $dz['tel']=$_POST['tel'];        //电话
                    $dz['xx']=$_POST['xiangxidz'];  //详细地址
                    $dz['xm']=$_POST['uname'];      //收货人姓名
                    $dz['yb']=$_POST['youbian'];//邮编
                    $szdq=$_POST['szdq'];
                    $szdq=explode(' ',$szdq);
                    $dz['sf']=$szdq[0]?$szdq[0]:'';
                    $dz['shi']=$szdq[1]?$szdq[1]:'';
                    $dz['qy']=$szdq[2]?$szdq[2]:'';
                    $dz['moren']=1;
                    if( $this->dizhi->add($dz)){
                        $data['xdz']=1;
                    }else{
                        $data['xdz']=2;
                    }
                   
                }else{
                    $data['xdz']=4;
                }
            }else{
                $data['xdz']=3;
            }
             $this->ajaxReturn($data);
        }

        if($dzid){
            $condition['id']=array('eq',$dzid);
            $condition['uid']=array('eq',$login);
            $this->assign('newdzxg',$this->dizhi->where($condition)->find()); 
            unset($condition);
        }
        // $this->assign('grzl',$this->login);
        $this->display();
    }
    //地址列表
    public function dizhilist(){
        $login=$this->is_login();
        $newdz=$this->dizhi->where(array('uid'=>$login))->order('id desc')->select();
        foreach ($newdz as $key => $value) {
           $newdz[$key]['xx']=$value['sf'].$value['shi'].$value['qy'].$value['xx'];
        }
        $this->assign('newdz',$newdz);
        $this->display();
    }
    //删除地址
    public function deldizhi(){
        $get_did=$_POST['did']+0;
        $login=$this->is_login();
        if($login){
            $condition['uid']=array('eq',$login);
            $condition['id']=array('eq',$get_did);
            if($this->dizhi->where($condition)->delete()){
                $data['dl']=1;

            }else{
                $data['dl']=2;
            } 
        }else{
            $data['dl']=3;
        }
       
        $this->ajaxReturn($data);
    }
    //修改地址
    public function eidtdz(){
        $login=$this->loginid();
        if($login){
            $this->dizhi->where(array('uid'=>$login))->setField('moren',0);
            $gid=$_POST['gid']+0;
            $dz['tel']=$_POST['tel'];        //电话
            $dz['xx']=$_POST['xiangxidz'];  //详细地址
            $dz['xm']=$_POST['uname'];      //收货人姓名
            $dz['yb']=$_POST['youbian'];//邮编
            $szdq=$_POST['szdq'];
            $szdq=explode(' ',$szdq);
            $dz['sf']=$szdq[0]?$szdq[0]:'';
            $dz['shi']=$szdq[1]?$szdq[1]:'';
            $dz['qy']=$szdq[2]?$szdq[2]:'';
            $dz['moren']=1;
            $condition['uid']=array('eq',$login);
            $condition['id']=array('eq',$gid);
            if($this->dizhi->where($condition)->save($dz)){
                unset($condition);
                $date['is']=1;
            }else{
                $date['is']=2;
            } 
        }else{
           $date['is']=3; 
        }
        
        $this->ajaxReturn($date);
    }
    //更换地址
    public function ghdz(){
        $get_id=$_POST['dids']+0;
        $login=$this->loginid();
        if($login&&$get_id){
            $this->dizhi->where(array('uid'=>$login))->setField('moren',0);

            if($this->dizhi->where(array('uid'=>$login,'id'=>$get_id))->setField('moren',1)){
                $date['gdzl']=1;
            }
           
        }
         $this->ajaxReturn($date);
    }
    // 我的收藏
    public function mycollection(){
        $login=$this->is_login();
        $mycollection=$this->collectionpc->where(array('uid'=>$login,'type'=>2))->order('id desc')->select();
        foreach ($mycollection as $key => $value) {
            $mycollection[$key]['goods']=$this->product->where(array('id'=>$value['sid']))->field('thumb,id,title,price')->find();
        }
        $this->assign('myshopcangp',$mycollection);
        $this->display();
    }
    //加入收藏
    public function addshoucang(){
        $get_id=$_POST['good_id']+0;
        $login=$this->loginid();
        if($login){
            $condition['uid']=array('eq',$login);
            $condition['sid']=array('eq',$get_id);
            $condition['type']=array('eq',2); //1,pc；
            if( $this->collectionpc->where($condition)->getField('id')){
                $data['apsc']=3;
                unset($condition);
            }else{
                $addsc['uid']=$login;
                $addsc['sid']=$get_id;
                $addsc['type']=2; //1,pc； 2,手机；3微信；
                $addsc['ftime']=time();
                if( $this->collectionpc->add($addsc)){
                    $data['apsc']=1;
                }
            }
            
        }else{
            $data['apsc']=2;
        }
        $this->ajaxReturn($data);
    }
    //删除收藏
    public function  delshoucangap(){
        $get_id=$_POST['get_id'];
        $login=$this->loginid();
        if($login){
            $condition['uid']=array('eq',$login);
            $condition['id']=array('eq',$get_id);
            if($this->collectionpc->where($condition)->delete()){
                $data['delp']=1;
            }   
        }else{
            $data['delp']=2;
        }
        $this->ajaxReturn($data);
    }
     /**
     * 库存变动
     *$get_ys       颜色
     *$get_cm       尺码
     *$get_gkc      库存
     *$gkc_number   库存数量
     *$get_number   购买数量
     *$get_id       对应商品id
     *$get_type     类型($get_type=1,加回;$get_type=2,减去)
    */
    function kubd($get_ys,$get_cm,$get_gkc,$gkc_number,$get_number,$get_id,$get_type=2){
        //2016.12.01
        //原来颜色 尺寸 库存
        $old_kc=$get_ys.'lizhenqiu'.$get_cm.'lizhenqiulizhenqiu'.$gkc_number;
        //新库存
        if($get_type==2){
            $new_kc_num=($gkc_number-$get_number)+0;
        }else{
            $new_kc_num=($gkc_number+$get_number)+0;
        }
        if($new_kc_num<0) $new_kc_num=0;
        $new_kc=$get_ys.'lizhenqiu'.$get_cm.'lizhenqiulizhenqiu'.$new_kc_num;
        $kc_data['kc']=str_ireplace($old_kc,$new_kc,$get_gkc);
        //更新库存
        if($this->product->where(array('id'=>$get_id))->save($kc_data)){
            return 1;
        }
        
        
        
    }
    /**
     * 获取对应商品的库存  库存格式->白色lizhenqiuMlizhenqiulizhenqiu998
     *$get_ys       颜色
     *$get_cm       尺码
     *$get_id       对应商品id
    */
    function thisku($get_id,$get_cm,$get_ys){
        $get_kc=$this->product->where(array('id'=>$get_id))->getField('kc');
        $yanseee=explode('moweilin',$get_kc);
         foreach($yanseee as $ks=>$vs){
            $ysssaaa=explode('lizhenqiu',$vs);
            $yansearr[]=$ysssaaa['0'];
            $yuccmmck=$yuccmmck?$yuccmmck.'-'.$ysssaaa['0'].$ysssaaa['1'].'-'.$ysssaaa['3']:$ysssaaa['0'].$ysssaaa['1'].'-'.$ysssaaa['3'];
          }
          $yuccmmck=explode('-',$yuccmmck);
            foreach($yuccmmck as $kk=>$vv){
                $s[]=$vv;
            }
          $yansearr=(array_unique($yansearr));
          $str=$get_ys.$get_cm;

          $tqjz=array_search($str,$s);
          $k=$tqjz+1;
          $ku=$s[$k];
          return $ku;
    }
    //订单期限
    function ordertermpc(){
        $thistime=time()-30*60; //当前时间-30分钟
        $condition['add_time']=array('lt',$thistime);
        $condition['pay_status']=array('neq',2);
        $get_order=$this->order->where($condition)->field('id,shipping_id,cm,ys,number')->select();//获取超时的订单信息
        $where['add_time']=array('lt',$thistime);
        $where['type']=array('neq',2);
        $get_cart=$this->cart->where($where)->field('id,product_id,ys,cm,number')->select();//获取超时的购物车数据
        //扫订单表
        if($get_order){
            foreach ($get_order as $key => $value) {
            $kus=$this->thisku($value['shipping_id'],$value['cm'],$value['ys']); //库存
            $get_kc=$this->product->where(array('id'=>$value['shipping_id']))->getField('kc'); //获取对应商品库存信息
            $vis=$this->kubd($value['ys'],$value['cm'],$get_kc,$kus,$value['number'],$value['shipping_id'],1); //修改库存
            }
            $this->order->where($condition)->delete();
            unset($condition);
        }
        
        // 扫购物车
        if($get_cart){
            foreach ($get_cart as $k => $val) {
            $kus_cart=$this->thisku($val['product_id'],$val['cm'],$val['ys']); //库存
            $get_kc_cart=$this->product->where(array('id'=>$val['product_id']))->getField('kc'); //获取对应商品库存信息
            $vis=$this->kubd($val['ys'],$val['cm'],$get_kc_cart,$kus_cart,$val['number'],$val['product_id'],1); //修改库存
            }
            $this->cart->where($where)->delete();
            unset($where);

        }
    }

}
?>