<?php 

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model\Product;

class Category extends Model {

	
	protected $fields = [
		"idcategory", "descategory", "dtregister"
	];
	
	public static function listAll(){
		$db = new Sql();

		return $db->select("SELECT * FROM tb_categories order by descategory");
	}

	public function save(){
		$sql=new Sql();
		$result=$sql->select("CALL sp_categories_save(:idcategory,:descategory)",array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));
		$this->setData($result[0]);
		Category::updateFile();
	}

	public function get($idcategory){
		$sql=new Sql();
		$result=$sql->select("select * from tb_categories where idcategory=:idcategory",array(
			":idcategory"=>$idcategory
		));
		$this->setData($result[0]);
	}

	public function delete(){
		$sql=new Sql();
		$sql->query("delete from tb_categories where idcategory=:idcategory",[":idcategory"=>$this->getidcategory()]);
		Category::updateFile();
	}

	public static function updateFile(){
		$categories=Category::listAll();
		$html=array();
		foreach($categories as $row){
			array_push($html,'<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}
		file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'categories-menu.html',implode('',$html));
	}

	public function getProducts($related=true){
		$sql = new Sql();
		if ($related === true) {
			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				);
			", [
				':idcategory'=>$this->getidcategory()
			]);
		} else {
			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct NOT IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				);
			", [
				':idcategory'=>$this->getidcategory()
			]);
		}
	}

	public function getProductsPage($page=1,$itemsPerPage=3){
		$start=($page-1)*$itemsPerPage;
		$sql=new Sql();
		$results= $sql->select("
		select 	SQL_CALC_FOUND_ROWS * from tb_products a
		inner join tb_productscategories b on a.idproduct=b.idproduct
		inner join tb_categories c on c.idcategory=b.idcategory
		where c.idcategory=:idcategory limit $start, $itemsPerPage;
		",[
			':idcategory'=>$this->getidcategory()
		]);
		$resultTotal=$sql->select("select FOUND_ROWS() AS nrtotal;");
		return [
			'data'=>Product::checkList($results),
			'total'=>(int)$resultTotal[0]['nrtotal'],
			'pages'=>ceil($resultTotal[0]['nrtotal']/$itemsPerPage)
		];

	}

	public function addProduct(Product $product){
		$sql=new Sql();
		$sql->query("INSERT INTO tb_productscategories (idcategory,idproduct) values (:idcategory,:idproduct);",[
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);
	}

	public function removeProduct(Product $product){
		$sql=new Sql();
		$sql->query("DELETE FROM tb_productscategories WHERE idcategory=:idcategory AND idproduct=:idproduct;",[
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);
	}
}

 ?>