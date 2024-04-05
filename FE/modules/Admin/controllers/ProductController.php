<?php

class Admin_ProductController extends Zend_Controller_Action {

    var $tb_post = "tb_product_post";
    var $tb_config = "tb_product_config";
    var $tb_cat = "tb_product_cat";
    var $tb_user = "tb_user";
    var $page = 10;
    var $dirImage = "files/images/product/";
    var $dirImageThumb = "files/images/product/thumb";

    public function init() {
        $this->view->dirImage = $this->dirImage;
        $settings = $this->Model->getOne($this->tb_config, "WHERE lang='{$_SESSION['cp_lang']}'");
        $this->view->search_color = $this->Model->get('tb_product_color', "WHERE lang='{$_SESSION['cp_lang']}'");
        $this->view->settings = $settings;
        //set page default in admin page
        $this->page = $this->Plugins->parseInt($settings['page_admin'], 10);
        //makedir
        if (!is_dir($this->dirImage)) {
            $this->Plugins->makedir($this->dirImage);
            $this->Plugins->makedir($this->dirImageThumb);
        }
    }

    public function indexAction() {
        if (isset($_REQUEST['ajax'])) {
            $this->_redirect("{$this->view->controllerUrl}/post/?ajax");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/post");
        }
    }

    public function settingAction() {
        if ($this->isPost) {
            $query = $this->Model->update($this->tb_config, array(
                post_number => $this->Plugins->getNum("post_number", "10"),
                next_number => $this->Plugins->getNum("next_number", "10"),
                page_admin => $this->Plugins->getNum("page_admin", "10")
                    ), "lang='{$_SESSION['cp_lang']}'");

            if ($query) {
                $this->_redirect($this->view->actionUrl);
            } else {
                $this->view->message = "ERROR_MSQL";
            }
        }
    }

    //show list cat
    public function catAction() {
        $mysql_qr = $this->Plugins->query("parent_id", "", " AND ");
        $mysql_qr = $mysql_qr != "" ? " AND " : "";
        $word = strtoupper($this->Plugins->getWordSearch("s"));
        $where = "";
        if ($word != '') {
            $where = " `title` like '%$word%' AND ";
        }
        //order all cat
        $cats = $this->Model->get($this->tb_cat, "WHERE$where $mysql_qr lang='{$_SESSION['cp_lang']}' $sort ORDER BY ord");
        $this->view->cats = $this->Plugins->orderForCats(array(
            items => $cats
        ));
    }

    //add cat action
    public function addcatAction() {
        $lang = $_SESSION["cp_lang"];
        $cats = $this->Model->get($this->tb_cat, "WHERE lang='$lang'");
        $title = $this->Plugins->get("title", "");
        $header = $this->Plugins->getEditor("header", "");
        $footer = $this->Plugins->getEditor("footer", "");
        $parent_id = $this->Plugins->getNum("parent_id", 0);
        $ord = $this->Plugins->getNum("ord", $this->Model->getTotal($this->tb_cat, "WHERE parent_id='$parent_id' AND lang='$lang'") + 1);
        $status = $this->Plugins->getNum("status", 1);
        $post_number = $this->Plugins->getNum("post_number", $this->Plugins->parseInt($this->view->settings['post_number'], 5));
        $next_number = $this->Plugins->getNum("next_number", $this->Plugins->parseInt($this->view->settings['next_number'], 5));

        $this->view->title = $title;
        $this->view->parent_id = $parent_id;
        $this->view->ord = $ord;
        $this->view->status = $status;
        $this->view->header = $header;
        $this->view->footer = $footer;
        $this->view->post_number = $post_number;
        $this->view->next_number = $next_number;

        $this->view->catOptions = $this->Plugins->getOptions(array(
            items => $cats,
            attr => " name='parent_id'",
            x => "-",
            selected => $parent_id
        ));
        if ($this->isPost) {
            //error tittle
            if ($title === "") {
                $title = "Không có tiêu đề";
            }
            //error parent_id
            if (( $parent_id == $id ) OR ( $parent_id !== '0' AND!$this->Model->getOne($this->tb_cat, "WHERE ID='$parent_id' AND lang='$lang'"))) {
                $this->view->message = "ERROR_CATELOGY_EXISTS";
                return false;
            }
            //insert into data
            $query = $this->Model->insert($this->tb_cat, array(
                title => $title,
                parent_id => $parent_id,
                status => $status,
                ord => $ord,
                lang => $lang,
                header => $header,
                footer => $footer,
                post_number => $post_number,
                next_number => $next_number
            ));

            if (isset($_REQUEST['ajax'])) {
                $this->_redirect("{$this->view->controllerUrl}/cat/?ajax");
            } else {
                $this->_redirect("{$this->view->controllerUrl}/cat");
            }
        }
    }

