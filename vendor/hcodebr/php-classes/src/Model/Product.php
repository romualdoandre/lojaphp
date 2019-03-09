<?php 

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;

class Product extends Model {

	
	protected $fields = [
		"idproduct", "desproduct","vlprice","vlwidth","vlheight","vllength","vlweight","desurl", "dtregister","desphoto"
	];
	
	public static function listAll(){
		$db = new Sql();

		return $db->select("SELECT * FROM tb_products order by desproduct");
    }
    
    public static function checkList($list){
        foreach($list as &$row){
            $p = new Product();
            $p->setData($row);
            $row=$p->getValues();
        }
        return $list;
    }

	public function save(){
		$sql=new Sql();
		$result=$sql->select("CALL sp_products_save(:idproduct,:desproduct,:vlprice,:vlwidth,:vlheight,:vllength,:vlweight,:desurl)",array(
			":idproduct"=>$this->getidproduct(),
            ":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllength"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl()
		));
		$this->setData($result[0]);
	}

	public function get($idproduct){
		$sql=new Sql();
		$result=$sql->select("select * from tb_products where idproduct=:idproduct",array(
			":idproduct"=>$idproduct
		));
		$this->setData($result[0]);
	}

	public function delete(){
        $arquivo=$_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
        "res".DIRECTORY_SEPARATOR.
        "site".DIRECTORY_SEPARATOR.
        "img".DIRECTORY_SEPARATOR.
        "products".DIRECTORY_SEPARATOR.
        $this->getidproduct().".jpg";
        if(file_exists($arquivo))
            unlink($arquivo);
		$sql=new Sql();
        $sql->query("delete from tb_products where idproduct=:idproduct",[":idproduct"=>$this->getidproduct()]);
        
    }
    
    public function getValues(){

        $this->checkPhoto();

        $values= parent::getValues();

        return $values;
    }

    public function checkPhoto(){
        if(file_exists($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
        "res".DIRECTORY_SEPARATOR.
        "site".DIRECTORY_SEPARATOR.
        "img".DIRECTORY_SEPARATOR.
        "products".DIRECTORY_SEPARATOR.
        $this->getidproduct().".jpg"))
            $url= "/res/site/img/products/".$this->getidproduct().".jpg";
        else
            $url= "/res/site/img/product.jpg";

        $this->setdesphoto($url);

    }

    public function setPhoto($file){
        $extension=explode('.',$file["name"]);
        $extension=end($extension);
        switch($extension){
            case "jpg":
            case "jpeg":
                $image=imagecreatefromjpeg($file['tmp_name']);
                break;
            case "gif":
                $image=imagecreatefromgif($file['tmp_name']);
                break;
            case "png":
                $image=imagecreatefrompng($file['tmp_name']);
                break;
        }
        $dist=$_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
        "res".DIRECTORY_SEPARATOR.
        "site".DIRECTORY_SEPARATOR.
        "img".DIRECTORY_SEPARATOR.
        "products".DIRECTORY_SEPARATOR.
        $this->getidproduct().".jpg";

        imagejpeg($image,$dist);
        
        imagedestroy($image);

        $this->checkPhoto();
    }
}

 ?>