<?php

class News_IndexController extends Zend_Controller_Action {

    var $tb_post = "tb_news_post";
    var $tb_cat = "tb_news_cat";
    var $tb_config = "tb_news_config";

    public function init() {

        $this->view->info = $this->Model->getOne("tb_contact");
        $this->view->cats = $this
                ->Model
                ->get($this->tb_cat, "ORDER BY ord ");
    }

    public function index2Action() {
        $url = 'https://cointelegraph.com/news/bitcoin-still-on-track-to-reach-100k-by-2023-says-bitbull-capital-ceo';

        $ch = curl_init();

        $timeout = 5; // thời gian đợi để lấy dữ liệu

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        $lines_string = curl_exec($ch); // lấy nội dung theo URL

        curl_close($ch); // giải phóng tài liệu sau khi lấy dữ liệu

        echo $lines_string; // hiển thị dữ liệu
        die('1111');
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
            $cat = $this
                    ->Model
                    ->getOne($this->tb_cat, "WHERE ID='$catID'");
            if (!$cat) {
                $this->_redirect($this->view->moduleUrl);
                return false;
            }
            $this->view->meta = $meta;
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
                    ->getPageBarDiv(($this->language == "vn" ? 'tin-tuc' : 'News') . "/?p=", $current, $total, $config['post_number'], $config['next_number'], true);
        }
    }

}
