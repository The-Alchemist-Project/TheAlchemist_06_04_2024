<?php

class Admin_GalleryController extends Zend_Controller_Action {

    var $tb_post = "tb_gallery_post";
    var $tb_config = "tb_gallery_config";
    var $tb_cat = "tb_gallery_cat";
    var $tb_files = "tb_gallery_files";
    var $tb_user = "tb_user";
    var $page = 10;
    var $dirImage = "files/images/gallery/";
    var $dirFiles = "upload/gallery/";
    var $thumbsImage = "upload/gallery/thumbs/";

    public function init() {
        $this->view->dirImage = $this->dirImage;
        $this->view->dirFiles = $this->dirFiles;
        $this->view->thumbsImage = $this->thumbsImage;
        $aaa = $this->dirFiles;
        $bbb = $this->thumbsImage;
        //createThumbs($aaa,$bbb,230);

        $settings = $this->Model->getOne($this->tb_config, "WHERE lang='{$_SESSION['cp_lang']}'");
        $this->view->settings = $settings;
        //set page default in admin page
        $this->page = $this->Plugins->parseInt($settings['page_admin'], 10);
        //makedir
        if ( !is_dir($this->dirImage) ) {
            $this->Plugins->makedir($this->dirImage);
        }
        if ( !is_dir($this->dirFiles) ) {
            $this->Plugins->makedir($this->dirFiles);
        }

    }

    public function indexAction() {
        if ( isset($_REQUEST['ajax']) ) {
            $this->_redirect("{$this->view->controllerUrl}/cat/?ajax");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/cat");
        }

    }

