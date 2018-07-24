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
class ProductAction extends BaseAction
{

	 function _initialize()
    {	
		parent::_initialize();
		$this->category    = M('category');
		$this->product     = M('product');
		$this->slide_data  = M('SlideData');
        $this->cart        = M('cart');
        $this->order       = M('order');
    }
    // 商品分类
    public function index() {
        $catid=184;
        $get_tid=$_GET['tid'];
        if($get_tid){
             $type_zl_lsit=$this->category->where(array('parentid'=>$get_tid))->order('listorder,id desc')->select();
        }else{
             $type_zl_lsit=$this->category->where(array('module'=>'Product','parentid'=>array('neq',0)))->order('listorder,id desc')->select();
			 //2017.01.15
        }
		//dump($type_zl_lsit);exit;
        $type_list=$this->category->where(array('parentid'=>$catid))->order('listorder,id desc')->select();
        foreach ($type_list as $key => $vals) {
            $vals['allctids']=$this->category->where(array('parentid'=>$vals['id']))->order('listorder,id desc')->select();
            $listtyp[]= $vals;
        }
        if($get_tid){
            $catidss=$get_tid;
        }else{
            $catidss=0;//$type_list[0]['id']; 2017.01.15
        }
        // $all_parentid=$this->category->where(array('parentid'=>$catid))->getField('id',true); //huo
        // $condition['parentid']=array('in',$all_parentid);
        // $all_catid=$this->category->where($condition)->getField('id',true);
        $this->assign('listtyp',$listtyp);//类型
        $this->assign('lists_type',$type_list);
        $this->assign('catid_lsits',$type_zl_lsit);
        $this->assign('catid', $catidss);
        $template_r = 'list_type';
        $this->display();
    }
    // 商品列表
    public function lists(){
    	$get_id=$_GET['id'];

        $get_order=$_GET['order'];
        $get_dxps=$_GET['dxps'];
        if($get_order){
            $ddqqppxx=$get_order;
                // 商品排序 2016.11.29
                switch ($ddqqppxx){
                    case 'xl':
                        if($get_dxps=='desc'){
                            $ddqorder='xiliang desc,hits desc,id desc';  $dxps='asc'; //销量排序
                        }else { 
                            $ddqorder='xiliang asc,hits asc,id asc'; $dxps='desc';
                        }
                        break;
                    case 'jg':
                        if($get_dxps=='desc'){
                            $ddqorder='price desc,hits desc,id desc'; $dxps='asc'; //价格排序
                        }else { 
                            $ddqorder='price asc,hits asc,id asc'; $dxps='desc';
                        }
                        break;
                    case 'zx':
                        if($get_dxps=='desc') {
                            $ddqorder='id desc,price desc,hits desc'; $dxps='asc'; //新品排序
                        }else{
                            $ddqorder='id asc,price asc,hits asc'; $dxps='desc';
                        }
                        break;
                        default:
                        $ddqorder='hits desc,price asc,id desc';  $dxps='desc';//综合排序
                }
                    $ddqorder=' '.$ddqorder.',';
        }
      // print_r($dxps);die;
        $dxps=$dxps?$dxps:'desc';
        $get_catid=$this->category->where(array('parentid'=>$get_id))->getField('id',true);

        if($get_catid){
            $strid=array($get_id);
            $get_tids=array_merge($strid,$get_catid);
            $condition['catid']=array('in',$get_tids);
        }else{
            $condition['catid']=array('eq',$get_id);
        }
        $condition['status']=array('eq',1);
        if($get_id==0){unset($condition['catid']);}
        $lists=$this->product->where($condition)->order($ddqorder.'listorder desc,id desc')->limit(8)->select();
        unset($condition);
        $this->assign('get_tids',$get_id);
        $this->assign('dxps',$dxps);
        $this->assign('orders',$get_order);
        $this->assign('pxfs',$get_dxps);
        $this->assign('list',$lists);
        $this->display();
    }
    //加载更多商品
    public function morelist(){
        $type_id=$_POST['type_id'];
        $listp=$_POST['listp'];
        $typeorder=$_POST['typeorder'];
        $typefs=$_POST['typefs'];
        $get_catid=$this->category->where(array('parentid'=>$type_id))->getField('id',true);
        if($get_catid){
            $strid=array($type_id);
            $get_tids=array_merge($strid,$get_catid);
            $condition['catid']=array('in',$get_tids);
        }else{
            $condition['catid']=array('eq',$type_id);
        }
        if($type_id==0){unset($condition['catid']);}
        $condition['status']=array('eq',1);
        $counts=$this->product->where($condition)->count();
        $number=8;
        $pages=ceil($counts/$number);
        if($listp>$pages){$listp=$pages;}
        if($listp<0){$listp=0;}
        $p=$listp*$number;
          if($typeorder){
            $ddqqppxx=$typeorder;
                // 商品排序 2016.11.29
                switch ($ddqqppxx){
                    case 'xl':
                        if($typefs=='desc'){
                            $ddqorder='xiliang desc,hits desc,id desc';  $dxps='asc'; //销量排序
                        }else { 
                            $ddqorder='xiliang asc,hits asc,id asc'; $dxps='desc';
                        }
                        break;
                    case 'jg':
                        if($typefs=='desc'){
                            $ddqorder='price desc,hits desc,id desc'; $dxps='asc'; //价格排序
                        }else { 
                            $ddqorder='price asc,hits asc,id asc'; $dxps='desc';
                        }
                        break;
                    case 'zx':
                        if($typefs=='desc') {
                            $ddqorder='id desc,price desc,hits desc'; $dxps='asc'; //新品排序
                        }else{
                            $ddqorder='id asc,price asc,hits asc'; $dxps='desc';
                        }
                        break;
                        default:
                        $ddqorder='hits desc,price asc,id desc';  $dxps='desc';//综合排序
                }
                    $ddqorder=' '.$ddqorder.',';
        }
        $data['lists']=$this->product->where($condition)->order($ddqorder.'listorder desc,id desc')->limit($p,$number)->select();
        foreach ($data['lists'] as $key => $value) {
             $data['lists'][$key]['nurl']="/index.php/Home/Product/detailes/id/$value[id]";
             $data['lists'][$key]['title']=asmsubstr($value['title'],0,16,'utf-8',true);
        }
        if(count($data['lists'])>0){$data['len']=1;}
        unset($condition);
        $data['listp']=$listp+1;
        $data['typefs']=$typefs;
        $this->ajaxReturn($data);

    }
    //商品详情
    public function detailes(){
        $get_id=$_GET['id'];
        $this->ordertermpc();
        $this->product->where (array('id'=>$get_id))->setInc('hits');  //点击量
        $detail=$this->product->where(array('id'=>$get_id))->find();
        if($detail['pics']){
        $strimg=explode(':::',$detail['pics']);
            foreach ($strimg as $key => $value) {
              $imgss=explode('|',$value);
              $lists_img[]=$imgss[0];
            }
            $this->assign('goodimg',$lists_img);     
        }       
        $this->assign('detail',$detail);
        $this->display();
    }
    //加入购物车
    // function cart_add(){
    //     $login=$this->loginid();
    //     if($login){
    //         $this->ordertermpc();
    //         $get_good_id=$_POST['good_id']+0;
    //         $get_cm=$_POST['cm'];
    //         $get_ys=$_POST['ys'];
    //         $get_gkc=$_POST['gkc']+0;
    //         $get_mun=$_POST['get_mun']+0;
    //         $get_good=$this->product->where(array('id'=>$get_good_id))->find();
    //         $condition['product_id']= array('eq',$get_good_id);
    //         $condition['userid']    = array('eq',$login);
    //         $condition['type']  = array('eq',0);
    //         $condition['cm']    = array('eq',$get_cm);
    //         $condition['ys']    = array('eq',$get_ys);
    //         $kus=$this->thisku($get_good_id,$get_cm,$get_ys);
    //         //购买数量
    //         $get_number=$this->cart->where($condition)->getField('number');  //加入购物车的数量
    //         // 实际数量=已加入购物车的数量+将要加入购物车的数量       
    //         $newsnumber=$get_number+$get_mun;
    //         //库存剩余量=库存-已加入购物车的数量
    //         $shengyukc=$kus- $get_number;
    //         //查询库存 如果库存量少于实际数量 说明预购买数量不足
    //         if($kus>=$newsnumber){
    //             //判断购物车有没有相同数据
    //             if($this->cart->where($condition)->find()){
    //                 //如果存在相同订单直接修改数量
    //                 if($this->cart->where($condition)->setInc('number',$get_mun)){
    //                     $date['is']=1;
                   
