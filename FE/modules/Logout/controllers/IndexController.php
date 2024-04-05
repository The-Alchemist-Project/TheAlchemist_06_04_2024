<?php

class Logout_IndexController extends Zend_Controller_Action {

    var $tb_post = "customer";
    var $tb_cat = "customer_type";
    var $tb_config = "customer_config";

    public function init() {
        if (!$_SESSION['cp__customer'])
            $this->_redirect(BASE_URL . "/{$this->language}");
    }

    public function indexAction() {
        unset($_SESSION['cp__customer']);
        $this->_redirect(BASE_URL . "/{$this->language}");
    }

}
