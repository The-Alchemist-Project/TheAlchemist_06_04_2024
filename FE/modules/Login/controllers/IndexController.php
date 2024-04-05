<?php

class Login_IndexController extends Zend_Controller_Action {

    var $tb_post = "customer";
    var $tb_cat = "customer_type";
    var $tb_config = "customer_config";

    public function init() {

    }

    public function indexAction() {
        if ($this->isPost) {
            $name = $this->Plugins->get("us", "", false);
            $password = $this->Plugins->get("pw", "", false);
            $customer = $this->Model->queryOne("SELECT * FROM `{$this->tb_post}`
                WHERE `status`='1'
                AND (
                    upper(user)='" . strtoupper($name) . "'
                    OR email='" . strtoupper($name) . "'
                    OR phone='" . strtoupper($name) . "'
                    )
                        AND password='" . md5($password) . "'");

            $customer = $this->Model->queryOne("SELECT * FROM `{$this->tb_post}`
                WHERE `status`='1'
                AND (
                    upper(user)='" . strtoupper($name) . "'
                    OR email='" . strtoupper($name) . "'
                    OR phone='" . strtoupper($name) . "'
                    )
                        AND password='" . md5($password) . "'");
            if ($customer) {
                $customer['password'] = "chicken";
                $_SESSION['cp__customer'] = $customer;
                $this->Model->update($this->tb_post, array('visit' => $this->Plugins->time()), "ID='{$customer['ID']}'");

                // Added by CBT
                if ($this->isAjax) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Login successfully',
                    ]);

                    exit();
                }

                $this->_redirect(BASE_URL . "/{$this->language}/trade2");

                $this->view->error = "Acount or password don't active";
            }

            // Added by CBT
            if ($this->isAjax) {
                http_response_code(400);
                header('Content-Type: application/json');

                echo json_encode([
                    'status' => 'error',
                    'message' => 'Current username or password is incorrect',
                ]);

                exit();
            }
        } else {
            // unset($_SESSION['cp__customer']);
        }
    }

}