    //                 }else{
    //                     $date['is']=2;
    //                 }
    //             }else{
    //                 if($get_good){
    //                     $data['product_id']=$get_good['id'];
    //                     $data['userid']=$login;
    //                     $data['product_price']=$get_good['price'];
    //                     $data['product_thumb']=$get_good['thumb'];
    //                     $data['product_name']=$get_good['title'];
    //                     $data['number']=$get_mun;
    //                     $data['cm']=$get_cm;
    //                     $data['ys']=$get_ys;
    //                     $data['add_time']=time();
    //                     //订单号
    //                     //$ddh=$userid.$get_id.$get_good['price'].time().rand(1000,9999);
    //                     if($this->cart->add($data)){
    //                         //获取库存数量
    //                             $date['is']=1;
    //                             $date['thiskcs']=$this->thisku($get_good_id,$get_cm,$get_ys);

    //                     }else{
    //                         $date['is']=2;
    //                     }
    //                 }
    //             }
    //         }else{$date['is']=4; $date['thiskcs']=$shengyukc;}

    //     }else{
    //         $date['is']=3;
    //     }
    //     $this->ajaxReturn($date);
    // }

    //加入购物车
    function cart_add(){
        $login=$this->loginid();
        if($login){
            $this->ordertermpc();
            $get_good_id=$_POST['good_id']+0;
            $get_cm=$_POST['cm'];
            $get_ys=$_POST['ys'];
            $get_gkc=$_POST['gkc']+0;
            $get_mun=$_POST['get_mun']+0;
            $get_good=$this->product->where(array('id'=>$get_good_id))->find();
            $condition['product_id']= array('eq',$get_good_id);
            $condition['userid']    = array('eq',$login);
            $condition['type']  = array('eq',0);
            $condition['cm']    = array('eq',$get_cm);
            $condition['ys']    = array('eq',$get_ys);
            $kus=$this->thisku($get_good['id'],$get_cm,$get_ys);
            //查询库存
            if($kus>=$get_mun){
                //判断购物车有没有相同数据
                if($this->cart->where($condition)->find()){
                    //如果存在相同订单直接修改数量
                    if($this->cart->where($condition)->setInc('number',$get_mun)){
                        
                        $vis=$this->kubd($get_ys,$get_cm,$get_good['kc'],$kus,$get_mun,$get_good_id,2);
                        if($vis==1){
                            $date['is']=1;
                    }
                    }else{
                        $date['is']=2;
                    }
                }else{
                    if($get_good){
                        $data['product_id']=$get_good['id'];
                        $data['userid']=$login;
                        $data['product_price']=$get_good['price'];
                        $data['product_thumb']=$get_good['thumb'];
                        $data['product_name']=$get_good['title'];
                        $data['number']=$get_mun;
                        $data['cm']=$get_cm;
                        $data['ys']=$get_ys;
                        $data['add_time']=time();
                        //订单号
                        //$ddh=$userid.$get_id.$get_good['price'].time().rand(1000,9999);

                        if($this->cart->add($data)){
                            //获取库存数量
                            $kus=$this->thisku($get_good['id'],$get_cm,$get_ys);
                            $vis=$this->kubd($get_ys,$get_cm,$get_good['kc'],$kus,$get_mun,$get_good['id'],2);
                            if($vis==1){
                                $date['is']=1;
                                $date['thiskcs']=$this->thisku($get_good_id,$get_cm,$get_ys);

                            }
                        }else{
                            $date['is']=2;
                        }
                    }
                }
            }else{$date['is']=4; $date['thiskcs']=$kus;}

        }else{
            $date['is']=3;
        }
        $this->ajaxReturn($date);
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
    //搜索
    function goodsearch(){
        $get_name=$_GET['wds'];
        // print_r($get_name);die;
		$get_name = iconv("gb2312","utf-8//IGNORE",$get_name);
        if($get_name){
            $condition['status']=array('eq',1);
            $condition['title']=array('like','%'.$get_name.'%');
            $counts=$this->product->where($condition)->count();
            $seach_list=$this->product->where($condition)->order('listorder desc,id desc')->limit(10)->select();
            $this->assign('names',$get_name);
            $this->assign('counts',$counts);
			//dump($seach_list);
            $this->assign('seach_list',$seach_list);
        }
		//exit;
        $this->display();
   }
   function morelistsh(){
        $listpss=$_POST['listpss'];
        $names=$_POST['names'];
        $pagep=10;
        $condition['title']=array('like','%'.$names.'%');
        $condition['status']=array('eq',1);
        $counts=$this->product->where($condition)->count();
        $pages=ceil($counts/$pagep);
        if($listpss>$pages){$listpss=$pages;}
        if($listpss<0){$listpss=0;}
        $p=$listpss*$pagep;
        $data['lists']=$this->product->where($condition)->order('listorder desc,id desc')->limit($p,$pagep)->select();
        foreach ($data['lists'] as $key => $value) {
            $data['lists'][$key]['title']=asmsubstr($value['title'],0,20,'utf-8',true);
           $data['lists'][$key]['urls']="/index.php/Home/Product/detailes/id/$value[id]";
        }
        if(count($data['lists'])>0){$data['len']=1;}
        unset($condition);
        $data['listpss']=$listpss+1;
        $this->ajaxReturn($data);

   }
     //订单期限
    function ordertermpc(){
        $thistime=time()-30*60; //当前时间-30分钟
        $condition['add_time']=array('lt',$thistime);
        $condition['pay_status']=array('neq',2);
        $get_order=$this->order->where($condition)->field('id,shipping_id,cm,ys,number')->select();//获取超时的订单信息
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
        
    }
}
?>