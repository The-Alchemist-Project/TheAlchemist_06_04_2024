<?php

class Product_IndexController extends Zend_Controller_Action {

    var $tb_post = "tb_product_post";
    var $tb_cat = "tb_product_cat";
    var $tb_config = "tb_product_config";

    public function init() {
		$this->view->info = $this->Model->getOne("tb_contact");
        $this->view->cats = $this
                ->Model
                ->get($this->tb_cat, "WHERE lang='{$this->language}' ORDER BY `ord` ");
        
        $this->view->color = $this->Model->get('tb_product_color', "WHERE `lang`='{$this->language}' ORDER BY `ord`");
    }

    public function indexAction() {
        $this->view->useThumb = 'true';
        $this->view->hoverProject = '_hover';
        $catID = $this->Plugins->getNum('C', 0);
        $postID = $this->Plugins->getNum('ID', 0);
        $colorID = $this->Plugins->getNum('color', 0);
        $config = $this->Model->getOne($this->tb_config, "WHERE lang='{$this->language}'");
        $current = $this->Plugins->getCurrentPage("p");
        $limit_normal = "0," . ($config['post_number'] + 1);
        $limit = ($current - 1) * $config['post_number'] . "," . ($config['post_number']);
        $this->view->date = $this->Plugins->time();
		$info=$this->view->info;
        $meta=array(
            'desc'=>$info['text2'],
            'key'=>$info['text1']
        );
        if ($postID != 0) {
            $post = $this
                    ->Model
                    ->getOne($this->tb_post, "WHERE lang='{$this->language}' AND ID='$postID' AND date <= {$this->Plugins->time()}");
            if (!$post) {
                $this->_redirect($this->view->moduleUrl);
                return false;
            }
            $cat = $this->Model->getOne($this->tb_cat, "WHERE ID='{$post['parent_id']}'");
            if (!$cat) {
                $this->_redirect($this->view->moduleUrl);
                return false;
            }
			if($post['desc']){
                $meta['desc']=$post['desc'];
            }
            if($post['keyword']){
                $meta['key']=$post['keyword'];
            }
            $this->view->meta=$meta;            
            $this->view->catTitle = $cat['title'];
            $this->view->catID = $cat['ID'];
            $this->view->parentCatID = $cat['parent_id'];
            if ($cat['parent_id']) {
                $cat = $this
                        ->Model
                        ->getOne($this->tb_cat, "WHERE lang='{$this->language}' AND `ID`='{$cat['parent_id']}'ORDER BY `ID` LIMIT 1");
                $this->view->parentCatTitle = $cat['title'];
                if ($cat['parent_id']) {
                    $cat = $this
                            ->Model
                            ->getOne($this->tb_cat, "WHERE lang='{$this->language}' AND `ID`='{$cat['parent_id']}'ORDER BY `ID` LIMIT 1");
                }
            }
            
            //
            if($post['color']){
                $color=$this->Model->getOne('tb_product_color',"WHERE `lang`='{$this->language}' AND `ID` IN ('{$post['color']}') ORDER BY `ord`");
                if($color){                    
                    $post['color_title']=  $color['title'];
                }
            }            
            $this->view->post = $post;
            $this->view->colorID = $post['color'];
            $this->view->date = $this->view->post['date'];
            $this->view->title = $cat['title'];           
                    
            $this->view->currentCat = $cat['ID'];
            $this->view->olderPosts = $this
                    ->Model
                    ->get($this->tb_post, "WHERE `ID`<>'$postID' AND parent_id='{$post['parent_id']}' LIMIT 5");


            $this->view->newerPosts = $this
                    ->Model
                    ->queryAll("SELECT *,
						(CASE WHEN sticky > 0 THEN sticky ELSE 'z' END ) as idx
						FROM {$this->tb_post}
						WHERE `ID`<'$postID' AND `parent_id`='{$post['parent_id']}' AND date <= {$this->Plugins->time()} AND lang='{$this->language}' AND `status`='1' "
                                                . "ORDER BY `date` DESC,`ID` DESC LIMIT 10");
        } else if ($catID != 0) {
            $cat = $this
                    ->Model
                    ->getOne($this->tb_cat, "WHERE ID='$catID' AND lang='{$this->language}'");
            if (!$cat) {
                $this->_redirect($this->view->moduleUrl);
                return false;
            }
            $this->view->colorID = $colorID;
            $where="";
            if($colorID){
                $where=" `color`='$colorID' AND ";
                $color=$this->Model->getOne('tb_product_color',"WHERE `lang`='{$this->language}' AND `ID` IN ('{$colorID}') ORDER BY `ord`");
                if($color){                    
                    $this->view->color_title=  $color['title'];
                }
            }
            $this->view->catTitle = $cat['title'];
            $this->view->catID = $cat['ID'];
            $this->view->parentCatID = $cat['parent_id'];
            if ($cat['ID']) {
                $cat = $this
                        ->Model
                        ->getOne($this->tb_cat, "WHERE lang='{$this->language}' AND `ID`='{$cat['ID']}' ORDER BY `ID` LIMIT 1");               
            }
            $this->view->title = $cat['title'];
            $this->view->slide = $this
                    ->Model
                    ->get("tb_slide", "WHERE lang='{$this->language}' ORDER BY ord");
            $this->view->posts = $this
                    ->Model
                    ->queryAll("SELECT *,
                                (CASE WHEN sticky > 0 THEN sticky ELSE 'z' END ) as idx
                                FROM {$this->tb_post}
                                WHERE$where lang='{$this->language}' 
                                            AND parent_id ='{$cat['ID']}' AND `status`='1' AND `date` <= {$this->Plugins->time()} 
                                ORDER BY `date` DESC,`ID` DESC
                                            LIMIT $limit");
            $this->view->title = $cat['title'];
            $this->view->currentCat = $catID;
            $this->view->newerPosts = $this
                    ->Model
                    ->queryAll("SELECT *,
                            (CASE WHEN sticky > 0 THEN sticky ELSE 'z' END ) as idx
                            FROM {$this->tb_post}
                            WHERE date <= {$this->Plugins->time()} AND lang='{$this->language}' AND `status`<>0 
                                ORDER BY idx, date DESC LIMIT $limit_normal");
            //build page bar
            $total = $this
                    ->Model
                    ->getTotal($this->tb_post, "WHERE$where lang='{$this->language}' AND `status`='1' AND parent_id='$catID' AND date <= {$this->Plugins->time()}");
            $this->view->pageBar = $this
                    ->Plugins
                    ->getPageBarDiv($this->language . '/' . ($this->language == 'vn' ? 'san-pham' : 'Product') . "/?C=$catID&p=", $current, $total, $config['post_number'], $config['next_number'], true);
        } else {
            $this->view->slide = $this->Model->get('tb_slide', "WHERE `lang`='{$this->language}' ORDER BY `ord`");
        }
    }

    public function rateAction() {
        $pid = $this->Plugins->get('pid', '');
        $rate = $this->Plugins->get('rate', '');
        $name = $this->Plugins->get('name', '');
        $desc = $this->Plugins->get('desc', '');
        $email = $this->Plugins->get('email', '');
        $error_title = array();
        $error = "";
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
            $error = "Bạn nhập email chưa đúng";
        }
        if ($email === "") {
            $error_title[] = "email";
        }
        if ($name === "") {
            $error_title[] = "tên";
        }
        if (count($error_title) > 0) {
            $error = "Bạn phải nhập " . implode(' và ', $error_title);
        }

        //Insert info             
        if ($error === "") {
            $check = $this->Model->getOne('tb_rates', "WHERE `pid`='$pid' AND `email`='$email'");
            if ($check) {
                $bool = $this->Model->update('tb_rates', array(
                    desc => $desc,
                    rate => $rate,
                    date_created => date('Y-m-d H:i:s'),
                        ), "`ID`='{$check['ID']}'");
            } else {
                $bool = $this->Model->insert('tb_rates', array(
                    name => $name,
                    desc => $desc,
                    email => $email,
                    rate => $rate,
                    pid => $pid,
                    date_created => date('Y-m-d H:i:s'),
                ));
            }
        }
        $suc = "";
        if ($bool) {
            $suc = "Cảm ơn bạn đã đánh giá cho sản phẩm của chúng tôi";
        }
        die(json_encode(array(
            error => $error,
            suc => $suc
        )));
    }

}
