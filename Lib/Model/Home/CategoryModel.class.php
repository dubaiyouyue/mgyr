<?php
class CategoryModel extends Model {
	/*
	 * 表单验证
	 */
	//获取单条数据
	public function lanmuone($get_id){
        $condition['id']=array('eq',$get_id);
        $condition['ismenu']=array('eq',1);
        $lmone=$this->where($condition)->find();
        unset($condition);
        return $lmone;
    }
    //获取多条数据
    public function lanmumoer($condition,$limit=6,$order="listorder asc,id desc"){
        $condition['ismenu']=array('eq',1);
        $lmmoer=$this->where($condition)->limit($limit)->order($order)->select();
        unset($condition);
        return $lmmoer;
    }

}
?>