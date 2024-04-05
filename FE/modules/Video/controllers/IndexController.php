<?php
class Video_IndexController  extends Zend_Controller_Action {
	var $tb	=	"tb_video";
	var $tb_config		=	"tb_video_config";
	public function init(){
		$this->view->video = $this->Model->get( $this->tb,"ORDER BY ord");
        $info = $this->Model->getOne("tb_contact");
        $this->view->info=$info;
        $meta=array(
            'desc'=>$info['text2'],
            'key'=>$info['text1']
        );
        $this->view->meta=$meta;
	}

	public function indexAction(){
		$idPost = $this->Plugins->get("C","");
		$config = $this->Model->getOne($this->tb_config);
		$current = $this->Plugins->getCurrentPage("p");

		$limit_normal = "0,".($config['post_number']+1);
		$limit= ($current-1)*$config['post_number'].",".($config['post_number']);
		$post = $this
			->Model
			->get($this->tb,"ORDER BY ord LIMIT $limit");
		if( $post ){
			$this->view->post = $post;
			$this->view->currentCat = $idPost;
		}else{
			$this->view->post = $this
				->Model
				->getOne($this->tb,"ORDER BY ord LIMIT $limit");
			$this->view->currentCat = $this->view->post["ID"];

		}
			$total = $this
				->Model
				->getTotal( $this->tb);
			$this->view->page = $current;
			$this->view->pageBar = $this
				->Plugins
				->getPageBarDiv("{$this->view->moduleUrl}/?p=",$current,$total,$config['post_number'],$config['next_number'],true);
	}
}