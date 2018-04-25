<?php

require_once __DIR__ . '/../autoloader.php';
use phpspider\core\phpspider;
use phpspider\core\requests;


$configs = array(
    'name' => 'XiYa',
    'log_show' => false,
    'tasknum' => 1,
    'output_encoding' => 'utf-8',
    //'save_running_state' => true,
    'domains' => array(
        'xiyashiji.com',
        'www.xiyashiji.com'
    ),
    'scan_urls' => array(
        'http://www.xiyashiji.com/prodtype.html',
    	'http://www.xiyashiji.com/goods-288.html',
    	'http://www.xiyashiji.com/goods-26785.html'		// zsf 测试页面
    ),
    'list_url_regexes' => array(
    	"/product-\d+.html",
        "/product-\d+.html?pageno=\d+&totalresult=\d+&key=",
    	// 列表页  http://www.xiyashiji.com/product-143.html?pageno=2&totalresult=541&key=
    ),
    'content_url_regexes' => array(
        "/goods-\d+.html",
    ),
    'max_try' => 2,
    'export' => array(
        'type' => 'csv',
        'file' => '../data/xiya.csv',
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
 
    'fields' => array(
        array(
            'name' => "商品名称",
            'selector' => '//article[@id="main"]//div[contains(@class,"goodinfo_detail_rightinfo")]//h2',
            'required' => true,
        ),
        array(
            'name' => "商品别称",
            'selector' => '//article[@id="main"]//div[contains(@class,"goodinfo_detail_rightinfo")]//li[3]/span[1]',        		
            'required' => false,
        ),
        array(
            'name' => "CAS号码",
            'selector' => '//article[@id="main"]//div[contains(@class,"goodinfo_detail_rightinfo")]//li[2]/div[2]/span',
            'required' => false,
        ),
        array(
            'name' => "分子式",
            'selector' => '//article[@id="main"]//div[contains(@class,"goodinfo_detail_rightinfo")]//li[3]/span[2]',
            'required' => false,
        ),
        array(
            'name' => "商品热点",		// 该字段留空
            'selector' => "",
            'required' => false,
        ),
        array(
            'name' => "成本价",
            'selector' => "",	// 非抓取字段，留空值
            'required' => false,
        ),
        array(
            'name' => "规格值",
            'selector' => '//div[contains(@class,"goodinfo_detail_rightlist")]//ul[contains(@id,"divorderlist")]//li[2]',
            'required' => false,
        	'repeated' => true
        ),
        array(
            'name' => "商品价格",
        	// 由于商品的优惠价会随着购买数量而变化，页面通过ajax异步向接口请求结果http://www.xiyashiji.com/member/getcuxiaoprice.html
            // 'selector' => '//div[contains(@class,"goodinfo_detail_rightlist")]//ul[contains(@id,"divorderlist")]//li[5]/span',
            'selector' => '',
            'required' => false,
//         	'repeated' => true
        ),
        array(
            'name' => "市场价",
            'selector' => '//div[contains(@class,"goodinfo_detail_rightlist")]//ul[contains(@id,"divorderlist")]//li[4]',
            'required' => false,
        	'repeated' => true
        ),
        array(
            'name' => "商品库存",		// 西亚的库存进行判断 如果有价格就是99，无价格就是0
            'selector' => "",
            'required' => false,
        ),
        array(
            'name' => "商品货号",
            'selector' => '//div[contains(@class,"goodinfo_detail_rightlist")]//ul[contains(@id,"divorderlist")]//li[1]',
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
            'selector' => '//div[@class="goodinfo_about"]',
            'required' => false,
//         	'repeated' => true
        ),
    ),
);



$spider = new phpspider($configs);
/*  on_fetch_url回调函数，在页面上通过正则表达式匹配到需要爬取的url后，在url加入队列前执行。
 * $spider->on_fetch_url = function($url,$phpspider){
	if($phpspider->is_list_page($url) || $phpspider->is_content_page($url)){
		$url = 'http://www.xiyashiji.com'.$url;
	}
	return $url;
}; */

// 爬取一个字段后执行
$spider->on_extract_field = function($fieldname,$data,$page){
	if(in_array($fieldname, array("规格值","市场价","商品货号")))
	{
		$data = implode(" | ", $data);
	}
	elseif(in_array($fieldname, array("商品热点","成本价","品牌名","商品库存")))
	{
		$data = " ";
	}
// 	if($fieldname =="商品描述")
// 	{
// 		$data = implode("", $data);
// 	}
	return $data;
};

// 在一个网页的所有field抽取完成之后, 可能需要对field进一步处理,再保存数据
$spider->on_extract_page = function($page,$fields){
	$orderid = explode(" | ", $fields["商品货号"]);
	$price = array();
	for($i=0; $i<count($orderid); $i++)
	{
		$request = new requests();
		$price_json = $request->post('http://www.xiyashiji.com/member/getcuxiaoprice.html',array("count"=>"1","orderid"=>$orderid[$i]));
		$price_arr = json_decode($price_json,true);
		$price[] = $price_arr["price"];
		$kucun[] = empty($price_arr["price"]) ? "0":"99";
	}
	$fields["商品库存"] = implode(" | ", $kucun);
	$fields["商品价格"] = implode(" | ", $price);
	
	return $fields;
};

/* 设置爬取网页时的超时时间 单位秒，默认5秒
 * $spider->on_start = function($phpspider)
{
	requests::set_timeout(10);
}; */

$spider->start();


