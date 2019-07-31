<?php 

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Mailer;

class User extends Model {

	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSucess";

	protected $fields = [
		"iduser", "idperson", "deslogin", "despassword", "inadmin", "dtregister","desperson","desemail","nrphone"
	];

	public static function getFromSession(){
		$user = new User();
		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser']>0){
			$user->setData($_SESSION[User::SESSION]);
			
		}
		return $user;
	}

	public static function login($login, $password):User
	{

		$db = new Sql();

		$results = $db->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if (count($results) === 0) {
			throw new \Exception("Não foi possível fazer login.");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"])) {

			$user = new User();
			
			$data['desperson']=utf8_encode($data['desperson']);
			$user->setData($data);
			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {

			throw new \Exception("Não foi possível fazer login.");

		}

	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}

	public static function verifyLogin($inadmin = true)
	{

		if (!User::checkLogin($inadmin)) {
			if ($inadmin) {
				header("Location: /admin/login");
			} else {
				header("Location: /login");
			}
			exit;
		}

	}

	public static function checkLogin($inadmin=true){
		if (
			!isset($_SESSION[User::SESSION])
			|| 
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		)
		return false;
		else{
			if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"]===true)
				return true;
			else if($inadmin === false)
				return true;
			else
				return false;
		}

	}

	public static function checkLoginExist($login)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
		]);
		return (count($results) > 0);
	}
	
	public static function listAll(){
		$db = new Sql();

		return $db->select("SELECT * FROM tb_users a inner join tb_persons b using(idperson) order by b.desperson");
	}
	
	public function save(){
		$sql = new Sql();
		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
		$this->setData($results[0]);
		
	}
	public static function getPasswordHash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);
	}

	public function get($iduser){
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b on a.idperson=b.idperson WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));
		$data = $results[0];
		$data['desperson']=utf8_encode($data['desperson']);
		$this->setData($data);
	}
	
	public function update(){
		$sql = new Sql();
		$results = $sql->select("CALL sp_usersupdate_save(:iduser,:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>user::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
		$this->setData($results[0]);
		
	}
	
	public function delete(){
		$sql = new Sql();
		$results = $sql->select("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
	}
	
	public static function getForgot($email, $inadmin = true){
		$sql=new Sql();
		$results=$sql->select("select * from tb_persons a inner join tb_users b on a.idperson=b.idperson where a.desemail=:email",array(
		":email"=>$email
		));
		if(count($results)===0){
			throw new \Exception("Não foi possível recuperar a senha");
		}
		else{
			$data=$results[0];
			$results2=$sql->select("call  sp_userspasswordsrecoveries_create(:iduser,:desip)",array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]
			));
			if(count($results2)===0){
				throw new \Exception("Não foi possível recuperar a senha");
			}
			else{
				$dataRecovery=$results2[0];
				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
				if ($inadmin === true) {
					
					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
				} else {
					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
				}
				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Hcode Store", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link
				));
				$mailer->send();
				return $data;

			}
		}
		
	}
	
	public static function validForgotDecrypt($code){
		
		$idrecovery=mcrypt_decrypt(MCRYPT_RIJNDAEL_128,User::SECRET,base64_decode($code),MCRYPT_MODE_ECB);
		$sql=new Sql();
		$results=$sql->select("select * 
		from tb_userspasswordsrecoveries a 
		inner join tb_users b using(iduser)
		inner join tb_persons c using(idperson)
		where 
		a.idrecovery=:idrecovery and a.dtrecovery is null and date_add(a.dtregister, interval 1 hour)>=now()",array(':idrecovery'=>$idrecovery));
		if(count($results)===0){
			throw new \Exception("Não foi possível recuperar a senha");
			
		}
		else{
			return $results[0];
		}
	}
	
	public static function setForgotUsed($idrecovery){
		$sql=new Sql();
		$sql->query("update tb_userspasswordsrecoveries set dtrecovery=now() where idrecovery=:idrecovery",array(':idrecovery'=>$idrecovery));
		
	}
	
	public function setPassword($password){
		$sql=new Sql();
		$sql->query("update tb_users set despassword=:password where iduser=:iduser",array(":password"=>$password,":iduser"=>$this->getiduser()));
	}
	public static function setError($msg)
	{

		$_SESSION[User::ERROR] = $msg;

	}

	public static function getError()
	{

		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

		User::clearError();

		return $msg;

	}

	public static function clearError()
	{

		$_SESSION[User::ERROR] = NULL;

	}

	public static function setSuccess($msg)
	{

		$_SESSION[User::SUCCESS] = $msg;

	}

	public static function getSuccess()
	{

		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

		User::clearSuccess();

		return $msg;

	}

	public static function clearSuccess()
	{

		$_SESSION[User::SUCCESS] = NULL;

	}

	public static function setErrorRegister($msg)
	{

		$_SESSION[User::ERROR_REGISTER] = $msg;

	}

	public static function getErrorRegister()
	{

		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

		User::clearErrorRegister();

		return $msg;

	}

	public static function clearErrorRegister()
	{

		$_SESSION[User::ERROR_REGISTER] = NULL;

	}


}

 ?>