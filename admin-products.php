<?php
use Hcode\Model\User;
use Hcode\Model\Product;
use Hcode\PageAdmin;


$app->get("/admin/products",function(){
	User::verifyLogin();
	$page = new Hcode\PageAdmin();
	$products=Product::listAll();

	$page->setTpl("products",[
	"products"=>$products]);
});

$app->get("/admin/products/create",function(){
    User::verifyLogin();
    $page = new Hcode\PageAdmin();
    $page->setTpl("products-create");
});

$app->post("/admin/products/create",function(){
    User::verifyLogin();
    $product=new Product();
    $product->setData($_POST);
    $product->save();
    header("Location: /admin/products");
    exit();
});

$app->get("/admin/products/:idproduct",function($idproduct){
    User::verifyLogin();
    $product=new Product();
    $product->get((int)$idproduct);
    $page = new Hcode\PageAdmin();
    $page->setTpl("products-update",['product'=>$product->getValues()]);
});

$app->post("/admin/products/:idproduct",function($idproduct){
    User::verifyLogin();
    $product=new Product();
    $product->get((int)$idproduct);
    $product->setData($_POST);
    $product->setPhoto($_FILES['file']);
    $product->save();
    
    
    header("Location: /admin/products");
    exit();
});

$app->get("/admin/products/:idproduct/delete",function($idproduct){
    User::verifyLogin();
    $product=new Product();
    $product->get((int)$idproduct);
    $product->delete();
    header("Location: /admin/products");
    exit();
});