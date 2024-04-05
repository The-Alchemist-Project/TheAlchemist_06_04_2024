<?php

class Admin_ColorController extends Zend_Controller_Action {

    var $tb_post = "tb_product_color";
    var $dirImage = "files/images/color/";

    public function init() {
        $this->view->dirImage = $this->dirImage;
        //makedir
        if (!is_dir($this->dirImage)) {
            $this->Plugins->makedir($this->dirImage);
        }
    }

    public function indexAction() {
        $this->view->posts = $this->Plugins->orderForCats(array(
            items => $this->Model->get($this->tb_post, "WHERE lang='{$_SESSION['cp_lang']}' ORDER BY ord")
        ));
    }

    public function addAction() {
        $lang = $_SESSION['cp_lang'];

        if ($this->isPost) {
            $title = $this->Plugins->get("title", "Không có tiêu đề");
            if ($title == "") {
                $title = "Không có tiêu đề";
            }
            $ord = $this->Plugins->getNum("ord", '');
            $this->view->title = $title;
            $this->view->ord = $ord;


            $file = @$_FILES['img'];
            $file_upload = "";
            if ($file && ( $file['tmp_name'] !== "" )) {
                $file_upload = $this->Plugins->uploadImage($this->dirImage, $file);
                if (is_int($file_upload)) {
                    $this->view->message = "ERROR_UPLOAD_$file_upload";
                    return false;
                }
            }

            $query = $this->Model->insert($this->tb_post, array(
                lang => $lang,
                title => $title,
                img => $file_upload,
                ord => $ord
                    )
            );
            if (isset($_REQUEST['ajax'])) {
                $this->_redirect($this->view->controllerUrl . "/?ajax");
            } else {
                $this->_redirect($this->view->controllerUrl);
            }
        }
    }

    public function editAction() {
        $lang = $_SESSION['cp_lang'];
        $id = $this->Plugins->getNum("ID", 0);
        $post = $this->Model->getOne($this->tb_post, "WHERE ID='$id' AND lang='$lang'");
        if (!$post) {
            $this->view->message = "NOT_EXISTS";
            return false;
        }
        $this->view->ID = $id;
        $this->view->title = $post['title'];
        $this->view->content = $post['content'];
        $this->view->quote = $post['quote'];
        $this->view->ord = $post['ord'];
        $this->view->sticky = $post['sticky'];
        $this->view->img = $post['img'];


        if ($this->isPost) {
            $title = $this->Plugins->get("title", $post['title']);
            if ($title == "") {
                $title = "Không có tiêu đề";
            }
            $ord = $this->Plugins->getNum("ord", $post['ord']);

            $this->view->title = $title;
            $this->view->ord = $ord;
            $this->view->img = $post['img'];


            $config_upload = array();
            if (file_exists($this->dirImage . $user['img'])) {
                $config_upload['oldfile'] = $user['img'];
            }
            $file = @$_FILES['img'];
            $file_upload = $post['img'];
            if ($file && ( $file['tmp_name'] !== "" )) {
                $file_upload = $this->Plugins->uploadImage($this->dirImage, $file, $config_upload);
                if (is_int($file_upload)) {
                    $this->view->message = "ERROR_UPLOAD_$file_upload";
                    return false;
                }
            }

            $query = $this->Model->update($this->tb_post, array(
                title => $title,
                img => $file_upload,
                ord => $ord,
                    ), "ID='$id' AND lang='$lang'");

            if (isset($_REQUEST['ajax'])) {
                $this->_redirect($this->view->controllerUrl . "/?ajax");
            } else {
                $this->_redirect($this->view->controllerUrl);
            }
        }
    }

    public function deleteAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $post = $this->Model->getOne($this->tb_post);
        if ($post) {
            $this->Model->delete($this->tb_post, "ID='$id'");
            @unlink($this->dirImage . $post['img']);
        }

        if (isset($_REQUEST['ajax'])) {
            $this->_redirect($this->view->controllerUrl . "/?ajax");
        } else {
            $this->_redirect($this->view->controllerUrl);
        }
    }

    public function upAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $this->Model->up($this->tb_post, $id);
        if (isset($_REQUEST['ajax'])) {
            $this->_redirect($this->view->controllerUrl . "/?ajax");
        } else {
            $this->_redirect($this->view->controllerUrl);
        }
    }

    public function downAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $this->Model->down($this->tb_post, $id);
        if (isset($_REQUEST['ajax'])) {
            $this->_redirect($this->view->controllerUrl . "/?ajax");
        } else {
            $this->_redirect($this->view->controllerUrl);
        }
    }

}
