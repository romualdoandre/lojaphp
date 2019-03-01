<?php 

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;

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
}

 ?>