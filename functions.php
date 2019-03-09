<?php 

function post($key)
{
	return str_replace("'", "", $_POST[$key]);
}
function get($key)
{
	return str_replace("'", "", $_GET[$key]);
}

function formatPrice($price){

    return number_format($price,2,",",".");
}

 ?>