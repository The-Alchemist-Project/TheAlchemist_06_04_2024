<?php

class About_IndexController extends Zend_Controller_Action {

    var $tb_post = "tb_about_post";
    var $tb_config = "tb_about_config";

    public function init() {
        $this->view->info = $this->Model->getOne("tb_contact");
    }

    public function indexAction() {
        $postID = $this->Plugins->getNum('C', 0);
        $config = $this->Model->getOne($this->tb_config, "WHERE lang='{$this->language}'");
        $current = $this->Plugins->getCurrentPage("p");
        $limit = ($current - 1) * $config['post_number'] . "," . ($config['post_number']);
        $this->view->date = $this->Plugins->time();
        $info=$this->view->info;
        $meta=array(
            'desc'=>$info['text2'],
            'key'=>$info['text1']
        );
        if ( $postID != 0 ) {
            $post = $this
                    ->Model
                    ->getOne($this->tb_post, "WHERE  ID='$postID'");
            if ( !$post ) {
                $this->_redirect($this->view->moduleUrl);
                return false;
            }
            if($this->language=='vn'){
               $post['title']=$post['title_vn'];
               $post['content']=$post['content_vn'];
            }else{
               $post['title']=$post['title_en'];
               $post['content']=$post['content_en'];
            }
            if($post['desc']){
                $meta['desc']=$post['desc'];
            }
            if($post['keyword']){
                $meta['key']=$post['keyword'];
            }
            $this->view->meta=$meta;
            $this->view->post = $post;
            $this->view->id_active=$postID;
            $this->view->newerPosts = $this
				->Model
				->queryAll("SELECT *,
                        IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`
						FROM {$this->tb_post}
						WHERE date <= {$this->Plugins->time()}  AND ID<>'$postID' ORDER BY  date DESC LIMIT 5");
        } else {
            $post = $this
                    ->Model
                    ->queryOne("SELECT *,
                    IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`,
                        IF('{$this->language}'='vn',`content_vn`,`content_en`) as `content`
					FROM {$this->tb_post}
					ORDER BY ord LIMIT 1");
            $this->view->id_active=$post['ID'];
            $this->view->post=$post;
            if($post['desc']){
                $meta['desc']=$post['desc'];
            }
            if($post['keyword']){
                $meta['key']=$post['keyword'];
            }
            $this->view->meta=$meta;
            $this->view->newerPosts = $this
				->Model
				->queryAll("SELECT `a`.*,
                        IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`
						FROM {$this->tb_post} as `a`
                        WHERE `a`.`ID` <> '{$post['ID']}'
                        ORDER BY `a`.`ord`, `a`.`ID` LIMIT 5");
//            $this->view->posts = $this
//                    ->Model
//                    ->queryAll("SELECT *,
//                    IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`,
//                        IF('{$this->language}'='vn',`content_vn`,`content_en`) as `content`
//					FROM {$this->tb_post}
//					WHERE lang='{$this->language}' ORDER BY ord LIMIT $limit");
//            //build page bar
//            $total = $this
//                    ->Model
//                    ->getTotal($this->tb_post, "WHERE lang='{$this->language}'");
//            $this->view->currentPage = $current;
//            $this->view->totalPage = ceil($total / $config['post_number']);
//            $this->view->pageBar = $this
//                    ->Plugins
//                    ->getPageBarDiv("{$this->view->moduleUrl}/?p=", $current, $total, $config['post_number'], $config['next_number'], true);
        }

    }

}
