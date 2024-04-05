<?php

class Gallery_IndexController extends Zend_Controller_Action {

    var $tb_post = "tb_gallery_post";
    var $tb_cat = "tb_gallery_cat";
    var $tb_config = "tb_gallery_config";
    var $tb_files = "tb_gallery_files";
    var $lang = 'vn';

    public function init() {
        $this->view->info = $this->Model->getOne("tb_contact");

    }

    public function indexAction() {
        $catID = $this->Plugins->getNum('C', 0);
        $postID = $this->Plugins->getNum('ID', 0);
        $config = $this->Model->getOne($this->tb_config, "WHERE lang='{$this->lang}'");
        $current = $this->Plugins->getCurrentPage("p");
        $limit_normal = "0," . ($config['post_number'] + 1);
        $limit = ($current - 1) * $config['post_number'] . "," . ($config['post_number']);
        $this->view->date = $this->Plugins->time();
        if ( $postID != 0 ) {
            $post = $this
                    ->Model
                    ->getOne($this->tb_post, "WHERE lang='{$this->lang}' AND ID='$postID' AND date <= {$this->Plugins->time()}");

            if ( !$post ) {
                $this->_redirect($this->view->moduleUrl);
                return false;
            }


            $cat = $this->Model->getOne($this->tb_cat, "WHERE ID='{$post['parent_id']}'");
//            if ( !$cat ) {
//                $this->_redirect($this->view->moduleUrl);
//                return false;
//            }
            $this->view->files = $this
                    ->Model
                    ->get($this->tb_files, "WHERE lang='{$this->lang}' AND parent_id='$postID' ORDER BY ord ");

            if ( $this->language == 'vn' ) {
                $post['title'] = $post['title_vn'];
                $post['quote'] = $post['quote_vn'];
                $post['content'] = $post['content_vn'];
                $post['inventory'] = $post['inventory_vn'];
                $post['address'] = $post['address_vn'];
                $post['cat_title'] = $cat['title_vn'];
                $post['service'] = $post['service_info_vn'];
            } else {
                $post['title'] = $post['title_en'];
                $post['quote'] = $post['quote_en'];
                $post['content'] = $post['content_en'];
                $post['inventory'] = $post['inventory_en'];
                $post['address'] = $post['address_en'];
                $post['cat_title'] = $cat['title_en'];
                $post['service'] = $post['service_info_en'];
            }
            $this->view->post = $post;
            $this->view->date = $this->view->post['date'];
            $this->view->title = $cat['title'];
            $this->view->title_post = $post['title'];
            $this->view->currentCat = $cat['ID'];
            $this->view->olderPosts = $this
                    ->Model
                    ->get($this->tb_post, "WHERE date <= {$post['date']} AND lang='{$this->lang}' AND status<>0 LIMIT $limit_normal");

            $this->view->newerPosts = $this
                    ->Model
                    ->queryAll("SELECT *,
                        IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`,
                        IF('{$this->language}'='vn',`quote_vn`,`quote_en`) as `quote`,
                        IF('{$this->language}'='vn',`content_vn`,`content_en`) as `content`
						FROM {$this->tb_post}
						WHERE date <= {$this->Plugins->time()} AND lang='{$this->lang}'  ORDER BY  date DESC LIMIT 6");
        } else if ( $catID != 0 ) {
            $cat = $this
                    ->Model
                    ->getOne($this->tb_cat, "WHERE ID='$catID' AND lang='{$this->lang}'");
            if ( !$cat ) {
                $this->_redirect($this->view->moduleUrl);
                return false;
            }
            $idCats=$this->getSubCatID($catID);
            $this->view->title = $cat['title'];
            $posts = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,
                        IF('{$this->language}'='vn',`a`.`title_vn`,`a`.`title_en`) as `title`,
                        IF('{$this->language}'='vn',`a`.`quote_vn`,`a`.`quote_en`) as `quote`,
                        IF('{$this->language}'='vn',`a`.`content_vn`,`a`.`content_en`) as `content`,
                        IF('{$this->language}'='vn',`b`.`title_vn`,`b`.`title_en`) as `cat_title`
						FROM {$this->tb_post} as `a`
                               LEFT JOIN {$this->tb_cat} as `b`
                                   ON `a`.`parent_id`=`b`.`ID`
						WHERE `a`.`lang`='{$this->lang}'
                               AND `a`.`parent_id` IN (".  implode(', ', $idCats).")
                               AND `a`.`date` <= {$this->Plugins->time()} ORDER BY `a`.`date` DESC,`a`.`ID`");
            if($posts){
                foreach($posts as &$post){
                    $post['imgs']= $this
                    ->Model
                    ->get($this->tb_files, "WHERE lang='{$this->lang}' AND `parent_id`='{$post['ID']}' ORDER BY ord ");
                }
            }
            $this->view->posts=$posts;
            $this->view->filess = $this
                    ->Model
                    ->get($this->tb_files, "WHERE lang='{$this->lang}' ORDER BY ord ");
            $this->view->title1 = $cat['title'];
            $this->view->currentCat = $catID;
            $this->view->this_cat = $catID;

        } else {
            $posts = $this
				->Model
				->queryAll("SELECT *,
                    IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`,
                        IF('{$this->language}'='vn',`quote_vn`,`quote_en`) as `quote`,
                        IF('{$this->language}'='vn',`content_vn`,`content_en`) as `content`,
					(CASE WHEN sticky > 0 THEN sticky ELSE 'z' END ) as idx
					FROM {$this->tb_post}
					WHERE  status<>0 AND date <= {$this->Plugins->time()} ORDER BY idx, date DESC LIMIT $limit" );
			if($posts){
                foreach($posts as &$post){
                    $post['imgs']= $this
                    ->Model
                    ->get($this->tb_files, "WHERE lang='{$this->lang}' AND `parent_id`='{$post['ID']}' ORDER BY ord ");
                }
            }
            $this->view->posts=$posts;
            //build page bar
			$total = $this
				->Model
				->getTotal( $this->tb_post,"WHERE  status<>0 AND date <= {$this->Plugins->time()}");
            $this->view->currentPage=$current;
            $this->view->totalPage=ceil( $total/$config['post_number'] );
			$this->view->pageBar = $this
				->Plugins
				->getPageBarDiv(($this->language == "vn" ? 'thu-vien-anh' : 'Gallery')."/?p=",$current,$total,$config['post_number'],$config['next_number'],true);
        }

    }

    private function getSubCatID( $root_id, $contains = true, $deep = 0 ) {
        $result = array();
        if ( $deep > 10 )
            return $result;
        if ( $contains ) {
            $result[] = $root_id;
        }

        $posts = $this
                ->Model
                ->get($this->tb_cat);
        if ( $posts ) {
            foreach ( $posts as $a ) {
                if ( $a['parent_id'] == $root_id ) {
                    $result = array_merge($result, self::getSubCatID($a['ID'], true, $deep + 1));
                }
            }
        }

        return array_unique($result);

    }

    public function downloadAction() {
        $download_file = "upload/gallery/" . $_GET['f'];
        $download_file_name = $_GET['f'];
        $handle = fopen($download_file, "r");
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $download_file_name);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($download_file));
        ob_clean();
        flush();
        readfile($download_file);
        fclose($handle);
        exit;

    }

}
