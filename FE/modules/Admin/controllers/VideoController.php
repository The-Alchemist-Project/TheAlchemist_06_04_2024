<?php
class Admin_VideoController  extends Zend_Controller_Action {
		var $tb						= "tb_video";
		var $tb_config		= "tb_video_config";
		var $dirImage			=	"files/images/video/";
		var $dirVideo			=	"files/images/video/main/";
		var $page					=	10;

    public function init(){
		$this->view->dirImage = $this->dirImage;
		$this->view->dirVideo = $this->dirVideo;
			$settings = $this->Model->getOne( $this->tb_config ,"" );
			$this->view->settings = $settings;
			//set page default in admin page
			$this->page	=	$this->Plugins->parseInt($settings['page_admin'],10);
			//makedir
		if( !is_dir( $this->dirImage ) ){
			$this->Plugins->makedir($this->dirImage);
		}
		if( !is_dir( $this->dirVideo ) ){
			$this->Plugins->makedir($this->dirVideo);
		}
	}

		//index post
		public function indexAction(){
			$current = $this->Plugins->getCurrentPage();
			$limit = (($current - 1)*($this->page)).','.($this->page);

			if(!isset($_REQUEST['s'])){

				$total_page = $this->Model->getTotal( $this->tb);

				$this->view->totalPost = $total_page;
				$this->view->pageBar = $this->Plugins->getPageBarFull( "{$this->view->actionUrl}/?$qr" ,$current, $total_page, $this->page);
				}
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
					ORDER BY ord LIMIT $limit");
		}

		public function settingAction(){
			if( $this->isPost ){
				$query = $this->Model->update( $this->tb_config, array(
					post_number					=>	$this->Plugins->getNum("post_number","10"),
					next_number					=>	$this->Plugins->getNum("next_number","10"),
					page_admin					=>	$this->Plugins->getNum("page_admin","10")
				),"");

				if( $query ){
					$this->_redirect( $this->view->actionUrl );
				}else{
					$this->view->message = "ERROR_MSQL";
				}
			}
		}

		//delte post action
		public function deleteAction() {
			$id = $this->Plugins->getNum("ID");
			$post = $this->Model->getOne( $this->tb,"WHERE ID=$id");
			if( $post ){
				$this->Model->delete( $this->tb, "ID=$id" );
				@unlink($this->dirImage.$post['img']);
				@unlink($this->dirVideo.$post['video']);
			}
			$this->_redirect($this->view->controllerUrl."?ajax");
		}

		//add post action
		public function addAction() {
			$current = $this->Plugins->getCurrentPage();
			$limit = (($current - 1)*($this->page)).','.($this->page);

			//init post
			if( $this->isPost ){
				$title			=	$this->Plugins->get("title","");
				//$link			=	$this->Plugins->get("link","");
				$ord 				= $this->Plugins->getNum("ord",$this->Model->getTotal($this->tb,"")+1);

				$content 		= $this->Plugins->getEditor("content","");

				$this->view->ord				=	$ord;
				$this->view->title				=	$title;
				//$this->view->link				=	$link;
				$this->view->content 		= $content;


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

				$file1	= @$_FILES['video'];
				$file_upload1 = '';
				if( $file1 && ( $file1['tmp_name'] !=='' ) ){
					$file_upload1 = $this->Plugins->upload( $this->dirVideo, $file1 );
					if( is_int($file_upload1 ) ){
						$this->view->message = "ERROR_UPLOAD_$file_upload1";
						return false;
					}
				}

				//insert into data
				$query = $this->Model->insert( $this->tb, array(
					img					=> $file_upload,
					video					=> $file_upload1,
					title				=> $title,
					ord					=> $ord,
					content			=> $content,
					//link			=> $link,

					user_post			=> $_SESSION['cp__user']['ID'],
				));

				if( !$query && isset($file_upload) ){
					@unlink( ($this->dirImage).$file_upload );
					@unlink( ($this->dirVideo).$file_upload1 );
				}
				$this->_redirect($this->view->controllerUrl);
			}
		}

		//edit post action
		public function editAction() {
			$id	=	$this->Plugins->getNum("ID",0);
			$post	=	$this->Model->getOne( $this->tb,"WHERE ID='$id'");

			if( !$post ){
				$this->view->message	=	"ERROR_NOT_EXISTS";
				return true;
			}

			$title			=	$this->Plugins->get("title",$post['title']);
			//$link			=	$this->Plugins->get("link",$post['link']);
			$ord				= $this->Plugins->getNum('ord',$post['ord']);
			$content 		= $this->Plugins->getEditor("content",$post['content']);


			$this->view->ID		 		= $id;
			$this->view->title		 		= $title;
			//$this->view->link		 		= $link;
			$this->view->img		 	= $post['img'];
			$this->view->video		 	= $post['video'];
			$this->view->ord			= $ord;
			$this->view->content 			= $content;

			//init post

			if( $this->isPost ){



			$config_upload = array();
			if( file_exists( $this->dirImage.$user['img'] ) ){
					$config_upload['oldfile'] = $user['img'];
				}
				$file	= @$_FILES['img'];
				$file_upload = $post['img'];
			/*
				if( !$query && isset($file_upload) ){
					@unlink( ($this->dirImage).$file_upload );
				}
			*/
			if( $file && ( $file['tmp_name'] !=="" )){
					$file_upload = $this->Plugins->uploadImage( $this->dirImage, $file, $config_upload );
					if( is_int($file_upload ) ){
						$this->view->message = "ERROR_UPLOAD_$file_upload";
						return false;
					}
				}
			$config_upload1 = array();
			if( file_exists( $this->dirVideo.$user['video'] ) ){
					$config_upload1['oldfile'] = $user['video'];
				}
				$file1	= @$_FILES['video'];
				$file_upload1 = $post['video'];
			/*
				if( !$query && isset($file_upload1) ){
					@unlink( ($this->dirVideo).$file_upload1 );
				}
			*/
			if( $file1 && ( $file1['tmp_name'] !=="" )){
					$file_upload1 = $this->Plugins->upload( $this->dirVideo, $file1, $config_upload1 );
					if( is_int($file_upload1 ) ){
						$this->view->message = "ERROR_UPLOAD_$file_upload1";
						return false;
					}
				}
				//query update
				$query = $this->Model->update( $this->tb, array(
					img				=> $file_upload,
					video			=> $file_upload1,
					ord				=> $ord,
					content			=> $content,
					//link			=> $link,

					title			=> $title,
					user_edit		=> @$_SESSION['user']['ID'],

				),"ID=$id");

				//direct to index
				$this->_redirect($this->view->controllerUrl);
			}
		}

		//up cat action
		public function upAction() { die('aa');
			$this->Model->up( $this->tb, $this->Plugins->getNum("ID",0) );
			if ( isset($_REQUEST['ajax']) ) {
            $this->_redirect("{$this->view->controllerUrl}/index/?ajax");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/index");
        }
		}

		//dow cat action
		public function downAction() {
			$this->Model->down( $this->tb, $this->Plugins->getNum("ID",0) );
			$this->_redirect( $this->view->controllerUrl."?ajax" );
		}
}