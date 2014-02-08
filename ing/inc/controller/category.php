<?php

require(EC_PATH . '/includes/init.php');
include_once(EC_PATH . '/includes/lib_transaction.php');

$categoryGoods = array();

$category = get_categories_tree();
$category = array_merge($category);
//print_r($category);exit;
if (!empty($category)) {

	foreach($category as $key=>$val) {
	
	//$categoryGoods[$key][] = array(
	
		$categoryGoods[$key]['id'] = $val['id'];
		
		$categoryGoods[$key]['name'] = $val['name'];
		
		//);
		
		if(!empty($val['cat_id'])){
		
			foreach($val['cat_id'] as $k=>$v){
			
				$categoryGoods[$key]['children'][] = array(
					 'id'=>$v['id'],
					'name'=>$v['name'],
				 
				//$val[$k]['id'] = $v['id'],
				
				//$val[$k]['name'] = $v['name']
 
				);
			}
		
		} else {
			$categoryGoods[$key]['children'] = array();
		}
		
	}
}


// print_r($categoryGoods);exit;

GZ_Api::outPut($categoryGoods);

