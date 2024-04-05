<?php
class Admin_LinkController  extends Zend_Controller_Action {
		var $tb						= "tb_link";
		var $dirImage			=	"files/images/link/";

    public function init(){
			$this->view->dirImage = $this->dirImage;
			if( !is_dir( $this->dirImage ) ){
				$this->Plugins->makedir($this->dirImage);
			}
   }

		//index post
		public function indexAction(){
			//always get noted post
			$this->view->posts = $this
				->Model
				->queryAll("
					SELECT
						td.*,
						( SELECT GROUP_CONCAT( name COLLATE utf8_general_ci ) FROM tb_user as u WHERE u.ID=td.user_post ) as user_post,
						( SELECT GROUP_CONCAT( name COLLATE utf8_general_ci ) FROM tb_user as k WHERE k.ID=td.user_edit ) as user_edit
					FROM
						{$this->tb} as td
					WHERE
						lang='{$_SESSION['cp_lang']}' ORDER BY ord");
		}

		//delte post action
		public function deleteAction() {
			$id = $this->Plugins->getNum("ID");
			$post = $this->Model->getOne( $this->tb,"WHERE ID=$id");
			if( $post ){
				$this->Model->delete( $this->tb, "ID=$id" );
				@unlink($this->dirImage.$post['img']);
			}
			$this->_redirect($this->view->controllerUrl."?ajax");
		}

		//add post action
		public function addAction() {
			$lang = $_SESSION['cp_lang'];
			//init post
			if( $this->isPost ){
				$link 			= $this->Plugins->get("link","");

				$title 			= $this->Plugins->get("title","");
				$ord 				= $this->Plugins->getNum("ord",$this->Model->getTotal($this->tb)+1);

				$this->view->link 			= $link;
				$this->view->title			= $title;
				$this->view->ord			=	$ord;

				//now upload img if browser
				$file	= @$_FILES['img'];
				$file_upload = '';
				if( $file && ( $file['tmp_name'] !=='' ) ){
					$file_upload = $this->Plugins->uploadImage( $this->dirImage, $file );
					if( is_int($file_upload ) ){
						$this->view->message = "ERROR_UPLOAD_$file_upload";
						return false;
					}
				}

				//insert into data
				$query = $this->Model->insert( $this->tb, array(
					title				=> $title,
					link				=> $link,
					img					=> $file_upload,
					lang				=> $lang,
					ord					=> $ord,
					user_post			=> $_SESSION['cp__user']['ID']
				));

				if( !$query && isset($file_upload) ){
					@unlink( ($this->dirImage).$file_upload );
				}
				$this->_redirect($this->view->controllerUrl);
			}
		}

		//add post action
		public function editAction() {
			$id	=	$this->Plugins->getNum("ID",0);
			$post	=	$this->Model->getOne( $this->tb,"WHERE ID='$id' AND lang='{$_SESSION['cp_lang']}'");

			if( !$post ){
				$this->view->message	=	"ERROR_NOT_EXISTS";
				return true;
			}

			$link 			= $this->Plugins->get('link',$post['link'] !="" ? $post['link']: '' );
			$title 			= $this->Plugins->get('title',$post['title'] !="" ? $post['title']: '' );
			$ord				= $this->Plugins->getNum('ord',$post['ord']);

			$this->view->ID		 		= $id;
			$this->view->img		 	= $post['img'];
			$this->view->link 			= $link;
			$this->view->title 			= $title;
			$this->view->ord			=	$ord;

			//init post
			if( $this->isPost ){
				$file	= @$_FILES['img'];
				$file_upload = $post['img'];
				if( $file && ( $file['tmp_name'] !=="" )){
					$file_upload = $this->Plugins->uploadImage( $this->dirImage, $file, array( 'oldfile' => $post['img'] ) );
					if( is_int($file_upload ) ){
						$this->view->message = "ERROR_UPLOAD_$file_upload";
						return false;
					}
				}
				//query update
				$query = $this->Model->update( $this->tb, array(
					link				=> $link,
					title				=> $title,
					img					=> $file_upload,
					ord					=> $ord,
					user_edit		=> @$_SESSION['user']['ID']
				),"ID=$id");

				//direct to index
				$this->_redirect($this->view->controllerUrl);
			}
		}

		//up cat action
		public function upAction() {
			$this->Model->up( $this->tb, $this->Plugins->getNum("ID",0) );
			$this->_redirect( $this->view->controllerUrl."?ajax" );
		}

		//dow cat action
		public function downAction() {
			$this->Model->down( $this->tb, $this->Plugins->getNum("ID",0) );
			$this->_redirect( $this->view->controllerUrl."?ajax" );
		}
}