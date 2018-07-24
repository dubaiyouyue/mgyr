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
		$this->category    = M('category');
		$this->product     = M('product');
		$this->slide_data  = M('SlideData');
    }

    public function index()
    {
        echo "资讯中心";die;
    	$list=$this->product->select();
		$this->assign('list',$list);
        $this->display();
    }

 
}
?>