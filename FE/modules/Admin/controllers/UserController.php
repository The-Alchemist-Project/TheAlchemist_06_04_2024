<?php
class Admin_UserController  extends Zend_Controller_Action {
	var $tb				=	'tb_user';
	var $dirImage = 'files/users/';
	public function init(){ }

	//@Show all user
	public function indexAction(){
		if( $_SESSION['cp__user']['ID'] == 1 ){
			$this->view->users = $this->Model->get( $this->tb," ORDER BY ID " );
		}else{
			$this->view->users = $this->Model->get( $this->tb," WHERE ID='".$_SESSION['cp__user']['ID']."' ORDER BY ID " );
		}
	}
	
	public function addAction(){
		if( !isset( $_SESSION['cp__user'] ) OR ( $_SESSION['cp__user']['group'] != 1 ) ){
			$this->_redirect( $this->view->controllerUrl );	
			return false;
		}		
		if( $this->isPost ){
			$this->view->name 			= $this->Plugins->get("name","",false);
			$this->view->email 			= $this->Plugins->get("email","",false);
			$this->view->password 	= $this->Plugins->get("password","",false);
			$this->view->password1 	= $this->Plugins->get("password1","",false);
			

			//name is too short	
			if( strlen($this->view->name ) < 3 ){
				$this->view->message = "ERROR_NAME_TOO_SHORT";
				return false;
			}
			
			//password is not same
			if( ($this->view->password =="") || ( $this->view->password1 !=$this->view->password ) ){
				$this->view->message = "ERROR_PASSWORD_NOT_EQUAL";
				return false;
			}
			
			//user name exists
			if( $this->Model->getTotal($this->tb," WHERE upper(name)='".strtoupper($this->view->name)."'" ) > 0 ){
				$this->view->message = "ERROR_NAME_EXIST";
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
			$query = $this->Model->insert( $this->tb, array(
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
		if( ( $id != $_SESSION['cp__user']['ID'] ) && ( $_SESSION['cp__user']['ID'] !=1) ){
			$this->_redirect( $this->view->controllerUrl );	
			return false;
		}
		
		$user = $this->Model->getOne( $this->tb," WHERE ID='".$id."'");
		if( !$user ){
			$this->view->message = 0;
			return false;
		}	
		
		$this->view->ID					= $user['ID'];
		$this->view->name 			= $user['name'];
		$this->view->email		 	= $user["email"];
		$this->view->img		 		= $user["img"];					
		
		//show all user
		if( $this->isPost ){
	
			$name 					= $this->Plugins->get("name","",false);
			$email 					= $this->Plugins->get("email","",false);
			$old_password 	= $this->Plugins->get("password0","",false);
			$password 			= $this->Plugins->get("password","",false);
			$password2 			= $this->Plugins->get("password2","",false);

			//only admin#1 can edit name of user
			if( $_SESSION['cp__user']['ID'] != 1 ){
				$name = $user['name'];
			}
			
			//name is too short
			if( strlen($name) < 3 ){
				$this->view->message = 1;
				return false;
			}
			
			//pass is not same
			if(  $password ==""  OR  $password2 != $password ){
				$this->view->message = 2;
				return false;
			}
			
			//name is exists
			if( $this->Model->getTotal($this->tb,"WHERE upper(name) ='".strtoupper($name)."' AND ID<>'".$user['ID']."'" ) > 0 ){
				$this->view->message = 3;
				return false;
			}
			
			//current pass is wrong, admin#1 needn't valid
			if( (md5($old_password) != $user["password"] )&& ( $_SESSION['cp__user']['ID'] != 1 ) ){
				$this->view->message = 4;
				return false;
			}
			
			//error with file
			$config_upload = array();
			if( file_exists( "{$this->dirImnage}/{$user['img']}") ){
				$config_upload['oldfile']=$user['img'];
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

			$query = $this->Model->update( $this->tb, array(
					name				=> $name,
					img 				=> $file,
					password			=> md5($password),
					email				=> $email
				)," ID='$id' ");
			$this->_redirect( $this->view->controllerUrl );
		}
	}	
	
	public function deleteAction() {
		$id = $this->Plugins->getNum("ID","0");
		
		if( $_SESSION["cp__user"]["ID"] != 1 ){
			$this->_redirect( $this->view->controllerUrl."?ajax" );
			return false;
		}
		
		if( $id != 1){
			$user = $this->Model->getOne( $this->tb," WHERE ID='$id'");
			@unlink( "{$this->dirImage}/{$user['img']}");
			$this->Model->delete( $this->tb, "ID='$id'" );
		}	
		if( isset($_REQUEST['ajax'])){		
			$this->_redirect( $this->view->controllerUrl."?ajax" );
		}else{
			$this->_redirect( $this->view->controllerUrl );
		}
		
	}	
}	
?>