    public function settingAction() {
        if ( $this->isPost ) {
            $query = $this->Model->update($this->tb_config, array(
                post_number => $this->Plugins->getNum("post_number", "10"),
                next_number => $this->Plugins->getNum("next_number", "10"),
                page_admin => $this->Plugins->getNum("page_admin", "10")
                    ), "lang='{$_SESSION['cp_lang']}'");

            if ( $query ) {
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
        //order all cat
        $cats = $this->Model->get($this->tb_cat, "WHERE $mysql_qr lang='{$_SESSION['cp_lang']}' $sort ORDER BY ord");
        $this->view->cats = $this->Plugins->orderForCats(array(
            items => $cats
        ));

    }

    //add cat action
    public function addcatAction() {
        //$lang = $_SESSION["cp_lang"];
        $lang = 'vn';
        $cats = $this->Model->get($this->tb_cat, "WHERE lang='$lang'");
        $title = $this->Plugins->get("title", "");
        $title_vn = $this->Plugins->get("title_vn", "");
        $title_en = $this->Plugins->get("title_en", "");
        $header = $this->Plugins->getEditor("header", "");
        $footer = $this->Plugins->getEditor("footer", "");
        $parent_id = $this->Plugins->getNum("parent_id", 0);
        $ord = $this->Plugins->getNum("ord", $this->Model->getTotal($this->tb_cat, "WHERE parent_id='$parent_id' AND lang='$lang'") + 1);
        $status = $this->Plugins->getNum("status", 1);
        $post_number = $this->Plugins->getNum("post_number", $this->Plugins->parseInt($this->view->settings['post_number'], 5));
        $next_number = $this->Plugins->getNum("next_number", $this->Plugins->parseInt($this->view->settings['next_number'], 5));

        $this->view->title = $title;
        $this->view->title_vn = $title_vn;
        $this->view->title_en = $title_en;
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

        $file = @$_FILES['img'];
        $file_upload = "";
        if ( $file && ( $file['tmp_name'] !== "" ) ) {
            $file_upload = $this->Plugins->uploadImage($this->dirImage, $file, array(
                resize => array(
                    400,
                    400)
            ));
            if ( is_int($file_upload) ) {
                $this->view->message = "ERROR_UPLOAD_$file_upload";
                return false;
            }
        }

        if ( $this->isPost ) {
            //error tittle
            if ( $title === "" ) {
                $title = "Không có tiêu đề";
            }
            if ( $title_vn == "" ) {
                $this->view->message = "Bạn phải nhập tiêu đề tiếng việt";
                return false;
            }
            //error parent_id
            if ( ( $parent_id == $id ) OR ( $parent_id !== '0' AND ! $this->Model->getOne($this->tb_cat, "WHERE ID='$parent_id' AND lang='$lang'")) ) {
                $this->view->message = "ERROR_CATELOGY_EXISTS";
                return false;
            }

            //insert into data
            $query = $this->Model->insert($this->tb_cat, array(
                img => $file_upload,
                title => $title_vn,
                title_vn => $title_vn,
                title_en => $title_en,
                parent_id => $parent_id,
                status => $status,
                ord => $ord,
                lang => $lang,
                header => $header,
                footer => $footer,
                post_number => $post_number,
                next_number => $next_number
            ));

            if ( isset($_REQUEST['ajax']) ) {
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

        if ( !$cat ) {
            $this->view->message = "ERROR_NOT_EXISTS";
            return false;
        }

        $title = $this->Plugins->get("title", $cat['title']);
        $title_vn = $this->Plugins->get("title_vn", $cat['title_vn']);
        $title_en = $this->Plugins->get("title_en", $cat['title_en']);



        $header = $this->Plugins->getEditor("header", $cat['header']);
        $footer = $this->Plugins->getEditor("footer", $cat['footer']);
        $parent_id = $this->Plugins->getNum("parent_id", $cat['parent_id']);
        $ord = $this->Plugins->getNum("ord", $cat['ord']);
        $status = $this->Plugins->getNum("status", $cat['status']);
        $post_number = $this->Plugins->getNum("post_number", $this->Plugins->parseInt($cat['post_number'], $this->view->settings['post_number'], 10));
        $next_number = $this->Plugins->getNum("next_number", $this->Plugins->parseInt($cat['next_number'], $this->view->settings['next_number'], 10));

        $config_upload = array(
            resize => array(
                800,
                800));
        if ( file_exists($this->dirImage . $user['img']) ) {
            $config_upload['oldfile'] = $user['img'];
        }

        $file = @$_FILES['img'];
        $file_upload = $cat['img'];
        if ( $file && ( $file['tmp_name'] !== "" ) ) {
            $file_upload = $this->Plugins->uploadImage($this->dirImage, $file, $config_upload);
            if ( is_int($file_upload) ) {
                $this->view->message = "ERROR_UPLOAD_$file_upload";
                return false;
            }
        }

        $this->view->ID = $id;
        $this->view->title = $title;
        $this->view->title_vn = $title_vn;
        $this->view->title_en = $title_en;
        $this->view->parent_id = $parent_id;
        $this->view->img = $cat['img'];
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

        if ( $this->isPost ) {
            if ( $title_vn == "" ) {
                $this->view->message = "Bạn phải nhập tiêu đề tiếng việt";
                return false;
            }
            //error parent_id
            if ( ( $parent_id == $id ) OR ( $parent_id !== '0' AND ! $this->Model->getOne($this->tb_cat, "WHERE ID='$parent_id' AND lang='$lang'")) ) {
                $this->view->message = "ERROR_PARENT";
                return false;
            }

            //insert into data
            $query = $this->Model->update($this->tb_cat, array(
                title => $title_vn,
                title_vn => $title_vn,
                title_en => $title_en,
                parent_id => $parent_id,
                status => $status,
                ord => $ord,
                lang => $lang,
                header => $header,
                footer => $footer,
                img => $file_upload,
                next_number => $next_number,
                post_number => $post_number
                    ), "ID='$id'");

            if ( isset($_REQUEST['ajax']) ) {
                $this->_redirect("{$this->view->controllerUrl}/cat/?ajax");
            } else {
                $this->_redirect("{$this->view->controllerUrl}/cat");
            }
        }

    }

    //up cat action
    public function upcatAction() {
        $this->Model->up($this->tb_cat, $this->Plugins->getNum("ID", "0"));
        if ( isset($_REQUEST['ajax']) ) {
            $this->_redirect("{$this->view->controllerUrl}/cat/?ajax");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/cat");
        }

    }

    //dow cat action
    public function downcatAction() {
        $this->Model->down($this->tb_cat, $this->Plugins->getNum("ID", "0"));
        if ( isset($_REQUEST['ajax']) ) {
            $this->_redirect("{$this->view->controllerUrl}/cat/?ajax");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/cat");
        }

    }

    //delete cat action
    public function deletecatAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $cat = $this->Model->getOne($this->tb_cat, "WHERE ID='$id'");
        if ( $cat ) {
            @unlink($this->dirImage . $cat['img']);
            $this->Model->delete($this->tb_cat, "ID='$id'");
            $this->Model->update($this->tb_cat, array(
                'parent_id' => 0), "parent_id='$id'");
            $this->Model->update($this->tb_post, array(
                'parent_id' => 0), "parent_id='$id'");
        }
        if ( isset($_REQUEST['ajax']) ) {
            $this->_redirect("{$this->view->controllerUrl}/cat/?ajax");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/cat");
        }

    }

    //delte post action
    public function deletepostAction() {
        if ( isset($_REQUEST['act_move']) ) {
            $arr = $_REQUEST['ID'];
            $id_cat = $_REQUEST['parent_id'];
            $cat = $this->Model->getOne($this->tb_cat, "WHERE ID='$id_cat'");
            //move if cat exists
            if ( $cat || $id_cat == 0 ) {
                if ( !is_array($arr) ) {
                    $arr = array();
                }
                $brr = array(
                    0,
                    -1);
                foreach ( $arr as $a ) {
                    if ( is_numeric($a) ) {
                        $brr[] = $a;
                    }
                }
                $this->Model->update($this->tb_post, array(
                    'parent_id' => $id_cat), "ID IN (" . implode(',', $brr) . ") ");
            }
        } else {
            $id = $_REQUEST["ID"];
            if ( !is_array($id) ) {
                if ( is_numeric($id) ) {
                    $post = $this->Model->getOne($this->tb_post, "WHERE ID=$id");
                    $post_files = $this->Model->get($this->tb_files, "WHERE parent_id=$id");
                    if ( $post ) {
                        @unlink($this->dirImage . $post['img']);
                        foreach ( $post_files as $files ) {
                            @unlink($this->dirFiles . $files['file']);
                        }
                        $query = $this->Model->delete($this->tb_post, "ID=$id");
                        /* @unlink($this->dirImage.$post['img']);
                          foreach($post_files as $files){
                          @unlink($this->dirFiles.$files['file']);
                          } */
                    }
                }
            } else {
                foreach ( $id as $i ) {
                    if ( !is_numeric($i) )
                        continue;
                    $post = $this->Model->getOne($this->tb_post, " WHERE ID=$i");
                    $post_files = $this->Model->get($this->tb_files, "WHERE parent_id=$id");
                    if ( $post ) {
                        unlink($this->dirImage . $post['img']);
                        $query = $this->Model->delete($this->tb_post, "ID=$i");

                        foreach ( $post_files as $files ) {
                            @unlink($this->dirFiles . $files['file']);
                        }
                    }
                }
            }
        }

        if ( isset($_REQUEST['ajax']) ) {
            $this->_redirect("{$this->view->controllerUrl}/post/?ajax");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/post");
        }

    }

    //index post
    public function postAction() {
        $this->view->postBad = $this
                ->Model
                ->getTotal($this->tb_post, "WHERE parent_id=0 AND lang='{$_SESSION['cp_lang']}'");

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

        $current = $this->Plugins->getCurrentPage();
        $limit = (($current - 1) * ($this->page)) . ',' . ($this->page);
        if ( !isset($_REQUEST['s']) ) {
            //check parent_id
            $qr = $this->Plugins->query("parent_id", "", "&");
            $mysql_qr = $this->Plugins->query("parent_id", "", " AND ");

            $qr.= $qr != "" ? "&p=" : "p=";
            $mysql_qr .= $mysql_qr != "" ? " AND " : "";

            $total_page = $this->Model->getTotal($this->tb_post, "WHERE $mysql_qr lang='{$_SESSION['cp_lang']}'");

            $this->view->totalPost = $total_page;
            $this->view->pageBar = $this->Plugins->getPageBarFull("{$this->view->actionUrl}/?$qr", $current, $total_page, $this->page);

            //query string divice to 2 part
            $query_all = "SELECT post.*,
							(SELECT GROUP_CONCAT(name  COLLATE utf8_general_ci) FROM {$this->tb_user} as user WHERE user.ID=post.user_post LIMIT 0,1 ) as user_post,
							(SELECT GROUP_CONCAT(name  COLLATE utf8_general_ci) FROM {$this->tb_user} as user WHERE user.ID=post.user_edit LIMIT 0,1 ) as user_edit,
							(SELECT GROUP_CONCAT(\"<a href='{$this->view->actionUrl}/?parent_id=\",cat.ID,\"'>\",cat.title COLLATE utf8_general_ci,'</a>') FROM {$this->tb_cat} as cat WHERE lang='{$_SESSION['cp_lang']}' AND post.parent_id=cat.ID LIMIT 0,1) as cat_link
							,( CASE WHEN sticky > 0 THEN sticky ELSE 999999 END ) as idx
							FROM {$this->tb_post} as post
						WHERE $mysql_qr lang='{$_SESSION['cp_lang']}' ORDER BY idx, date DESC LIMIT $limit";

            $this->view->posts = $this->Model->query($query_all);
        } else {
            $word = strtoupper($this->Plugins->getWordSearch("s"));
            $qr = $this->Plugins->query("parent_id", "", "&");
            $mysql_qr = $this->Plugins->query("parent_id", "", " AND ");

            $qr.= "&p=";
            $mysql_qr .= $mysql_qr != "" ? " AND " : "";

            $total_page = $this
                    ->Model
                    ->getTotal($this->tb_post, "WHERE $mysql_qr lang='{$_SESSION['cp_lang']}' AND upper(CONCAT(' ', title,' ',quote,' ',content )) LIKE '%$word%' ");
            $this->view->totalPost = $total_page;
            $this->view->pageBar = $this->Plugins->getPageBarFull("{$this->view->actionUrl}/?s=$word" . $qr, $current, $total_page, $this->page);

            $this->view->posts = $this->Model->queryAll("SELECT post.*,
						(SELECT GROUP_CONCAT(name  COLLATE utf8_general_ci) FROM {$this->tb_user} as user WHERE user.ID=post.user_post LIMIT 0,1 ) as user_post,
						(SELECT GROUP_CONCAT(name  COLLATE utf8_general_ci) FROM {$this->tb_user} as user WHERE user.ID=post.user_edit LIMIT 0,1 ) as user_edit,
						(SELECT GROUP_CONCAT(\"<a href='{$this->view->actionUrl}/?parent_id=\",cat.ID,\"'>\",title COLLATE utf8_general_ci,'</a>') FROM {$this->tb_cat} as cat WHERE post.parent_id=cat.ID ) as cat_link,
						( CASE WHEN sticky > 0 THEN sticky ELSE 999999 END ) as idx
					FROM {$this->tb_post} as post
					WHERE $mysql_qr post.lang='{$_SESSION['cp_lang']}' AND upper(CONCAT(' ', title,' ',quote,' ',content )) LIKE '%$word%' ORDER BY idx, date DESC, title LIMIT $limit");
        }

    }

    //add post action
    public function addpostAction() {
        $lang = $_SESSION['cp_lang'];
        $cats = $this->Model->get($this->tb_cat, "WHERE lang='$lang' ORDER BY title");
        if ( count($cats) == 0 ) {
            $this->view->message = "ERROR_NO_CATELOGY_EXISTS";
            return false;
        }

        $this->view->catOptions = $this->Plugins->getOptions(array(
            items => $cats,
            attr => " name='parent_id'",
            x => "-"
        ));

        $parent_id = $this->Plugins->getNum("parent_id", 0);

        $inventory_vn = $this->Plugins->get("inventory_vn", "");
        $inventory_en = $this->Plugins->get("inventory_en", "");
        $address_vn = $this->Plugins->get("address_vn", "");
        $address_en = $this->Plugins->get("address_en", "");
        $quote_vn = $this->Plugins->getEditor("quote_vn", "");
        $quote_en = $this->Plugins->getEditor("quote_en", "");
        $content_vn = $this->Plugins->getEditor("content_vn", "");
        $content_en = $this->Plugins->getEditor("content_en", "");
        $date = $this->Plugins->get("date", date("Y/m/d H:i:s", $this->Plugins->time()));
        $sticky = $this->Plugins->getNum("sticky", 0);
        $hot = $this->Plugins->getNum("hot", 0);
        $status = $this->Plugins->getNum("status", 1);
        $has_video = $this->Plugins->getNum("has_video", 0);
        $has_img = $this->Plugins->getNum("has_img", 0);
        $title = $this->Plugins->get("title", "");

        $this->view->title = $title;
        $this->view->hot = $hot;
        $this->view->parent_id = $parent_id;
        $this->view->quote = $quote;
        $this->view->content = $content;
        $this->view->date = $date;
        $this->view->sticky = $sticky;
        $this->view->status = $status;
        $this->view->has_video = $has_video;
        $this->view->has_img = $has_img;

        //init post
        if ( $this->isPost ) {
            $title = $this->Plugins->get("title", "Không có tiêu đề");
            if ( $title == "" ) {
                $title = "Không có tiêu đề";
            }
            $title_vn = $this->Plugins->get("title_vn", '');
            $title_en = $this->Plugins->get("title_en", '');
            if ( $title_vn == "" ) {
                $this->view->message = "Bạn phải nhập tiêu đề tiếng việt";
                return false;
            }

            if ( !$this->Plugins->strtotime($date) ) {
                $this->view->message = "ERROR_DATE";
                return false;
            }

            if ( !$this->Model->getOne($this->tb_cat, "WHERE ID='$parent_id'") ) {
                $this->view->message = "ERROR_CATELOGY_NOT_EXISTS";
                return false;
            }

            //now upload img if browser
            $file = @$_FILES['img'];
            $file_upload = "";
            if ( $file && ( $file['tmp_name'] !== "" ) ) {
                $file_upload = $this->Plugins->uploadImage($this->dirImage, $file, array(
                    resize => array(
                        800,
                        800)
                ));
                if ( is_int($file_upload) ) {
                    $this->view->message = "ERROR_UPLOAD_$file_upload";
                    return false;
                }
            }

            //insert into data
            $query = $this->Model->insert($this->tb_post, array(
                title => $title_vn,
                title_vn => $title_vn,
                title_en => $title_en,
                hot => $hot,
                parent_id => $parent_id,
                sticky => $sticky,
                status => $status,
                img => $file_upload,
                quote_vn => $quote_vn,
                quote_en => $quote_en,
                inventory_vn => $inventory_vn,
                inventory_en => $inventory_en,
                address_vn => $address_vn,
                address_en => $address_en,
                content_vn => $content_vn,
                content_en => $content_en,
                date => $this->Plugins->strtotime($date),
                date_post => $this->Plugins->time(),
                lang => $lang,
                has_video => $has_video,
                has_img => $has_img,
                user_post => $_SESSION['cp__user']['ID']
            ));

            if ( !$query ) {
                $this->view->message = "ERROR_MYSQL";
                @unlink("$this->dirImage/$file_upload");
            } else {
                if ( isset($_REQUEST['ajax']) ) {
                    $this->_redirect("{$this->view->controllerUrl}/post/?ajax");
                } else {
                    $this->_redirect("{$this->view->controllerUrl}/post");
                }
            }
        }

    }

    //add post action
    public function editpostAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $post = $this->Model->getOne($this->tb_post, "WHERE ID='$id'");

        if ( !$post ) {
            $this->view->message = "ERROR_NOT_EXISTS";
            return true;
        }
        $cats = $this->Model->get($this->tb_cat, "WHERE lang='{$_SESSION['cp_lang']}' ORDER BY ord,title");

        $title = $this->Plugins->get("title", $post['title']);
        if ( $title == "" ) {
            $title = "Không có tiêu đề";
        }
        $title_vn = $this->Plugins->get("title_vn", $post['title_vn']);
        $title_en = $this->Plugins->get("title_en", $post['title_en']);


        $parent_id = $this->Plugins->getNum("parent_id", 0);
        $inventory_vn = $this->Plugins->get("inventory_vn", $post['inventory_vn']);
        $inventory_en = $this->Plugins->get("inventory_en", $post['inventory_en']);
        $address_vn = $this->Plugins->get("address_vn", $post['address_vn']);
        $address_en = $this->Plugins->get("address_en", $post['address_en']);
        $quote_vn = $this->Plugins->getEditor("quote_vn", $post['quote_vn']);
        $quote_en = $this->Plugins->getEditor("quote_en", $post['quote_en']);
        $content_vn = $this->Plugins->getEditor("content_vn", $post['content_en']);
        $content_en = $this->Plugins->getEditor("content_en", $post['content_en']);
        $date = $this->Plugins->get("date", $this->Plugins->date("Y/m/d H:i:s", $post['date']));
        $sticky = $this->Plugins->getNum("sticky", $post['sticky']);
        $hot = $this->Plugins->getNum("hot", $post['hot']);
        $status = $this->Plugins->getNum("status", $post['status']);
        $has_video = $this->Plugins->getNum("has_video", $post['has_video']);
        $has_img = $this->Plugins->getNum("has_img", $post['has_img']);
        $lang = $this->Plugins->get("lang", $post['lang']);

        $this->view->ID = $id;
        $this->view->title = $title;
        $this->view->title_vn = $title_vn;
        $this->view->title_en = $title_en;
        $this->view->type = $type;
        $this->view->capacity = $capacity;
        $this->view->hot = $hot;
        $this->view->parent_id = $parent_id;
        $this->view->quote_vn = $quote_vn;
        $this->view->quote_en = $quote_en;
        $this->view->content_vn = $content_vn;
        $this->view->content_en = $content_en;
        $this->view->address_vn = $address_vn;
        $this->view->address_en = $address_en;
        $this->view->inventory_vn = $inventory_vn;
        $this->view->inventory_en = $inventory_en;
        $this->view->date = $date;
        $this->view->sticky = $sticky;
        $this->view->status = $status;
        $this->view->img = $post['img'];
        $this->view->has_img = $has_img;
        $this->view->has_video = $has_video;
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
        if ( $this->isPost ) {
            if ( $title_vn == "" ) {
            $this->view->message = "Bạn phải nhập tiêu đề tiếng việt";
            return false;
        }
            if ( !$this->Plugins->strtotime($date) ) {
                $this->view->message = "ERROR_DATE";
                return false;
            }

            if ( !$this->Model->getOne($this->tb_cat, "WHERE lang='{$_SESSION['cp_lang']}' AND ID='$parent_id'") ) {
                $this->view->message = "ERROR_CATELOGY_NOT_EXISTS";
                return false;
            }
            //now upload img if browser
            $config_upload = array(
                resize => array(
                    800,
                    800));
            if ( file_exists($this->dirImage . $user['img']) ) {
                $config_upload['oldfile'] = $user['img'];
            }

            $file = @$_FILES['img'];
            $file_upload = $post['img'];
            if ( $file && ( $file['tmp_name'] !== "" ) ) {
                $file_upload = $this->Plugins->uploadImage($this->dirImage, $file, $config_upload);
                if ( is_int($file_upload) ) {
                    $this->view->message = "ERROR_UPLOAD_$file_upload";
                    return false;
                }
            }

            //die($status);
            $query = $this->Model->update($this->tb_post, array(
                title => $title_vn,
                title_vn => $title_vn,
                title_en => $title_en,
                hot => $hot,
                parent_id => $parent_id,
                sticky => $sticky,
                status => $status,
                img => $file_upload,
                quote_vn => $quote_vn,
                quote_en => $quote_en,
                inventory_vn => $inventory_vn,
                inventory_en => $inventory_en,
                address_vn => $address_vn,
                address_en => $address_en,
                content_vn => $content_vn,
                content_en => $content_en,
                date => $this->Plugins->strtotime($date),
                date_edit => $this->Plugins->time(),
                lang => $lang,
                has_video => $has_video,
                has_img => $has_img,
                user_edit => @$_SESSION['cp__user']['ID']
                    ), "ID=$id");

            if ( !$query ) {
                $this->view->message = "ERROR_MYSQL";
            } else {
                if ( isset($_REQUEST['ajax']) ) {
                    $this->_redirect("{$this->view->controllerUrl}/post/?ajax");
                } else {
                    $this->_redirect("{$this->view->controllerUrl}/post");
                }
            }
        }

    }

    public function filesAction() {
        $lang = $_SESSION['cp_lang'];
        $cats = $this->Model->get($this->tb_post, "WHERE lang='$lang' ORDER BY title");
        $this->view->catOptions = $this->Plugins->getOptions(array(
            items => $cats,
            attr => " name='parent_id'",
            x => "-"
        ));
        $current = $this->Plugins->getCurrentPage();
        $sort = "";
        if ( isset($_REQUEST['parent_id']) ) {
            $sort = " AND parent_id = '{$_REQUEST['parent_id']}' ";
        }
        $limit = (($current - 1) * ($this->page)) . ',' . ($this->page);

        $total_page = $this->Model->getTotal($this->tb_files, "WHERE lang='{$_SESSION['cp_lang']}' $sort");
        $this->view->totalPost = $total_page;
        $this->view->pageBar = $this->Plugins->getPageBarDiv("{$this->view->actionUrl}/?p=", $current, $total_page, $this->page, 10);
        $this->view->posts = $this->Model->query("
				SELECT file.*,
					(SELECT GROUP_CONCAT(\"<a href='{$this->view->actionUrl}/?parent_id=\",post.ID,\"'>\",post.title COLLATE utf8_general_ci,'</a>' SEPARATOR '<br/>' ) FROM {$this->tb_post} as post WHERE post.lang='$lang' AND file.parent_id=post.ID LIMIT 0,1) as post_link
					FROM {$this->tb_files} as file
					WHERE lang='$lang' $sort
					ORDER BY parent_id,ord LIMIT $limit
			");

    }

    //add image action
    public function addfilesAction() {
        $lang = $_SESSION['cp_lang'];
        $cats = $this->Model->get($this->tb_post, "WHERE lang='$lang' ORDER BY title");
        $this->view->catOptions = $this->Plugins->getOptions(array(
            items => $cats,
            attr => " name='parent_id'",
            x => "-"
        ));
        //init post
        if ( $this->isPost ) {
            $parent_id = $this->Plugins->getNum("parent_id", 0);
            //die($parent_id);exit;
            $title = $this->Plugins->get("title", "");
            $ord = $this->Plugins->getNum("ord", $this->Model->getTotal($this->tb_files, "WHERE parent_id='$parent_id' AND lang='$lang'") + 1);
            $this->view->ord = $ord;
            $this->view->title = $title;

            //now upload files if browser
            $file1 = @$_FILES['files1'];
            $file_upload1 = '';
            if ( $file1 && ( $file1['tmp_name'] !== '' ) ) {
                $file_upload1 = $this->Plugins->uploadImage($this->dirFiles, $file1, array(
                    resize => array(
                        800,
                        800)
                ));
                if ( is_int($file_upload1) ) {
                    $this->view->message = "ERROR_UPLOAD_$file_upload1";
                    return false;
                }
            }
            // die($parent_id);
            //insert into data
            $query = $this->Model->insert($this->tb_files, array(
                parent_id => $parent_id,
                file => $file_upload1,
                lang => $lang,
                title => $title,
                ord => $ord,
                user_post => $_SESSION['cp__user']['ID']
            ));
            if ( !$query && isset($file_upload) ) {
                @unlink(($this->dirFiles) . $file_upload);
            }

            if ( $query ) {
                $this->_redirect("{$this->view->controllerUrl}/files/?parent_id=$parent_id");
            } else {
                $this->view->message = "ERROR_MSQL";
            }
        }

    }

    //add image action
    public function editfilesAction() {
        $lang = $_SESSION['cp_lang'];
        $id = $this->Plugins->getNum("ID", "0");

        $post = $this->Model->getOne($this->tb_files, "WHERE ID='$id'");

        //if( !$post ){
        //	$this->view->message	=	"ERROR_NOT_EXISTS";
        //	return true;
        //}

        $cats = $this->Model->get($this->tb_post, "WHERE lang='$lang' ORDER BY title");
        $this->view->catOptions = $this->Plugins->getOptions(array(
            items => $cats,
            attr => " name='parent_id'",
            x => "-",
            selected => $post['parent_id']
        ));

        $parent_id = $this->Plugins->getNum("parent_id", 0);
        $title = $this->Plugins->get("title", $post['title']);
        $ord = $this->Plugins->getNum("ord", $post['ord']);
        $file = $this->Plugins->get("files", $post['file']);

        $this->view->ID = $id;
        $this->view->ord = $ord;
        $this->view->title = $title;
        $this->view->file = $file;



        //init post

        if ( $this->isPost ) {

            $config_upload = array(
                resize => array(
                    800,
                    800));
            if ( file_exists($this->dirFiles . $user['file']) ) {
                $config_upload['oldfile'] = $user['file'];
            }
            $file = @$_FILES['files1'];
            $file_upload = $post['file'];

            if ( $file && ( $file['tmp_name'] !== "" ) ) {
                $file_upload = $this->Plugins->uploadImage($this->dirFiles, $file, $config_upload);
                if ( is_int($file_upload) ) {
                    $this->view->message = "ERROR_UPLOAD_$file_upload";
                    return false;
                }
            }
            //query update
            $query = $this->Model->update($this->tb_files, array(
                parent_id => $parent_id,
                file => $file_upload,
                lang => $lang,
                title => $title,
                ord => $ord,
                user_edit => @$_SESSION['user']['ID'],
                    ), "ID=$id");
             if ( !$query && isset($file_upload) ) {
               @unlink(($this->dirFiles) . $file_upload);
            }
            //direct to index
            if ( $query ) {
                $this->_redirect("{$this->view->controllerUrl}/files/?parent_id=$parent_id");
            } else {
                $this->view->message = "ERROR_MSQL";
            }
        }

    }

    //delte image action
    public function deletefilesAction() {
        $id = $_REQUEST["ID"];
        if ( !is_array($id) ) {
            if ( is_numeric($id) ) {
                $post = $this->Model->getOne($this->tb_files, "WHERE ID=$id");
                if ( $post ) {
                    unlink($this->dirFiles . $post['files']);
                    $query = $this->Model->delete($this->tb_files, "ID=$id");
                }
            }
        } else {
            foreach ( $id as $i ) {
                if ( !is_numeric($i) )
                    continue;
                $post = $this->Model->getOne($this->tb_files, " WHERE ID=$i");
                if ( $post ) {
                    unlink($this->dirFiles . "/" . $post['files']);
                    $query = $this->Model->delete($this->tb_files, "ID=$i");
                }
            }
        }
        $this->_redirect("{$this->view->controllerUrl}/files/?ajax");

    }

    //up cat action
    public function upfilesAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $post = $this
                ->Model
                ->getOne($this->tb_files, "WHERE ID='$id'");
        if ( $post ) {
            $this->Model->up($this->tb_files, $id);
            if ( isset($_REQUEST['ajax']) ) {
                $this->_redirect("{$this->view->controllerUrl}/files/?parent_id={$post['parent_id']}&ajax");
            } else {
                $this->_redirect("{$this->view->controllerUrl}/files/?parent_id={$post['parent_id']}");
            }
        } else {
            if ( isset($_REQUEST['ajax']) ) {
                $this->_redirect("{$this->view->controllerUrl}/files&ajax");
            } else {
                $this->_redirect("{$this->view->controllerUrl}/files");
            }
        }

    }

    //dow cat action
    public function downfilesAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $post = $this
                ->Model
                ->getOne($this->tb_files, "WHERE ID='$id'");
        if ( $post ) {
            $this->Model->down($this->tb_files, $id);
            if ( isset($_REQUEST['ajax']) ) {
                $this->_redirect("{$this->view->controllerUrl}/files/?parent_id={$post['parent_id']}&ajax");
            } else {
                $this->_redirect("{$this->view->controllerUrl}/files/?parent_id={$post['parent_id']}");
            }
        } else {
            if ( isset($_REQUEST['ajax']) ) {
                $this->_redirect("{$this->view->controllerUrl}/files&ajax");
            } else {
                $this->_redirect("{$this->view->controllerUrl}/files");
            }
        }

    }

}
