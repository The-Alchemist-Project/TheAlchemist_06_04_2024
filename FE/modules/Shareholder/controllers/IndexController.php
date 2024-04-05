<?php
class Shareholder_IndexController  extends Zend_Controller_Action {
	var $tb_post		=	"tb_shareholder_post";
	var $tb_cat			=	"tb_shareholder_cat";
	var $tb_config		=	"tb_shareholder_config";
	public function init(){
		$this->view->cats = $this
			->Model
			->get( $this->tb_cat,"ORDER BY ord ");
        $info = $this->Model->getOne("tb_contact");
        $this->view->info=$info;
        $meta=array(
            'desc'=>$info['text2'],
            'key'=>$info['text1']
        );
        $this->view->meta=$meta;
	}

	public function indexAction(){
		$catID  = $this->Plugins->getNum('C',0);
		$postID = $this->Plugins->getNum('ID',0);
		$config = $this->Model->getOne($this->tb_config,"WHERE lang='vn'");
		$current = $this->Plugins->getCurrentPage("p");
		$limit_normal = "0,".($config['post_number']+1);
		$limit= ($current-1)*$config['post_number'].",".($config['post_number']);
		$this->view->date = $this->Plugins->time();
		if( $postID != 0 ){
			$post = $this
				->Model
				->getOne( $this->tb_post,"WHERE ID='$postID' AND date <= {$this->Plugins->time()}" );
			if( !$post ){
				$this->_redirect( $this->view->moduleUrl );
				return false;
			}

			$cat = $this->Model->getOne( $this->tb_cat,"WHERE ID='{$post['parent_id']}'");
			/*if( !$cat ){
				$this->_redirect( $this->view->moduleUrl );
				return false;
			}
			*/
            if($this->language=='vn'){
               $post['title']=$post['title_vn'];
               $post['quote']=$post['quote_vn'];
               $post['content']=$post['content_vn'];
            }else{
                $post['title']=$post['title_en'];
               $post['quote']=$post['quote_en'];
               $post['content']=$post['content_en'];
            }
			$this->view->post = $post;
			$this->view->date = $this->view->post['date'];
			$this->view->title = $cat['title'];
			$this->view->currentCat = $cat['ID'];
			$this->view->olderPosts = $this
				->Model
				->get( $this->tb_post,"WHERE date <= {$post['date']} AND status<>0 LIMIT $limit_normal");

			$this->view->newerPosts = $this
				->Model
				->queryAll("SELECT *,
                        IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`,
                        IF('{$this->language}'='vn',`quote_vn`,`quote_en`) as `quote`,
                        IF('{$this->language}'='vn',`content_vn`,`content_en`) as `content`,
						(CASE WHEN sticky > 0 THEN sticky ELSE '9999999999' END ) as idx
						FROM {$this->tb_post}
						WHERE `status`<>0 ORDER BY idx, `date` DESC LIMIT $limit_normal");

		}else if( $catID != 0 ){
			$cat = $this
				->Model
				->getOne( $this->tb_cat,"WHERE ID='$catID'");
			if( !$cat ){
				$this->_redirect( $this->view->moduleUrl );
				return false;
			}

			$this->view->title = $cat['title'];
			$posts = $this
                        ->Model
                        ->queryAll("SELECT *,
                            IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`,
                                IF('{$this->language}'='vn',`quote_vn`,`quote_en`) as `quote`,
                                IF('{$this->language}'='vn',`content_vn`,`content_en`) as `content`,
                            (CASE WHEN sticky > 0 THEN sticky ELSE 'z' END ) as idx
                            FROM {$this->tb_post}
                            WHERE `parent_id`='{$cat['ID']}'  AND `status` <> 0
                                ORDER BY `year` DESC, idx, date DESC" );
			$arrPosts=array();
            if($posts){
                foreach($posts as $a){
                    $arrPosts[$a['year']][]=$a;
                }
            }
            $cat['posts']=$arrPosts;
            $this->view->title = $cat['title'];
			$this->view->currentCat = $catID;
			$this->view->cat=$cat;
		}else{
            $post=$this
				->Model
				->queryOne("SELECT *,
                    IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`
					FROM {$this->tb_post}
					ORDER BY `ID` DESC LIMIT 1" );
            if($post){
                $cat = $this
				->Model
				->queryOne("SELECT *,
                    IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`
					FROM {$this->tb_cat}
                    WHERE `ID`='{$post['parent_id']}'
					ORDER BY `ord`,`ID` DESC LIMIT 1" );
            }

            if($cat){
                    $posts = $this
                            ->Model
                            ->queryAll("SELECT *,
                                IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`,
                                    IF('{$this->language}'='vn',`quote_vn`,`quote_en`) as `quote`,
                                    IF('{$this->language}'='vn',`content_vn`,`content_en`) as `content`,
                                (CASE WHEN sticky > 0 THEN sticky ELSE 'z' END ) as idx
                                FROM {$this->tb_post}
                                WHERE `parent_id`='{$cat['ID']}' AND `status` <> 0
                                    ORDER BY `year` DESC,  idx, date DESC" );
                $arrPosts=array();
                if($posts){
                    foreach($posts as $a){
                        $arrPosts[$a['year']][]=$a;
                    }
                }
                $cat['posts']=$arrPosts;

            }
            $this->view->cat=$cat;
		}
        if( $this->isPost ){
            $post_id = $this->Plugins->getNum('post_id',0);
            $name= $this->Plugins->get('name','');
            $phone= $this->Plugins->get('phone','');
            $address= $this->Plugins->get('address','');
            $desc= $this->Plugins->get('desc','');
            $error_title=array();
			if( $name === "" ){
				$error_title[] = "tên";
				$error = true;
			}
			if( $phone === "" ){
				$error_title[] = "số điện thoại";
				$error = true;
			}
            if(count($error_title)>0){
                $this->view->error=  "Bạn phải nhập ".implode(' và ', $error_title);
            }
			if( $error == true ){
				$this->view->name = $name;
				$this->view->phone = $phone;
				$this->view->address = $address;
				$this->view->desc = $desc;
				return true;
			}
            $post=$this->Model->getOne("tb_shareholder_post","WHERE `ID`='{$post_id}'");
            if(!$post){
                $this->view->error="File không tồn tại";
            }
            $file_path = "files/images/shareholder/{$post['img']}";

            if (!file_exists($file_path)) {
                $this->view->error="File không tồn tại";
            }
            //Insert info
            $this->Model->insert('tb_shareholder_guest', array(
                name => $name,
                phone => $phone,
                address => $address,
                desc => $desc,
                post_id => $post_id,
                date => date('Y-m-d H:i:s'),
            ));
            //Save file
            @header("Content-Type:{$post['type']}");
            @header("Content-Disposition: attachment; filename=\"{$post['img']}\"");
            @readfile($file_path);
            exit;
        }
	}
}