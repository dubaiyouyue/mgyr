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
class NewsAction extends BaseAction
{

	 function _initialize()
    {	
		parent::_initialize();
		$this->category    = D('Category');
		$this->article     = M('article');
		$this->slide_data  = M('SlideData');
        $this->page        = M('page');
    }
    //资讯中心
    public function index(){
        
        $catid=196;
        $get_tid=$_GET['tid'];
        $get_page=$_GET['page'];
        $thisp=$_GET['p']+0;
        $condition['parentid']=array('eq',196);
        //图片
        $getimg=$this->category->where(array('id'=>$catid))->getField('image');
    	$zxzx_list=$this->category->lanmumoer($condition,3);
        unset($condition);
        if($get_tid){
            $zxctid=$get_tid;
        }else{
            $zxctid=$zxzx_list[0]['id'];
        }
        $categoryfind=$this->category->where(array('id'=>$zxctid))->find();

        $this->assign('catnames',$categoryfind['catname']);
        $condition['catid']=array('eq',$zxctid);
        $condition['status']=array('eq',1);
        //分页
        $count = $this->article->where($condition)->count();
        if($categoryfind['pagesize']>0){
            $numbers=$categoryfind['pagesize'];
        }else{
            $numbers=6;
        }
        $pages=ceil($count/$numbers);
        if(!$thisp||$thisp<1){$thisp=1;}
        if($thisp>$pages){$thisp=$pages;$nextp=$pages;}  
        if(!$prevp){$prevp=$thisp;}    //设置默认初始上页
        if(!$nextp){$nextp=$thisp+1;}  //设置默认初始下页
        $p=($thisp-1)*$numbers;
        if($get_page){$nextp=$thisp+1; if($nextp>$pages){$nextp=$pages;} $prevp=$thisp-1; if($prevp<1){$prevp=1;}}
        $list = $this->article->where($condition)->limit($p,$numbers)->order('listorder desc,createtime desc,id desc')->select();
            foreach ($list as $key => $value) {
                $list[$key]['createtime']=date('Y-m-d',$list[$key]['createtime']);
                        
            }
        unset($condition);
        $this->assign('prevp',$prevp);
        $this->assign('nextp',$nextp); 
        $this->assign('list_news',$list);
        $this->assign('zxctid',$zxctid);
        $this->assign('zx_list',$zxzx_list);
        $this->assign('tupian',$getimg);
        $this->display(); 
    }
    public function detailes(){
        $get_id=$_GET['id'];
        $this->article->where (array('id'=>$get_id))->setInc('hits');  //点击量
        $articles=$this->article->where(array('id'=>$get_id))->find();
        $catid=$articles['catid'];
        $articles['createtime']=date('Y年m月d日',$articles['createtime']);
        /*上一篇下一篇 */
        $pre = $this->article->where("id<$get_id and catid=$catid")->order("id DESC")->find();
        $next =$this->article->where("id>$get_id and catid=$catid")->order("id ASC")->find();
        $this->assign('pre',$pre);
        $this->assign('next',$next);
        $this->assign($articles);
        $this->display(); 
    }
    // 购物指南
    public function gwzhinan(){
        $getimg=$this->category->where(array('id'=>205))->getField('image');
        $get_tid=$_GET['tid'];
        $condition['parentid']=array('eq',205);
        $gwzn_list=$this->category->lanmumoer($condition,4);
        unset($condition);
        $get_tid=$get_tid?$get_tid:$gwzn_list[0]['id'];
        $this->assign('catcontent', $this->page->where(array('id'=>$get_tid))->find());
        $this->assign('get_tid',$get_tid);
        $this->assign('gwzn_list',$gwzn_list);
        $this->assign('tupian',$getimg);
        $this->display();
    }
    //会员订购
    public function huiyuandg(){
        $get_tid=$_GET['tid'];
        $getimg=$this->category->where(array('id'=>211))->getField('image');
        $condition['parentid']=array('eq',211);
        $hydg_list=$this->category->lanmumoer($condition,4);
        $get_tid=$get_tid?$get_tid:$hydg_list[0]['id'];
        unset($condition);
        $this->assign('hycontent', $this->page->where(array('id'=>$get_tid))->find());
        $this->assign('get_tid',$get_tid);
        $this->assign('hydg_list',$hydg_list);
        $this->assign('tupian',$getimg);
        $this->display();
    }
 
}
?>