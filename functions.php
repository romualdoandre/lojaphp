<?php 
use Hcode\Model\User;

function post($key)
{
	return str_replace("'", "", $_POST[$key]);
}
function get($key)
{
	return str_replace("'", "", $_GET[$key]);
}

function formatPrice($price){
	if(!$price>0) 
		$price=0;
    return number_format($price,2,",",".");
}

function checkLogin($inadmin = true)
{

	return User::checkLogin($inadmin);

}

function getUserName()
{

	$user = User::getFromSession();

	return $user->getdesperson();

}

 ?>