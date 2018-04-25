<?php

require_once __DIR__ . '/../autoloader.php';
use phpspider\core\phpspider;
use phpspider\core\requests;


$configs = array(
    'name' => 'macklin',
    'log_show' => false,
    'tasknum' => 1,
    'output_encoding' => 'utf-8',
    //'save_running_state' => true,
    'domains' => array(
        'macklin.cn',
        'www.macklin.cn'
    ),
    'scan_urls' => array(
        'http://www.macklin.cn/products',
    	'http://www.macklin.cn/products/M812869',
    	'http://www.macklin.cn/products/A823020'
    ),
    'list_url_regexes' => array(
        "http://www.macklin.cn/category/\d+"
    ),
    'content_url_regexes' => array(
        "http://www.macklin.cn/products/[A-Z]\d+",
    ),
    'max_try' => 2,
    'export' => array(
        'type' => 'csv',
        'file' => '../data/macklin.csv',
    ),
//     'export' => array(
//         'type'  => 'sql',
//         'file'  => '../data/macklin.sql',
//         'table' => 'content',
//     ),
    //'export' => array(
        //'type' => 'db', 
        //'table' => 'content',
    //),
    //'db_config' => array(
        //'host'  => '127.0.0.1',
        //'port'  => 3306,
        //'user'  => 'root',
        //'pass'  => 'root',
        //'name'  => 'macklin',
    //),
    //'queue_config' => array(
        //'host'      => '127.0.0.1',
        //'port'      => 6379,
        //'pass'      => '',
        //'db'        => 5,
        //'prefix'    => 'phpspider',
        //'timeout'   => 30,
    //),
    'fields' => array(
        array(
            'name' => "商品名称",
            'selector' => "//div[contains(@class,'product-detail')]//h1",
            'required' => true,
        ),
        array(
            'name' => "商品别称",
            'selector' => "//div[contains(@class,'product-detail')]/div[contains(@class,'product-general')]/table[1]/tr[1]/td[1]",        		
            'required' => false,
        ),
        array(
            'name' => "CAS号码",
            'selector' => "//div[contains(@class,'product-detail')]/div[contains(@class,'product-general')]/table[1]/tr[2]/td[1]/a",
            'required' => false,
        ),
        array(
            'name' => "分子式",
            'selector' => "//div[contains(@class,'product-detail')]/div[contains(@class,'product-general')]/table[1]/tr[3]/td[1]",
            'required' => false,
        ),
        array(
            'name' => "商品热点",
            'selector' => "//div[contains(@class,'product-detail')]//div[contains(@class,'product-general')]//span",
            'required' => false,
        ),
        array(
            'name' => "成本价",
            'selector' => "",	// 非抓取字段，留空值
            'required' => false,
        ),
        array(
            'name' => "规格值",
            'selector' => "//div[contains(@class,'product-detail')]//div[contains(@class,'shopping')]/table[1]/tbody/tr/td[2]",
            'required' => false,
        	'repeated' => true,
        ),
        array(
            'name' => "商品价格",
            'selector' => "//div[contains(@class,'product-detail')]//div[contains(@class,'shopping')]/table[1]/tbody/tr/td[5]",
            'required' => false,
        	'repeated' => true
        ),
        array(
            'name' => "市场价",
            'selector' => "//div[contains(@class,'product-detail')]//div[contains(@class,'shopping')]/table[1]/tbody/tr/td[4]/del",
            'required' => false,
        	'repeated' => true
        ),
        array(
            'name' => "商品库存",
            'selector' => "//div[contains(@class,'product-detail')]//div[contains(@class,'shopping')]/table[1]/tbody[1]/tr/td[3]",
            'required' => false,
        	'repeated' => true
        ),
        array(
            'name' => "商品货号",
            'selector' => "//div[contains(@class,'product-detail')]//div[contains(@class,'shopping')]//table[1]//tbody[1]//tr//td[1]",
            'required' => false,
        	'repeated' => true
        ),
        array(
            'name' => "品牌名",	//目标网站未定义该字段
            'selector' => "",
            'required' => false,
        ),
        array(
            'name' => "商品描述",
            'selector' => "//*[@id='intro']",
            'required' => false,
        ),
    ),
);



$spider = new phpspider($configs);

$spider->on_extract_field = function($fieldname,$data,$page){
	if(in_array($fieldname, array("规格值","商品价格","市场价","商品库存","商品货号")))
	{
		$data = implode(" | ", $data);
	}
	elseif (in_array($fieldname, array("成本价","品牌名")))
	{
		$data = " ";
	}
	return $data;
};
// $spider->on_extract_page = function($page,$fields){
// 	$fields["品牌名"] = "";
// 	$fields["成本价"] = "";
// 	return $fields;
// };

$spider->start();


