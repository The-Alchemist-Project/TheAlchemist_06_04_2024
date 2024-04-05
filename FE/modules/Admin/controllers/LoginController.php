<?php
class Admin_LoginController  extends Zend_Controller_Action {
	public function init(){
		$this->tb_user	= "tb_user";
	}
	
	public function indexAction(){
		if( $this->isPost ){
			$name = $this->Plugins->get("username","",false);
			$password = $this->Plugins->get("password","",false);
			$user=$this->Model->queryOne("SELECT * FROM `tb_user` WHERE upper(name)='".strtoupper($name)."' AND password='".md5($password)."'");
	
			if( $user ){
				$user['password']="chicken";
				$_SESSION['cp__user'] = $user;
				$this->Model->update($this->tb_user,array( 'visit' => $this->Plugins->time() ),"ID='{$user['ID']}'");
				$this->_redirect( BASE_URL."/{$this->language}/Admin" );
			}else{
				$this->view->error = "LOGIN_ERROR";	
			}
		}else{
			unset($_SESSION['cp__user']);
		}
	}
}	
?>