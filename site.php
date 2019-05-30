<?php

use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Page;
use Hcode\Model\Cart;

$app->get('/', function() {

	$products=Product::listAll();
	$page = new Hcode\Page();
	$page->setTpl("index",["products"=>Product::checkList($products)]);

});

$app->get("/categories/:idcategory",function($idcategory){

	$pageNum=(isset($_GET['page']))?(int)$_GET['page']:1;

	$category=new Category();
	$category->get((int)$idcategory);
	$pagination=$category->getProductsPage($pageNum);
	$pages=[];
	for($i=1;$i<=$pagination['pages'];$i++){
		array_push($pages,[
			'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
			'page'=>$i
		]);
	}
	$page=new Hcode\Page();
	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>$pagination['data'],
		'pages'=>$pages
	]);
});

$app->get("/products/:desurl",function($desurl){
	$product = new Product();
	$product->getFromURL($desurl);
	$page = new Page();
	$page->setTpl("product-detail",[
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()]);

});

$app->get("/cart",function(){
	$cart =Cart::getFromSession();

	$page=new Page();
	$page->setTpl("cart");
});