    //edit user action
    public function editcatAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $lang = $_SESSION["cp_lang"];
        $cat = $this->Model->getOne($this->tb_cat, "WHERE ID='$id' AND lang='$lang'");

        if (!$cat) {
            $this->view->message = "ERROR_NOT_EXISTS";
            return false;
        }

        $title = $this->Plugins->get("title", $cat['title']);
        if ($title == "") {
            $title = "Không có tiêu đề";
        }

        $header = $this->Plugins->getEditor("header", $cat['header']);
        $footer = $this->Plugins->getEditor("footer", $cat['footer']);
        $parent_id = $this->Plugins->getNum("parent_id", $cat['parent_id']);
        $ord = $this->Plugins->getNum("ord", $cat['ord']);
        $status = $this->Plugins->getNum("status", $cat['status']);
        $post_number = $this->Plugins->getNum("post_number", $this->Plugins->parseInt($cat['post_number'], $this->view->settings['post_number'], 10));
        $next_number = $this->Plugins->getNum("next_number", $this->Plugins->parseInt($cat['next_number'], $this->view->settings['next_number'], 10));

        $this->view->ID = $id;
        $this->view->title = $title;
        $this->view->parent_id = $parent_id;
        $this->view->ord = $ord;
        $this->view->status = $status;
        $this->view->header = $header;
        $this->view->footer = $footer;
        $this->view->post_number = $post_number;
        $this->view->next_number = $next_number;

        $cats = $this->Model->get($this->tb_cat, "WHERE lang='$lang'");
        $this->view->catOptions = $this->Plugins->getOptions(array(
            items => $cats,
            attr => " name='parent_id'",
            x => "-",
            selected => $parent_id,
            disabled => $id
        ));

