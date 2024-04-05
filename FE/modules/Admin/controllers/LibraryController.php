<?php

class Admin_LibraryController extends Zend_Controller_Action {

    var $tb_post = "tb_library_post";
    var $tb_config = "tb_library_config";
    var $page = 10;
    var $dirImage = "files/images/library/";

    public function init() {
        $settings = $this->Model->getOne($this->tb_config, "WHERE lang='{$_SESSION['cp_lang']}'");
        $this->view->settings = $settings;
        $this->view->dirImage = $this->dirImage;

        if ( !is_dir($this->dirImage) ) {
            $this->Plugins->makedir($this->dirImage);
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
    public function indexAction() {
        $this->view->posts = $this->Plugins->orderForCats(array(
            items => $this->Model->get($this->tb_post, " ORDER BY ord")
        ));

    }

    public function addAction() {
        //$lang	=	$_SESSION['cp_lang'];
        $lang = 'vn';
        $this->view->catOptions = $this->Plugins->getOptions(array(
            attr => " name='parent_id'",
            items => $this->Model->get($this->tb_post, "WHERE lang='$lang'"),
            selected => $this->Plugins->getNum("parent_id", -1),
            x => "+"
        ));
        if ( $this->isPost ) {
            $title_vn = $this->Plugins->get("title_vn", "Không có tiêu đề");
            $title_en = $this->Plugins->get("title_en", "Không có tiêu đề");
            if ( $title_vn == "" || $title_en == "" ) {
                $title = "Không có tiêu đề";
            }
            $parent_id = $this->Plugins->getNum("parent_id", 0);
            $content_vn = $this->Plugins->getEditor("content_vn", "");
            $content_en = $this->Plugins->getEditor("content_en", "");
            $date = $this->Plugins->get("date", date("Y/m/d H:i:s", $this->Plugins->time()));
            //$quote = $this->Plugins->getEditor("quote", "");
            $ord = $this->Plugins->getNum("ord", $this->Model->getTotal($this->tb_post, "WHERE lang='$lang' AND parent_id='$parent_id'"));


            $this->view->title_vn = $title_vn;
            $this->view->title_en = $title_en;
            $this->view->content_vn = $content_vn;
            $this->view->content_en = $content_en;
            $this->view->date = $date;
            $this->view->parent_id = $parent_id;
            //$this->view->quote = $quote;
            $this->view->ord = $ord;


//            if ( $parent_id != '0' AND ! $this->Model->getOne($this->tb_post, "WHERE lang='$lang' AND ID='$parent_id'") ) {
//                $this->view->message = "ERROR_PARENT_NOT_EXISTS";
//                return false;
//            }
            if ( !$this->Plugins->strtotime($date) ) {
                $this->view->message = "ERROR_DATE";
                return false;
            }
            //now upload img if browser
            $file = @$_FILES['img'];
            $file_upload = "";
            if ( $file && ( $file['tmp_name'] !== "" ) ) {
                $file_upload = $this->Plugins->uploadImage($this->dirImage, $file);
                if ( is_int($file_upload) ) {
                    $this->view->message = "ERROR_UPLOAD_$file_upload";
                    return false;
                }
            }
            $query = $this->Model->insert(
                    $this->tb_post, array(
                lang => $lang,
                title => $title_vn,
                title_vn => $title_vn,
                title_en => $title_en,
                alias => $this->Plugins->createAlias($title),
                content_vn => $content_vn,
                content_en => $content_en,
                date => $this->Plugins->strtotime($date),
                date_post => $this->Plugins->time(),
                //quote => $quote,
                //parent_id => $parent_id,
                user_post => $_SESSION['cp__user']['ID'],
                ord => $ord,
                img => $file_upload
                    )
            );
            if ( isset($_REQUEST['ajax']) ) {
                $this->_redirect($this->view->controllerUrl . "/?ajax");
            } else {
                $this->_redirect($this->view->controllerUrl);
            }

            if ( !$query ) {
                $this->view->message = "ERROR_MYSQL";
                @unlink("$this->dirImage/$file_upload");
            }
        }

    }

    public function editAction() {
        //$lang = $_SESSION['cp_lang'];
        $lang='vn';
        $id = $this->Plugins->getNum("ID", 0);
        $post = $this->Model->getOne($this->tb_post, "WHERE ID='$id'");
        if ( !$post ) {
            $this->view->message = "NOT_EXISTS";
            return false;
        }

        $this->view->ID = $id;
        $this->view->catOptions = $this->Plugins->getOptions(array(
            attr => " name='parent_id'",
            items => $this->Model->get($this->tb_post, "WHERE lang='$lang'"),
            selected => $post['lang'],
            disabled => $id,
            x => "+"
        ));
        $this->view->title = $post['title'];
        $this->view->title_vn = $post['title_vn'];
        $this->view->title_en = $post['title_en'];
        $this->view->parent_id = $post['parent_id'];
        $this->view->content_vn = $post['content_vn'];
        $this->view->content_en = $post['content_en'];
        $this->view->quote = $post['quote'];
        $this->view->ord = $post['ord'];
        $this->view->sticky = $post['sticky'];
        $this->view->img = $post['img'];
        $this->view->date = $date;

        if ( $this->isPost ) {
            $title_vn = $this->Plugins->get("title_vn", $post['title_vn']);
            $title_en = $this->Plugins->get("title_en", $post['title_en']);
            if ( $title_vn == "" || $title_en=="" ) {
                $title = "Không có tiêu đề";
            }
            $parent_id = $this->Plugins->getNum("parent_id", $post['parent_id']);
            $content_vn = $this->Plugins->getEditor("content_vn", $post['content_vn']);
            $content_en = $this->Plugins->getEditor("content_en", $post['content_en']);
            $quote = $this->Plugins->getEditor("quote", $post['quote']);
            $ord = $this->Plugins->getNum("ord", $post['ord']);
            $sticky = $this->Plugins->getNum("sticky", $post['sticky']);
            $date = $this->Plugins->get("date", $this->Plugins->date("Y/m/d H:i:s", $post['date']));


            $this->view->title_vn = $title_vn;
            $this->view->title_en = $title_en;
            $this->view->parent_id = $parent_id;
            $this->view->content_vn = $content_vn;
            $this->view->content_en = $content_en;
            $this->view->quote = $quote;
            $this->view->ord = $ord;
            $this->view->sticky = $sticky;
            $this->view->img = $post['img'];
            $this->view->date = $date;

            if ( $parent_id != '0' AND ! $this->Model->getOne($this->tb_post, "WHERE lang='$lang' AND ID='$parent_id'") ) {
                $this->view->message = "ERROR_PARENT_NOT_EXISTS";
                return false;
            }

            if ( $id == $parent_id ) {
                $this->view->message = "ERROR_PARENT_IS_SELF";
                return false;
            }

            //now upload img if browser
            $config_upload = array();
            if ( file_exists($this->dirImnage . $user['img']) ) {
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
            $query = $this->Model->update(
                    $this->tb_post, array(
                title => $title_vn,
                title_vn => $title_vn,
                title_en => $title_en,
                alias => $this->Plugins->createAlias($title_vn),
                content_vn => $content_vn,
                content_en => $content_en,
                quote => $quote,
                parent_id => $parent_id,
                user_edit => $_SESSION['cp__user']['ID'],
                ord => $ord,
                sticky => $sticky,
                img => $file_upload,
                date => $this->Plugins->strtotime($date),
                date_edit => $this->Plugins->time(),
                    ), "ID='$id' AND lang='$lang'");

            if ( isset($_REQUEST['ajax']) ) {
                $this->_redirect($this->view->controllerUrl . "/?ajax");
            } else {
                $this->_redirect($this->view->controllerUrl);
            }
        }

    }

    public function deleteAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $post = $this->Model->getOne($this->tb_post);
        if ( $post ) {
            $this->Model->delete($this->tb_post, "ID='$id'");
        }

        if ( isset($_REQUEST['ajax']) ) {
            $this->_redirect($this->view->controllerUrl . "/?ajax");
        } else {
            $this->_redirect($this->view->controllerUrl);
        }

    }

    public function upAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $this->Model->up($this->tb_post, $id);
        if ( isset($_REQUEST['ajax']) ) {
            $this->_redirect($this->view->controllerUrl . "/?ajax");
        } else {
            $this->_redirect($this->view->controllerUrl);
        }

    }

    public function downAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $this->Model->down($this->tb_post, $id);
        if ( isset($_REQUEST['ajax']) ) {
            $this->_redirect($this->view->controllerUrl . "/?ajax");
        } else {
            $this->_redirect($this->view->controllerUrl);
        }

    }

}
