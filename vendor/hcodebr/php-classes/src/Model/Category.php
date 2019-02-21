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
		//var_dump($result);
		//exit();
		$this->setData($result[0]);
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
		$sql->query("delete from tb_categories where idcategory=:idcategory",
		[":idcategory"=>$this->getidcategory()]
	);


	}
}

 ?>