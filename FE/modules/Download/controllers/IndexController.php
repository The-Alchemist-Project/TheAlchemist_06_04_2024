<?php
class Download_IndexController  extends Zend_Controller_Action {
	var $tb_post		=	"tb_download_post";
	var $tb_cat			=	"tb_download_cat";
	var $tb_config		=	"tb_download_config";
	public function init(){
		$this->view->cats = $this
			->Model
			->get( $this->tb_cat,"WHERE lang='{$this->language}' ORDER BY ord ");
	}
	
	public function indexAction(){
		$catID  = $this->Plugins->getNum('C',0);
		$postID = $this->Plugins->getNum('ID',0);
		$config = $this->Model->getOne($this->tb_config,"WHERE lang='{$this->language}'");
		$current = $this->Plugins->getCurrentPage("p");
		$limit_normal = "0,".($config['post_number']+1);
		$limit= ($current-1)*$config['post_number'].",".($config['post_number']);
		$this->view->date = $this->Plugins->time();
		if( $postID != 0 ){
			$post = $this
				->Model
				->getOne( $this->tb_post,"WHERE lang='{$this->language}' AND ID='$postID' AND date <= {$this->Plugins->time()}" );
			if( !$post ){
				$this->_redirect( $this->view->moduleUrl );
				return false;
			}
			
			$cat = $this->Model->getOne( $this->tb_cat,"WHERE ID='{$post['parent_id']}'");
			if( !$cat ){
				$this->_redirect( $this->view->moduleUrl );
				return false;
			}			
			
			$this->view->post = $post;
			$this->view->date = $this->view->post['date'];
			$this->view->title = $cat['title'];	
			$this->view->currentCat = $cat['ID'];
			$this->view->olderPosts = $this
				->Model
				->get( $this->tb_post,"WHERE date <= {$post['date']} AND lang='{$this->language}' AND status<>0 LIMIT $limit_normal");
			
			$this->view->newerPosts = $this
				->Model
				->queryAll("SELECT *,
						(CASE WHEN sticky > 0 THEN sticky ELSE 'z' END ) as idx
						FROM {$this->tb_post}
						WHERE date <= {$this->Plugins->time()} AND lang='{$this->language}' AND status<>0 ORDER BY idx, date DESC LIMIT $limit_normal");	
				
		}else if( $catID != 0 ){
			$cat = $this
				->Model
				->getOne( $this->tb_cat,"WHERE ID='$catID' AND lang='{$this->language}'");
			if( !$cat ){
				$this->_redirect( $this->view->moduleUrl );
				return false;
			}
	
			$this->view->title = $cat['title'];
			$this->view->posts = $this
				->Model
				->queryAll("SELECT *,
						(CASE WHEN sticky > 0 THEN sticky ELSE 'z' END ) as idx
						FROM {$this->tb_post}
						WHERE lang='{$this->language}' AND parent_id='$catID' AND status<>0 AND date <= {$this->Plugins->time()} ORDER BY idx ASC, date DESC,date DESC LIMIT $limit" );
			$this->view->title = $cat['title'];	
			$this->view->currentCat = $catID;
			
			//build page bar
			$total = $this
				->Model
				->getTotal( $this->tb_post,"WHERE lang='{$this->language}' AND status<>0 AND parent_id='$catID' AND date <= {$this->Plugins->time()}");
			$this->view->pageBar = $this
				->Plugins
				->getPageBarDiv("{$this->view->moduleUrl}/?C=$catID&p=",$current,$total,$config['post_number'],$config['next_number'],true);	
		}else{
			$this->view->posts = $this
				->Model
				->queryAll("SELECT *,
					(CASE WHEN sticky > 0 THEN sticky ELSE 'z' END ) as idx
					FROM {$this->tb_post} 
					WHERE lang='{$this->language}' AND status<>0 AND date <= {$this->Plugins->time()} ORDER BY idx, date DESC LIMIT $limit" );
			//build page bar
			$total = $this
				->Model
				->getTotal( $this->tb_post,"WHERE lang='{$this->language}' AND status<>0 AND date <= {$this->Plugins->time()}");
			
			$this->view->pageBar = $this
				->Plugins
				->getPageBarDiv("{$this->view->moduleUrl}/?p=",$current,$total,$config['post_number'],$config['next_number'],true);				
		}	
	}
}	
