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
class IndexAction extends BaseAction
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
        $this->mendianbiaozhu  = M('Mendianbiaozhu','gz_',otherdbs()); 
    }

    public function index()
    {
    	//幻灯片
    	$condition['fid']=array('eq',1);
    	$condition['status']=array('eq',1);
        // print_r($this->slide_data->where($condition)->order('listorder')->select());die;
		$this->assign('home_banner',$this->slide_data->where($condition)->order('listorder')->select());
		unset($condition);
    	//商品分类
        $condition['parentid']=array('eq',184);
        $this->assign('good_type_list',$this->category->lanmumoer($condition,4));
        unset($condition);
        $this->assign('good_type_name',$this->category->lanmuone(184));
        //品牌背景
        $this->assign('ppbj',$this->category->lanmuone(194));
        
    	//优惠活动
		$good_yhhd=$this->category->lanmuone(195);
		$condition['status']=array('eq',1);
		$condition['catid']=array('eq',$good_yhhd['id']);
		$condition['createtime']=array('lt',time());
		$this->assign('get_yhgood',$this->article->where($condition)->limit(4)->order('listorder asc,id desc')->select());
		$this->assign('good_yhhd',$good_yhhd);
		unset($condition);
        // 热销商品
        $condition['status']=array('eq',1);
        $condition['createtime']=array('lt',time());
        $get_hotgood=$this->product->where($condition)->limit(8)->order('id desc')->select();
        // print_r($get_hotgood);die;
        foreach ($get_hotgood as $key => $value) {
            $get_hotgood[$key]['googtype']=$this->category->where(array('id'=>$value['catid']))->getField('catname');
        }
        $this->assign('get_hotgood',$get_hotgood);
        unset($condition);
		$this->assign('bcid',0);//顶级栏目 
		$this->assign('ishome','home');
        $this->display();
    }
    // 品牌背景
    public function brandbj(){
        $catid=194;
        $get_tid=$_GET['tid'];
        $getimg=$this->category->where(array('id'=> $catid))->getField('image');

        $condition['parentid']=array('eq',$catid);
        $get_bjname=$this->category->lanmumoer($condition,4);
        $thisid= $get_tid?$get_tid:$get_bjname[0]['id'];
        //联系我们
        if($thisid==202){
            $this->assign('lianxi',$this->page->where(array('id'=>$thisid))->find());
            $this->assign('Mdianfenbuapi',$this->mendianbiaozhu->select());
        }
        //有情链接
        if($thisid==203){
            // print_r( $this->link->where(array('status'=>1))->select());die;
            $this->assign('linklist', $this->link->where(array('status'=>1))->order('listorder,id desc')->select());
        }
       
        $this->assign('contents',$this->page->where(array('id'=>$thisid))->find());
        $this->assign('bjname',$get_bjname);
        $this->assign('bjtid',$thisid);
        $this->assign('tupian',$getimg);
    	$this->display();
    }
    //更多热销
    public function moresell(){
        $phot=$_POST['phot']+0;
        $condition['status']=array('eq',1);
        $condition['createtime']=array('lt',time());
        $counts=$this->product->where($condition)->count(); //总数
        $number=8;
        $pages=ceil($counts/$number);
        if($phot>$pages){$phot=$pages;}
        if($phot<0){$phot=0;}
        $p=$phot*$number;
        $data['lists']=$this->product->where($condition)->limit($p,$number)->order('id desc')->select();
        unset($condition);
        $data['counts']=count($data['lists']);
        if($data['counts']>0){
             $data['len']=1;
        }
        foreach ($data['lists'] as $key => $value) {
			$data['lists'][$key]['thumb']=$value['thumb'];
            $data['lists'][$key]['nurl']="/index.php/Home/Product/detailes/id/$value[id]";
            $data['lists'][$key]['title']=asmsubstr($value['title'],0,20,'utf-8',true);
            $data['lists'][$key]['googtype']=$this->category->where(array('id'=>$value['catid']))->getField('catname');
        }
        $data['phot']= $phot+1;
        $this->ajaxReturn($data);
    }
    //更多优惠
    public function moreyouhui(){
        $pyh=$_POST['pyh']+0;
        $condition['status']=array('eq',1);
        $condition['catid']=array('eq',195);
        $condition['createtime']=array('lt',time());
        $counts=$this->article->where($condition)->count(); //总数
        $number=4;
        $pages=ceil($counts/$number);
        if($pyh>$pages){$pyh=$pages;}
        if($pyh<0){$pyh=0;}
        $p=$pyh*$number;
        $data['lists']=$this->article->where($condition)->limit($p,$number)->order('listorder,id desc')->select();
        unset($condition);
        $data['counts']=count($data['lists']);
        if($data['counts']>0){
             $data['len']=1;
        }
        foreach ($data['lists'] as $key => $value) {
            $data['lists'][$key]['nurl']="/index.php/Home/Index/yhdetails/id/$value[id]";
            $data['lists'][$key]['title']=asmsubstr($value['title'],0,20,'utf-8',true);
            $data['lists'][$key]['description']=asmsubstr($value['description'],0,26,'utf-8',true);
        }
       if($data['counts']<$number){$pyh=-1;}
        $data['pyh']= $pyh+1; 
        $this->ajaxReturn($data);
    }
      // 优惠活动
    public function yhactivity(){
        //exit;
        $good_yhhd=$this->category->lanmuone(195);
        $condition['status']=array('eq',1);
        $condition['catid']=array('eq',$good_yhhd['id']);
        $condition['createtime']=array('lt',time());
        // print_r($this->article->where($condition)->limit(4)->order('listorder asc,id desc')->select());die;
        $this->assign('get_yhgood',$this->article->where($condition)->limit(4)->order('listorder asc,id desc')->select());
        $this->assign('good_yhhd',$good_yhhd);
        unset($condition);
        $this->display();
    }
    // 更多优惠
    public function yhmoreajax(){
        $pyh=$_POST['pyh']+0;
        $condition['status']=array('eq',1);
        $condition['catid']=array('eq',195);
        $condition['createtime']=array('lt',time());
        $counts=$this->article->where($condition)->count(); //总数
        $numbers=4;
        $pages=ceil($counts/$numbers);
        if($pyh>$pages){$pyh=$pages;}
        if($pyh<0){$pyh=0;}
        $p=$pyh*$numbers;
        $data['lists']=$this->article->where($condition)->limit($p,$numbers)->order('listorder,id desc')->select();
        unset($condition);
        $data['counts']=count($data['lists']);
        if($data['counts']>0){
             $data['len']=1;
           
        }
        foreach ($data['lists'] as $key => $value) {
            $data['lists'][$key]['nurl']="/index.php/Home/Index/yhdetails/id/$value[id]";
            $data['lists'][$key]['title']=asmsubstr($value['title'],0,20,'utf-8',true);
            $data['lists'][$key]['googtype']=$this->category->where(array('id'=>$value['catid']))->getField('catname');
        }
       
        $data['pyh']= $pyh+1; 
        $this->ajaxReturn($data);
    }
    //优惠活动详情
    public function yhdetails(){
        $get_id=$_GET['id']+0;
        $this->article->where (array('id'=>$get_id))->setInc('hits');  //点击量
        $yhgood=$this->article->where(array('id'=>$get_id))->find();
        $yhgood['createtime']=date('Y年m月d日',$yhgood['createtime']);
        //上下篇
        $catid=$yhgood['catid'];
        $pre = $this->article->where("id<$get_id and catid=$catid")->order("id DESC")->find();
        $next =$this->article->where("id>$get_id and catid=$catid")->order("id ASC")->find();
        $this->assign('pre',$pre);
        $this->assign('next',$next);
        $this->assign('yhgood',$yhgood);
        $this->display();
    }
    //客服中心
    public function kefuzhongxin(){
        $catid=216;
        $getimg=$this->category->where(array('id'=> $catid))->getField('image');
        $get_tid=$_GET['tid']+0;
        $condition['parentid']=array('eq',$catid);
        $get_kfzx=$this->category->lanmumoer($condition,4);
        unset($condition);
        $thisid= $get_tid?$get_tid:$get_kfzx[0]['id'];
        // 在线客服
        $this->assign('thisname',$this->category->where(array('id'=>$thisid))->getField('catname'));
        $this->assign('kfzx',$get_kfzx);
        $this->assign('catid',$thisid);
         $this->assign('tupian',$getimg);
        $this->display();
    }
    //提交留言
    public function addmessage(){
        $login=$this->loginid();
        if($login){
            $get_time=$this->message->where(array('userid'=>$login))->order('id desc')->getField('createtime'); // 获取最后一条留言的时间戳
            $daysjc=86400;  //一天的时间戳
            $dqsj=time()-$get_time;
            if($dqsj>$daysjc){  //限制留言提交一天一次
                $ly['name']=$_POST['title'];
                $ly['email']=$_POST['lyyx'];
                $ly['tel']=$_POST['lytel'];
                $ly['content']=$_POST['lycontent'];
                $ly['userid']=$login;
                $ly['createtime']=time();
                if($this->message->add($ly)){
                    $data['ly']=1;
                }
            }else{
                  $data['ly']=2;
            }
        }else{
            $data['ly']=3;
        }
        $this->ajaxReturn($data);
    }
    public function lrequires(){
        $this->display();
    }
}
?>