<?php

class User_IndexController extends Zend_Controller_Action {

    var $tb_post = "tb_personnel_post";
    var $tb_cat = "tb_personnel_cat";
    var $tb_config = "tb_personnel_config";

    public function init() {
        if (!$_SESSION['cp__customer']) {
            if ($this->isAjax) {
                http_response_code(401);
                header('Content-Type: application/json');

                echo json_encode(['status' => 'error', 'message' => 'Unauthenticated']);
                exit();
            } else {
                $this->_redirect(BASE_URL . "/{$this->language}/login");
                exit;
            }
        }
    }

    public function indexAction() {
        $catID = $this->Plugins->getNum('C', 0);
        $postID = $this->Plugins->getNum('ID', 0);
        $config = $this->Model->getOne($this->tb_config, "WHERE lang='vn'");
        $current = $this->Plugins->getCurrentPage("p");
        $limit_normal = "0," . ($config['post_number'] + 1);
        $limit = ($current - 1) * $config['post_number'] . "," . ($config['post_number']);
        $this->view->date = $this->Plugins->time();
        $info = $this->view->info;
        $meta = array(
            'desc' => $info['text2'],
            'key' => $info['text1']
        );

        if ($postID != 0) {
            $post = $this
                    ->Model
                    ->getOne($this->tb_post, "WHERE ID='$postID' AND date <= {$this->Plugins->time()}");
            if (!$post) {
                $this->_redirect($this->view->moduleUrl);
                return false;
            }

            $cat = $this->Model->getOne($this->tb_cat, "WHERE ID='{$post['parent_id']}'");
            /* if( !$cat ){
              $this->_redirect( $this->view->moduleUrl );
              return false;
              }
             */
            if ($this->language == 'vn') {
                $post['title'] = $post['title_vn'];
                $post['quote'] = $post['quote_vn'];
                $post['content'] = $post['content_vn'];
            } else {
                $post['title'] = $post['title_en'];
                $post['quote'] = $post['quote_en'];
                $post['content'] = $post['content_en'];
            }
            if ($post['desc']) {
                $meta['desc'] = $post['desc'];
            }
            if ($post['keyword']) {
                $meta['key'] = $post['keyword'];
            }
            $this->view->meta = $meta;
            $this->view->post = $post;
            $this->view->date = $this->view->post['date'];
            $this->view->title = $cat['title'];
            $this->view->currentCat = $cat['ID'];
            $this->view->olderPosts = $this
                    ->Model
                    ->get($this->tb_post, "WHERE date <= {$post['date']} AND status<>0 LIMIT $limit_normal");

            $this->view->newerPosts = $this
                    ->Model
                    ->queryAll("SELECT *,
                        IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`,
                        IF('{$this->language}'='vn',`quote_vn`,`quote_en`) as `quote`,
                        IF('{$this->language}'='vn',`content_vn`,`content_en`) as `content`,
						(CASE WHEN sticky > 0 THEN sticky ELSE '9999999999' END ) as idx
						FROM {$this->tb_post}
						WHERE date <= {$this->Plugins->time()}  AND status<>0 ORDER BY idx, date DESC LIMIT $limit_normal");
        } else if ($catID != 0) {
            $this->view->meta = $meta;
            $cat = $this
                    ->Model
                    ->getOne($this->tb_cat, "WHERE ID='$catID'");
            if (!$cat) {
                $this->_redirect($this->view->moduleUrl);
                return false;
            }

            $this->view->title = $cat['title'];
            $this->view->posts = $this
                    ->Model
                    ->queryAll("SELECT *,
                        IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`,
                        IF('{$this->language}'='vn',`quote_vn`,`quote_en`) as `quote`,
                        IF('{$this->language}'='vn',`content_vn`,`content_en`) as `content`,
						(CASE WHEN sticky > 0 THEN sticky ELSE 'z' END ) as idx
						FROM {$this->tb_post}
						WHERE  parent_id='$catID' AND status<>0 AND date <= {$this->Plugins->time()} ORDER BY idx ASC, date DESC,date DESC LIMIT $limit");
            $this->view->title = $cat['title'];
            $this->view->currentCat = $catID;
            //Download thông tin ứng viên
            if (trim($cat['title']) == 'Thông tin ứng viên') {
                $file_path = "files/images/personnel/JobApplication.docx";
                if (!file_exists($file_path)) {
                    $this->view->error = "File không tồn tại";
                }
                @header("Content-Type:application/vnd.openxmlformats-officedocument.wordprocessingml.document");
                @header("Content-Disposition: attachment; filename=\"JobApplication.docx\"");
                @readfile($file_path);
                exit;
            }

            //End
            //build page bar
            $total = $this
                    ->Model
                    ->getTotal($this->tb_post, "WHERE  status<>0 AND parent_id='$catID' AND date <= {$this->Plugins->time()}");
            $this->view->pageBar = $this
                    ->Plugins
                    ->getPageBarDiv("{$this->view->moduleUrl}/?C=$catID&p=", $current, $total, $config['post_number'], $config['next_number'], true);
        } else {
            $this->view->meta = $meta;
            $this->view->posts = $this
                    ->Model
                    ->queryAll("SELECT *,
                    IF('{$this->language}'='vn',`title_vn`,`title_en`) as `title`,
                        IF('{$this->language}'='vn',`quote_vn`,`quote_en`) as `quote`,
                        IF('{$this->language}'='vn',`content_vn`,`content_en`) as `content`,
					(CASE WHEN sticky > 0 THEN sticky ELSE 'z' END ) as idx
					FROM {$this->tb_post}
					WHERE  status<>0 AND date <= {$this->Plugins->time()} ORDER BY idx, date DESC LIMIT $limit");
            //build page bar
            $total = $this
                    ->Model
                    ->getTotal($this->tb_post, "WHERE  status<>0 AND date <= {$this->Plugins->time()}");
            $this->view->currentPage = $current;
            $this->view->totalPage = ceil($total / $config['post_number']);
            $this->view->pageBar = $this
                    ->Plugins
                    ->getPageBarDiv("{$this->view->moduleUrl}/?p=", $current, $total, $config['post_number'], $config['next_number'], true);
        }
    }

    public function headerAction() {
        $this->view->moduleTitle1 = $this->_request->getParam('moduleTitle');
        $meta['desc'] = $this->_request->getParam('meta_desc');
        $meta['key'] = $this->_request->getParam('meta_key');
        $this->view->meta = $meta;
    }

    public function contentAction() {
        $posts = $this
                ->Model
                ->queryAll("SELECT *
                FROM `tb_social_post`
                ORDER BY `date_post` DESC LIMIT 100");

        $this->view->posts = $posts;
    }

    public function mobileAction() {
        $posts = $this
                ->Model
                ->queryAll("SELECT *
                FROM `tb_social_post`
                ORDER BY `date_post` DESC LIMIT 100");

        $this->view->posts = $posts;
    }

    public function topAction() {

    }

    public function moreAction() {

    }

    public function itemsAction() {
        $customer = $_SESSION['cp__customer'];
        $posts = $this
                ->Model
                ->queryAll("SELECT `a`.*,`b`.`name` as `customer_name`,`b`.`img`
                FROM `tb_social_post` as `a`
                LEFT JOIN `customer` as `b`
                ON `a`.`user_post`=`b`.`ID`
                WHERE `a`.`user_post`='{$customer['ID']}'
                ORDER BY `a`.`date_post` DESC LIMIT 100");

        if ($posts) {
            $arrPosts = [];
            foreach ($posts as $a) {
                $a['comments'] = [];
                $a['liked'] = false;
                $arrPosts[$a['ID']] = $a;
            }
            $comments = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,`b`.`name` as `customer_name`,`b`.`img`
                    FROM `tb_social_comment` as `a`
                        LEFT JOIN `customer` as `b`ON `a`.`user_post`=`b`.`ID`
                        WHERE `post_id` IN (" . implode(',', array_keys($arrPosts)) . ")
                        ORDER BY `a`.`date_post`");

            $arrComments = [];
            if ($comments) {
                foreach ($comments as $a) {
                    $a['liked'] = false;
                    $arrComments[$a['ID']] = $a;
                }
            }

            //Check like
            $likes = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,`b`.`name` as `customer_name`,`b`.`img`
                    FROM `tb_social_like` as `a`
                        LEFT JOIN `customer` as `b`ON `a`.`user_post`=`b`.`ID`
                        WHERE `post_id` IN (" . implode(',', array_keys($arrPosts)) . ")
                        ORDER BY `a`.`date_post`");

            if ($likes) {
                foreach ($likes as $a) {
                    if ($a['type'] == 'CONTENT') {
                        $arrPosts[$a['post_id']]['liked'] = true;
                    } elseif ($a['type'] == 'COMMENT') {
                        $arrComments[$a['sub_id']]['liked'] = true;
                    }
                }
            }

            if ($arrComments) {
                foreach ($arrComments as $a) {
                    $arrPosts[$a['post_id']]['comments'][] = $a;
                }
            }

            $posts = array_values($arrPosts);
        }

        $this->view->posts = $posts;
    }

    public function meAction()
    {
        $customer = $_SESSION['cp__customer'];

        // Query current user then send json response.
        $customer = $this->Model->getOne('customer', "WHERE ID='{$customer['ID']}' LIMIT 1");

        http_response_code(200);
        header('Content-Type: application/json');

        unset($customer['password']);

        echo json_encode([
            'status' => 'success',
            'data' => $customer
        ]);
        exit;
    }
}