        if ($this->isPost) {
            //error parent_id
            if (( $parent_id == $id ) OR ( $parent_id !== '0' AND!$this->Model->getOne($this->tb_cat, "WHERE ID='$parent_id' AND lang='$lang'"))) {
                $this->view->message = "ERROR_PARENT";
                return false;
            }

            //insert into data
            $query = $this->Model->update($this->tb_cat, array(
                title => $title,
                parent_id => $parent_id,
                status => $status,
                ord => $ord,
                lang => $lang,
                header => $header,
                footer => $footer,
                next_number => $next_number,
                post_number => $post_number
                    ), "ID='$id'");

            if (isset($_REQUEST['ajax'])) {
                $this->_redirect("{$this->view->controllerUrl}/cat/?ajax");
            } else {
                $this->_redirect("{$this->view->controllerUrl}/cat");
            }
        }
    }

    //up cat action
    public function upcatAction() {
        $this->Model->up($this->tb_cat, $this->Plugins->getNum("ID", "0"));
        if (isset($_REQUEST['ajax'])) {
            $this->_redirect("{$this->view->controllerUrl}/cat/?ajax");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/cat");
        }
    }

    //dow cat action
    public function downcatAction() {
        $this->Model->down($this->tb_cat, $this->Plugins->getNum("ID", "0"));
        if (isset($_REQUEST['ajax'])) {
            $this->_redirect("{$this->view->controllerUrl}/cat/?ajax");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/cat");
        }
    }

    //delete cat action
    public function deletecatAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $cat = $this->Model->getOne($this->tb_cat, "WHERE ID='$id'");
        if ($cat) {
            $this->Model->delete($this->tb_cat, "ID='$id'");
            $this->Model->update($this->tb_cat, array('parent_id' => 0), "parent_id='$id'");
            $this->Model->update($this->tb_post, array('parent_id' => 0), "parent_id='$id'");
        }
        if (isset($_REQUEST['ajax'])) {
            $this->_redirect("{$this->view->controllerUrl}/cat/?ajax");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/cat");
        }
    }

    //delte post action
    public function deletepostAction() {
        if (isset($_REQUEST['act_move'])) {
            $arr = $_REQUEST['ID'];
            $id_cat = $_REQUEST['parent_id'];
            $cat = $this->Model->getOne($this->tb_cat, "WHERE ID='$id_cat'");
            //move if cat exists
            if ($cat || $id_cat == 0) {
                if (!is_array($arr)) {
                    $arr = array();
                }
                $brr = array(0, -1);
                foreach ($arr as $a) {
                    if (is_numeric($a)) {
                        $brr[] = $a;
                    }
                }
                $this->Model->update($this->tb_post, array('parent_id' => $id_cat), "ID IN (" . implode(',', $brr) . ") ");
            }
        } else {
            $id = $_REQUEST["ID"];
            if (!is_array($id)) {
                if (is_numeric($id)) {
                    $post = $this->Model->getOne($this->tb_post, "WHERE ID=$id");
                    if ($post) {
                        $query = $this->Model->delete($this->tb_post, "ID=$id");
                        @unlink($this->dirImage . $post['img']);
                    }
                }
            } else {
                foreach ($id as $i) {
                    if (!is_numeric($i))
                        continue;
                    $post = $this->Model->getOne($this->tb_post, " WHERE ID=$i");
                    if ($post) {
                        $query = $this->Model->delete($this->tb_post, "ID=$i");
                        @unlink($this->dirImage . $post['img']);
                    }
                }
            }
        }

        if (isset($_REQUEST['ajax'])) {
            $this->_redirect("{$this->view->controllerUrl}/post/?ajax");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/post");
        }
    }

    //index post
    public function postAction() {
        $this->view->postBad = $this
                ->Model
                ->getTotal($this->tb_post, "WHERE parent_id=0 AND lang='{$_SESSION['cp_lang']}' AND `customer_id` IS NULL");

        $cats = $this->Model->get($this->tb_cat, "WHERE lang='{$_SESSION['cp_lang']}' ORDER BY title");

        $this
                ->view
                ->catOptions = $this
                ->Plugins
                ->getOptions(array(
            items => $cats,
            attr => " class='itext' name='parent_id'",
            x => "-"
        ));
        $status = $this->Plugins->getNum("status", -1);
        $where = "";
        if ($status != -1) {
            $where = " AND `status`='$status'";
        }
        $where = " AND `customer_id` IS NULL";
        $current = $this->Plugins->getCurrentPage();
        $limit = (($current - 1) * ($this->page)) . ',' . ($this->page);
        if (!isset($_REQUEST['s'])) {
            //check parent_id
            $qr = $this->Plugins->query("parent_id", "", "&");
            $mysql_qr = $this->Plugins->query("parent_id", "", " AND ");

            $qr .= $qr != "" ? "&p=" : "p=";
            $mysql_qr .= $mysql_qr != "" ? " AND " : "";

            $total_page = $this->Model->getTotal($this->tb_post, "WHERE $mysql_qr lang='{$_SESSION['cp_lang']}'$where");

            $this->view->totalPost = $total_page;
            $this->view->pageBar = $this->Plugins->getPageBarFull("{$this->view->actionUrl}/?$qr", $current, $total_page, $this->page);

            //query string divice to 2 part
            $query_all = "SELECT post.*,
							(SELECT GROUP_CONCAT(name  COLLATE utf8_general_ci) FROM {$this->tb_user} as user WHERE user.ID=post.user_post LIMIT 0,1 ) as user_post,
							(SELECT GROUP_CONCAT(name  COLLATE utf8_general_ci) FROM {$this->tb_user} as user WHERE user.ID=post.user_edit LIMIT 0,1 ) as user_edit,
							(SELECT GROUP_CONCAT(\"<a href='{$this->view->actionUrl}/?parent_id=\",cat.ID,\"'>\",cat.title COLLATE utf8_general_ci,'</a>') FROM {$this->tb_cat} as cat WHERE lang='{$_SESSION['cp_lang']}' AND post.parent_id=cat.ID LIMIT 0,1) as cat_link
							,( CASE WHEN sticky > 0 THEN sticky ELSE 999999 END ) as idx
							FROM {$this->tb_post} as post
						WHERE $mysql_qr lang='{$_SESSION['cp_lang']}'$where ORDER BY idx, date DESC LIMIT $limit";

            $this->view->posts = $this->Model->query($query_all);
        } else {
            $word = strtoupper($this->Plugins->getWordSearch("s"));
            $qr = $this->Plugins->query("parent_id", "", "&");
            $mysql_qr = $this->Plugins->query("parent_id", "", " AND ");

            $qr .= "&p=";
            $mysql_qr .= $mysql_qr != "" ? " AND " : "";

            $total_page = $this
                    ->Model
                    ->getTotal($this->tb_post, "WHERE $mysql_qr lang='{$_SESSION['cp_lang']}'$where AND upper(CONCAT(' ', title,' ',quote,' ',content )) LIKE '%$word%' ");
            $this->view->totalPost = $total_page;
            $this->view->pageBar = $this->Plugins->getPageBarFull("{$this->view->actionUrl}/?s=$word" . $qr, $current, $total_page, $this->page);

            $this->view->posts = $this->Model->queryAll("SELECT post.*,
						(SELECT GROUP_CONCAT(name  COLLATE utf8_general_ci) FROM {$this->tb_user} as user WHERE user.ID=post.user_post LIMIT 0,1 ) as user_post,
						(SELECT GROUP_CONCAT(name  COLLATE utf8_general_ci) FROM {$this->tb_user} as user WHERE user.ID=post.user_edit LIMIT 0,1 ) as user_edit,
						(SELECT GROUP_CONCAT(\"<a href='{$this->view->actionUrl}/?parent_id=\",cat.ID,\"'>\",title COLLATE utf8_general_ci,'</a>') FROM {$this->tb_cat} as cat WHERE post.parent_id=cat.ID ) as cat_link,
						( CASE WHEN sticky > 0 THEN sticky ELSE 999999 END ) as idx
					FROM {$this->tb_post} as post
					WHERE $mysql_qr post.lang='{$_SESSION['cp_lang']}'$where AND upper(CONCAT(' ', title,' ',quote,' ',content )) LIKE '%$word%' ORDER BY idx, date DESC, title LIMIT $limit");
        }
    }

    //delete img
    public function delimgAction() {
        if (isset($_REQUEST['ID'])) {
            $post = $this->Model->getOne($this->tb_post, "WHERE ID='{$_REQUEST['ID']}' LIMIT 0,1");
            if ($post) {
                $i = $this->Plugins->getNum('i', 0);
                if (isset($post["i{$i}mg"])) {
                    unlink($this->dirImage . "/" . $post["i{$i}mg"]);
                    $this->Model->update($this->tb_post, array("i{$i}mg" => ""), "ID='{$_REQUEST['ID']}'");
                }
            }
        }
        if (isset($_REQUEST['ajax'])) {
            $this->_redirect("{$this->view->controllerUrl}/editpost/?ajax&ID={$_REQUEST['ID']}");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/editpost?ID={$_REQUEST['ID']}");
        }
    }

    //add post action
    public function addpostAction() {
        $lang = $_SESSION['cp_lang'];
        $cats = $this->Model->get($this->tb_cat, "WHERE lang='$lang' ORDER BY title");
        if (count($cats) == 0) {
            $this->view->message = "ERROR_NO_CATELOGY_EXISTS";
            return false;
        }

        $this->view->catOptions = $this
                ->Plugins
                ->getOptions(array(
            items => $cats,
            attr => " name='parent_id'",
            x => "-"
        ));

        $types = [
            [
                'ID' => 'NORMAL',
                'title' => 'Không cho phép'
            ],
            [
                'ID' => 'PUBLIC',
                'title' => 'Cho phép khách hàng tạo nến nhanh'
            ],
        ];

        $this->view->catTypes = $this->Plugins->getOptions(array(
            items => $types,
            attr => " name='method'",
            x => "-"
        ));

        $parent_id = $this->Plugins->getNum("parent_id", 0);
        $title = $this->Plugins->get("title", "");
        $method = $this->Plugins->get("method", "NORMAL");
        $desc = $this->Plugins->get("desc", "");
        $buylong = $this->Plugins->get("buylong", '');
        $target1 = $this->Plugins->get("target1", '');
        $target2 = $this->Plugins->get("target2", '');
        $target3 = $this->Plugins->get("target3", '');
        $r1 = $this->Plugins->get("r1", '');
        $r2 = $this->Plugins->get("r2", '');
        $stoploss = $this->Plugins->get("stoploss", '');
        $link_url = $this->Plugins->get("link_url", '');
        $date = $this->Plugins->get("date", date("Y/m/d H:i:s", $this->Plugins->time()));

        $this->view->title = $title;
        $this->view->parent_id = $parent_id;
        $this->view->method = $method;
        $this->view->desc = $desc;
        $this->view->buylong = $buylong;
        $this->view->target1 = $target1;
        $this->view->target2 = $target2;
        $this->view->target3 = $target3;
        $this->view->stoploss = $stoploss;
        $this->view->link_url = $link_url;
        $this->view->date = $date;
        $this->view->r1 = $r1;
        $this->view->r2 = $r2;

        //init post
        if ($this->isPost) {
            $title = $this->Plugins->get("title", "Không có tiêu đề");
            if ($title == "") {
                $title = "Không có tiêu đề";
            }

            if (!$this->Plugins->strtotime($date)) {
                $this->view->message = "ERROR_DATE";
                return false;
            }

            $parent = $this->Model->getOne($this->tb_cat, "WHERE ID='$parent_id'");

            if (!$parent) {
                $this->view->message = "ERROR_CATELOGY_NOT_EXISTS";
                return false;
            }

            $desc = $_REQUEST['desc'];

            $data = array(
                'title' => $title,
                'model_name' => $title,
                'method' => $method,
                'parent_id' => $parent_id,
                'parent_title' => $parent['title'],
                'buylong' => $buylong,
                'desc' => $desc,
                'target1' => $target1,
                'target2' => $target2,
                'target3' => $target3,
                'stoploss' => $stoploss,
                'link_url' => $link_url,
                'r1' => $r1,
                'r2' => $r2,
                'date' => $this->Plugins->strtotime($date),
                'date_post' => $this->Plugins->time(),
                'lang' => $lang,
                'user_post' => $_SESSION['cp__user']['ID']
            );

            foreach ($_FILES as $k => $file) {
                $link = $this->Plugins->uploadImage($this->dirImage, $file);
                if (!is_int($link))
                    $data[$k] = $link;
            }
            //insert into data
            $query = $this->Model->insert($this->tb_post, $data);
            
            if($query){
                $this->Model->update('stock', [
                        'd1' => 'PENDING',
                        'h4' => 'PENDING',
                    ], "`id` <> 0");
            }

            if (!$query) {
                $this->view->message = "ERROR_MYSQL";
                @unlink("$this->dirImage/$file_upload");
            } else {
                if (isset($_REQUEST['ajax'])) {
                    $this->_redirect("{$this->view->controllerUrl}/post/?ajax");
                } else {
                    $this->_redirect("{$this->view->controllerUrl}/post");
                }
            }
        }
        
    }

    //edit post action
    public function editpostAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $post = $this->Model->getOne($this->tb_post, "WHERE ID='$id'");
        $this->view->id = $id;
        if (!$post) {
            $this->view->message = "ERROR_NOT_EXISTS";
            return true;
        }

        $types = [
            [
                'ID' => 'NORMAL',
                'title' => 'Không cho phép'
            ],
            [
                'ID' => 'PUBLIC',
                'title' => 'Cho phép khách hàng tạo nến nhanh'
            ],
        ];

        $this->view->catTypes = $this->Plugins->getOptions(array(
            items => $types,
            attr => " name='method'",
            x => "-"
        ));

        $cats = $this->Model->get($this->tb_cat, "WHERE lang='{$_SESSION['cp_lang']}' ORDER BY ord,title");

        $title = $this->Plugins->get("title", $post['title']);
        if ($title == "") {
            $title = "Không có tiêu đề";
        }
        $parent_id = $this->Plugins->getNum("parent_id", $post['parent_id']);
        $method = $this->Plugins->get("method", $post['method']);
        $desc = $this->Plugins->get("desc", $post['desc']);
        $date = $this->Plugins->get("date", $this->Plugins->date("Y/m/d H:i:s", $post['date']));
        $lang = $this->Plugins->get("lang", $post['lang']);
        $target1 = $this->Plugins->get("target1", $post['target1']);
        $target2 = $this->Plugins->get("target2", $post['target2']);
        $target3 = $this->Plugins->get("target3", $post['target3']);
        $buylong = $this->Plugins->get("buylong", $post['buylong']);
        $stoploss = $this->Plugins->get("stoploss", $post['stoploss']);
        $link_url = $this->Plugins->get("link_url", $post['link_url']);
        $r1 = $this->Plugins->get("r1", $post['r1']);
        $r2 = $this->Plugins->get("r2", $post['r2']);

        $this->view->ID = $id;
        $this->view->title = $title;
        $this->view->method = $method;
        $this->view->parent_id = $parent_id;
        $this->view->desc = $desc;
        $this->view->buylong = $buylong;
        $this->view->target1 = $target1;
        $this->view->target2 = $target2;
        $this->view->target3 = $target3;
        $this->view->stoploss = $stoploss;
        $this->view->link_url = $link_url;
        $this->view->r1 = $r1;
        $this->view->r2 = $r2;

        $this->view->img = $post['img'];
        $this->view->langOptions = $this
                ->Plugins
                ->langOptions("lang", $lang);
        $this->view->catOptions = $this
                ->Plugins
                ->getOptions(array(
            items => $cats,
            attr => " name='parent_id'",
            x => "-",
            selected => $post['parent_id']
        ));

        //init post
        if ($this->isPost) {
            if (!$this->Plugins->strtotime($date)) {
                $this->view->message = "ERROR_DATE";
                return false;
            }

            $parent = $this->Model->getOne($this->tb_cat, "WHERE lang='{$_SESSION['cp_lang']}' AND ID='$parent_id'");

            if (!$parent) {
                $this->view->message = "ERROR_CATELOGY_NOT_EXISTS";
                return false;
            }
            $desc = $_REQUEST['desc'];

            $data = array(
                'title' => $title,
                'model_name' => $title,
                'method' => $method,
                'parent_id' => $parent_id,
                'parent_title' => $parent['title'],
                'buylong' => $buylong,
                'desc' => $desc,
                'target1' => $target1,
                'target2' => $target2,
                'target3' => $target3,
                'stoploss' => $stoploss,
                'link_url' => $link_url,
                'r1' => $r1,
                'r2' => $r2,
                'date' => $this->Plugins->strtotime($date),
                'date_edit' => $this->Plugins->time(),
                'lang' => $lang,
                'user_post' => $_SESSION['cp__user']['ID']
            );

            foreach ($_FILES as $k => $file) {
                $link = $this->Plugins->uploadImage($this->dirImage, $file);
                if (!is_int($link)) {
                    unlink($this->dirImage, $post[$k]);
                    $data[$k] = $link;
                }
            }

            //die($status);
            $query = $this->Model->update($this->tb_post, $data, "ID=$id");
            
            if($query){
                $this->Model->update('stock', [
                        'd1' => 'PENDING',
                        'h4' => 'PENDING',
                    ], "`id` <> 0");
            }
            
            if (!$query) {
                $this->view->message = "ERROR_MYSQL";
            } else {
                if (isset($_REQUEST['ajax'])) {
                    $this->_redirect("{$this->view->controllerUrl}/post/?ajax");
                } else {
                    $this->_redirect("{$this->view->controllerUrl}/post");
                }
            }
        }
    }

    public function faqAction() {

    }

}
