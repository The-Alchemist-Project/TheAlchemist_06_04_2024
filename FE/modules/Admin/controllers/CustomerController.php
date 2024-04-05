<?php

class Admin_CustomerController extends Zend_Controller_Action {

    var $tb_customer = "customer";
    var $tb_post = "customer";
    var $tb_config = "customer_config";
    var $tb_user = "tb_user";
    var $page = 10;
    var $dirImage = "files/images/customer/";

    public function init() {
        $this->view->dirImage = $this->dirImage;
        $settings = $this->Model->getOne($this->tb_config, "WHERE lang='{$_SESSION['cp_lang']}'");
        $this->view->settings = $settings;
        //set page default in admin page
        $this->page = $this->Plugins->parseInt($settings['page_admin'], 10);
        //makedir
        if (!is_dir($this->dirImage)) {
            $this->Plugins->makedir($this->dirImage);
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

    public function deletepostAction() {
        $id = $_REQUEST["ID"];
        if (is_array($id)) {
            $id = explode(',', $id);
        }
        $this->Model->delete($this->tb_customer, "ID IN ($id)");

        if (isset($_REQUEST['ajax'])) {
            $this->_redirect("{$this->view->controllerUrl}/post/?ajax");
        } else {
            $this->_redirect("{$this->view->controllerUrl}/post");
        }
    }

    public function postAction() {
        $this->view->postBad = $this
                ->Model
                ->getTotal($this->tb_customer, "WHERE lang='{$_SESSION['cp_lang']}'");

        $current = $this->Plugins->getCurrentPage();
        $limit = (($current - 1) * ($this->page)) . ',' . ($this->page);
        if (!isset($_REQUEST['s'])) {
            //check parent_id
            $qr = $this->Plugins->query("type_id", "", "&");
            $mysql_qr = $this->Plugins->query("type_id", "", " AND ");

            $qr .= $qr != "" ? "&p=" : "p=";
            $mysql_qr .= $mysql_qr != "" ? " AND " : "";

            $total_page = $this->Model->getTotal($this->tb_customer, "WHERE $mysql_qr lang='{$_SESSION['cp_lang']}'");

            $this->view->totalPost = $total_page;
            $this->view->pageBar = $this->Plugins->getPageBarFull("{$this->view->actionUrl}/?$qr", $current, $total_page, $this->page);

            //query string divice to 2 part
            $query_all = "SELECT post.*,`b`.`title` as `type_title`
                    FROM `{$this->tb_customer}` as post
                        LEFT JOIN `customer_type` as `b`
                    ON `post`.`type_id`=`b`.`ID`
						WHERE $mysql_qr post.lang='{$_SESSION['cp_lang']}' ORDER BY date DESC LIMIT $limit";
            //die($query_all);
            $this->view->posts = $this->Model->query($query_all);
        } else {
            $word = strtoupper($this->Plugins->getWordSearch("s"));
            $qr = $this->Plugins->query("type_id", "", "&");
            $mysql_qr = $this->Plugins->query("type_id", "", " AND ");

            if ($qr == 'type_id=0')
                $qr = '';
            if ($mysql_qr == 'type_id=0')
                $mysql_qr = '';

            $qr .= "&p=";
            $mysql_qr .= $mysql_qr != "" ? " AND " : "";

            $total_page = $this
                    ->Model
                    ->getTotal($this->tb_customer, "WHERE $mysql_qr lang='{$_SESSION['cp_lang']}' AND upper(CONCAT(' ', name,' ',email,' ',phone )) LIKE '%$word%' ");
            $this->view->totalPost = $total_page;
            $this->view->pageBar = $this->Plugins->getPageBarFull("{$this->view->actionUrl}/?s=$word" . $qr, $current, $total_page, $this->page);

            $this->view->posts = $this->Model->queryAll("SELECT post.*
                       FROM `{$this->tb_customer}` as post
					WHERE $mysql_qr post.lang='{$_SESSION['cp_lang']}' AND upper(CONCAT(' ', name,' ',email,' ',phone )) LIKE '%$word%' ORDER BY ID, date DESC LIMIT $limit");
        }
    }

    public function viewpostAction() {
        $id = $this->Plugins->getNum("ID", "0");
        $this->view->post = $this->Model->getOne($this->tb_customer, "WHERE ID='$id'");
        $this->view->room = $this->Model->queryAll("SELECT `r`.`title` as name FROM `tb_room_post` as r RIGHT JOIN `room_reg` as rg ON `rg`.`rID`=`r`.`ID` WHERE `rg`.`cID`='$id'");
        //print_r($post['name']);exit;
        if (!$this->view->post) {
            $this->view->message = "ERROR_NOT_EXISTS";
            return true;
        }
    }

    //endcustomer
    public function thumbAction() {
        $file = @$_FILES['img'];
        require_once '../ThumbLib.inc.php';
        $thumb = PhpThumbFactory::create('test.jpg');
        $thumb->resize(100, 100);
        $thumb->show();
    }

    public function editpostAction() {
        //$lang=$_SESSION['cp_lang'];
        $lang = 'vn';
        $id = $this->Plugins->getNum("ID", "0");
        $post = $this->Model->getOne($this->tb_post, "WHERE ID='$id'");

        if (!$post) {
            $this->view->message = "ERROR_NOT_EXISTS";
            return true;
        }

        $status = $this->Plugins->get("status", $post['status']);
        $type_id = $this->Plugins->get("type_id", $post['type_id']);
        $date = $this->Plugins->get("date", date("Y/m/d H:i:s", $this->Plugins->time()));

        $this->view->ID = $id;
        $this->view->status = $status;
        $this->view->type_id = $type_id;
        $this->view->date = $date;

        $this->view->langOptions = $this
                ->Plugins
                ->langOptions("lang", $lang);

        //init post
        if ($this->isPost) {

            if (!in_array($status, ['0', '1', '2'])) {
                $this->view->message = "Trạng thái không đúng";
                return false;
            }
            if (!in_array($type_id, ['1', '2', '3'])) {
                $this->view->message = "Hình thức báo kèo không đúng";
                return false;
            }
            if (!$this->Plugins->strtotime($date)) {
                $this->view->message = "ERROR_DATE";
                return false;
            }

            $query = $this->Model->update($this->tb_post, array(
                'status' => $status,
                'type_id' => $type_id,
                'date' => $date,
                'lang' => $lang,
                    ), "ID=$id");

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

}
