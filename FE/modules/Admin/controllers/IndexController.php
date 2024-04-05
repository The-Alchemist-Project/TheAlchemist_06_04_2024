<?php
class Admin_IndexController  extends Zend_Controller_Action {
  var $tb_user	=	'tb_user';
  var $dirImage = 'files/users/';
	public function init(){ 
		if( ( $id != $_SESSION['cp__user']['ID'] ) && ( $_SESSION['cp__user']['ID'] !=1) ){
			//$this->_redirect( $this->view->controllerUrl );	
			$this->_redirect( BASE_URL.'/vn/Admin/Product/post' );	
			return false;
		}	
	}

	//@Show all user
	public function indexAction(){
		if( $_SESSION['cp__user']['ID'] == 1 ){
			$this->view->users = $this->Model->get( $this->tb_user," ORDER BY ID " );
		}else{
			$this->view->users = $this->Model->get( $this->tb_user," WHERE ID='".$_SESSION['cp__user']['ID']."' ORDER BY ID " );
		}
	}
	
	public function addAction(){
		$name 			= $this->Plugins->get("name","",false);
		$email 			= $this->Plugins->get("email","",false);		

		$this->view->name 	= $name;
		$this->view->email 	= $email;
			
		if( $this->isPost ){
			$password 	= $this->Plugins->get("password","",false);
			$password1 	= $this->Plugins->get("password1","",false);

			if( strlen($name ) < 3 ){
				$this->view->message = "ERROR_USER_TOO_SHORT";
				return false;
			}
			
			if( $this->Model->getTotal($this->tb_user," WHERE upper(name)='".strtoupper($this->view->name)."'" ) > 0 ){
				$this->view->message = "ERROR_USER_EXIST";
				return false;
			}
			
			if(  strlen($password) < 3 ){
				$this->view->message = "ERROR_PASSWORD_TOO_SHORT";
				return false;
			}
				
			if( $password1 != $password ){
				$this->view->message = "ERROR_PASSWORD_NOT_VALID";
				return false;
			}
		
			$file_upload ="";
			if( isset( $_FILES['img'] ) AND ( $_FILES['img']['tmp_name'] !=="" ) ){
				$file_upload = $this->Plugins->uploadImage( $this->dirImage, $_FILES['img'] );
				if( is_int($file_upload) ){
					$this->view->message = "ERROR_UPLOAD_$file_upload";
					return false;
				}
			}
			
			//add to database
			$query = $this->Model->insert( $this->tb_user, array(
				name	=> $this->view->name,
				password=> md5($this->view->password),
				email	=> $this->view->email,
				img		=> $file_upload
			));
			
			if( !$query ){
				$this->view->message	=	"ERROR_MYSQL";
				return false;
			}else{				
				$this->_redirect( $this->view->controllerUrl );	
			}	
		}
	}
	//edit
	public	function editAction(){
		$id = $this->Plugins->getNum("ID","0");
		$user = $this->Model->getOne( $this->tb_user," WHERE ID='".$id."'");
		if( !$user ){
			$this->view->message = "ERROR_USER_NOT_EXISTS";
			return false;
		}	

		$name 					= $this->Plugins->get("name", $user['name'],false);
		$email 					= $this->Plugins->get("email", $user['email'], false);
		
		$this->view->ID					= $id;
		$this->view->name 			= $name;
		$this->view->email		 	= $email;
		$this->view->img		 		= $user["img"];					
		
		//show all user
		if( $this->isPost ){
			$old_password 	= $this->Plugins->get("password0","",false);
			$password 			= $this->Plugins->get("password","",false);
			$password2 			= $this->Plugins->get("password2","",false);

			//only admin#1 can edit name of user
			if( $_SESSION['cp__user']['ID'] != 1 ){
				$name = $user['name'];
			}
			
			//name is too short
			if( strlen($name) < 3 ){
				$this->view->message = "ERROR_USER_TOO_SHORT";
				return false;
			}
			
			//name is exists
			if( $this->Model->getTotal($this->tb_user,"WHERE upper(name) ='".strtoupper($name)."' AND ID<>'".$user['ID']."'" ) > 0 ){
				$this->view->message = "ERROR_USER_EXISTS";
				return false;
			}

			if( $password !="" OR $password2 !="" OR $password0 !=""){
				//pass is not same
				if(  strlen($password) < 3 ){
					$this->view->message = "ERROR_PASSWORD_TOO_SHORT";
					return false;
				}
				
				if( $password2 != $password ){
					$this->view->message = "ERROR_PASSWORD_NOT_VALID";
					return false;
				}
				
				//current pass is wrong, admin#1 needn't valid
				if( (md5($old_password) != $user["password"] )&& ( $_SESSION['cp__user']['ID'] != 1 ) ){
					$this->view->message = "ERORROR_CURRENT_PASSWORD";
					return false;
				}
				$password = md5( $password );
			}else{
				$password = $user['password'];
			}	
			
			//error with file
			$config_upload = array();
			if( file_exists( $this->dirImage.$user['img'] ) ){
				$config_upload['oldfile'] = $user['img'];
			}
			if( isset( $_FILES['img'] ) && ( $_FILES['img']['tmp_name'] !="" ) ){
				$file = $this->Plugins->uploadImage( $this->dirImage, $_FILES['img'], $config_upload );
				if( is_int( $file ) ){
					$this->view->message ="ERROR_UPLOAD_$file";
					return false;
				}
			}else{
				$file = $user["img"];
			}					

			$query = $this->Model->update( $this->tb_user, array(
					name				=> $name,
					img 				=> $file,
					password		=> $password,
					email				=> $email
				)," ID='$id' ");
		
		if( isset($_REQUEST['ajax'])){		
			$this->_redirect( $this->view->controllerUrl."?ajax" );
		}else{
			$this->_redirect( $this->view->controllerUrl );
		}
    	}
	}	
	
	public function deleteAction() {
		$id = $this->Plugins->getNum("ID","0");
		if( $_SESSION["cp__user"]["ID"] != 1 ){
			$this->_redirect( $this->view->controllerUrl );	
			return false;
		}
		
		if( $id != 1){
			$user = $this->Model->getOne( $this->tb_user," WHERE ID='$id'");
			@unlink( $this->dirImage.$user['img']);
			$this->Model->delete( $this->tb_user, "ID='$id'" );
		}
		if( isset($_REQUEST['ajax'])){		
			$this->_redirect( $this->view->controllerUrl."?ajax" );
		}else{
			$this->_redirect( $this->view->controllerUrl );
		}
	}	
}	
?>