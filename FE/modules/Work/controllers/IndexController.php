<?php

class Work_IndexController extends Zend_Controller_Action {

    var $tb_post = "tb_product_post";
    var $tb_cat = "tb_product_cat";
    var $tb_config = "tb_personnel_config";
    var $tb_product = "tb_product_post";

    public function init() {
        if (!$_SESSION['cp__customer'])
            $this->_redirect(BASE_URL . "/{$this->language}/login");

        $tab = $this->Plugins->get("tab", '');
        $this->view->tab = $tab;
        $this->view->coins = $this
                ->Model
                ->queryAll("SELECT * FROM `stock`");
    }

    public function indexAction() {

    }

    public function headerAction() {
        $this->view->moduleTitle1 = $this->_request->getParam('moduleTitle');
        $meta['desc'] = $this->_request->getParam('meta_desc');
        $meta['key'] = $this->_request->getParam('meta_key');
        $this->view->meta = $meta;
    }

    public function contentAction() {

    }

    public function mobileAction() {

    }

    public function topAction() {

    }

    public function moreAction() {

    }

    public function itemsAction() {
        $customer = $_SESSION['cp__customer'];
        $tab = $this->Plugins->get("tab", 'bs');

        if ($tab == 'bs') {
            $whereTab = "(`method` = 'BASIC' OR `method`='' OR `method` IS NULL)";
        } elseif ($tab == 'pro') {
            $whereTab = "`method` ='PRO' AND  `customer_id`='{$customer['ID']}'";
        } else {
            $whereTab = "`method` ='NORMAL' AND `customer_id`='{$customer['ID']}'";
        }

        $this->view->posts = $this->Model->query("SELECT `a`.*,`b`.`title` as `parent_title`
                            FROM {$this->tb_product} as `a`
                                LEFT JOIN `{$this->tb_cat}` as `b`
                                    ON `a`.`parent_id`=`b`.`ID`
                                WHERE  $whereTab
                                ORDER BY date DESC");
        $this->view->tab = $tab;
    }

    public function formbsAction() {
        $customer = $_SESSION['cp__customer'];
        $cats = $this->Model->get($this->tb_post, "WHERE `method`='PUBLIC' AND `customer_id` IS NULL ORDER BY title");
        if (count($cats) == 0) {
            $this->view->message = "ERROR_NO_CATELOGY_EXISTS";
            return false;
        }
        $postId = $this->Plugins->getNum("ID", 0);
        $post = [];
        if ($postId) {
            $post = $this->Model->getOne($this->tb_post, "WHERE `ID`='{$postId}' AND `customer_id`='{$customer['ID']}'");
        }

        $this->view->catOptions = $this
                ->Plugins
                ->getOptions(array(
            'items' => $cats,
            'attr' => " name='root_id' style='width:260px'",
            'x' => "-",
            'selected' => $post['root_id']
        ));

        $arrAiTrades = [];
        $arrAiTrades[] = [
            'ID' => "1",
            'title' => "RSI7 & Candlestick Model"
        ];
        $arrAiTrades[] = [
            'ID' => "2",
            'title' => "RSI7 & ParabolicSAR Model"
        ];
        $arrAiTrades[] = [
            'ID' => "3",
            'title' => "RSI14 & Candlestick Model"
        ];
        $arrAiTrades[] = [
            'ID' => "4",
            'title' => "RSI14 & ParabolicSAR Model"
        ];
        $arrAiTrades[] = [
            'ID' => "5",
            'title' => "ADX14 & ParabolicSAR Model"
        ];
        $this->view->itemAiTrades = $this
                ->Plugins
                ->getOptions(array(
            'items' => $arrAiTrades,
            'attr' => " name='view_id' style='width:255px'",
            'x' => "",
            'selected' => $post['view_id']
        ));

        $arrRSI = [];
        for ($i = 1; $i <= 4; $i++) {
            $arrRSI[] = [
                'ID' => "{cd{$i}_rsi}",
                'title' => "{cd{$i}_rsi}"
            ];
            $arrRSI[] = [
                'ID' => "{cd{$i}_rsi14}",
                'title' => "{cd{$i}_rsi14}"
            ];
        }
        $this->view->items = $this
                ->Plugins
                ->getOptions(array(
            'items' => $arrRSI,
            'attr' => " name='root_id' style='width:100px'",
            'x' => "",
            'selected' => $post['root_id']
        ));

        $arrAdds = [
            [
                'ID' => '>',
                'title' => '>'
            ],
            [
                'ID' => '<',
                'title' => '='
            ],
            [
                'ID' => '=',
                'title' => '<'
            ]
        ];

        $this->view->additions = $this
                ->Plugins
                ->getOptions(array(
            'items' => $arrAdds,
            'attr' => " name='bs_condition' style='width:50px' ",
            'x' => "",
            'selected' => $post['bs_rsi']
        ));
    }

    public function formAction() {
        $customer = $_SESSION['cp__customer'];
        $cats = $this->Model->get($this->tb_cat, "ORDER BY title");
        if (count($cats) == 0) {
            $this->view->message = "ERROR_NO_CATELOGY_EXISTS";
            return false;
        }
        $postId = $this->Plugins->getNum("ID", 0);
        $copyId = $this->Plugins->getNum("copy_id", 0);
        $post = [];
        if ($postId) {
            $post = $this->Model->getOne($this->tb_post, "WHERE `ID`='{$postId}' AND `customer_id`='{$customer['ID']}'");
        } elseif ($copyId) {
            $post = $this->Model->getOne($this->tb_post, "WHERE `ID`='{$copyId}'");
            unset($post['ID']);
        }

        $arrAiTrades = [];
        $arrAiTrades[] = [
            'ID' => "1",
            'title' => "RSI7 & Candlestick Model"
        ];
        $arrAiTrades[] = [
            'ID' => "2",
            'title' => "RSI7 & ParabolicSAR Model"
        ];
        $arrAiTrades[] = [
            'ID' => "3",
            'title' => "RSI14 & Candlestick Model"
        ];
        $arrAiTrades[] = [
            'ID' => "4",
            'title' => "RSI14 & ParabolicSAR Model"
        ];
        $this->view->itemAiTrades = $this
                ->Plugins
                ->getOptions(array(
            'items' => $arrAiTrades,
            'attr' => " name='view_id' class='selectpicker' ",
            'x' => "",
            'selected' => $post['view_id']
        ));

        $this->view->catOptions = $this
                ->Plugins
                ->getOptions(array(
            items => $cats,
            attr => " name='parent_id'  class='selectpicker' ",
            x => "-",
            selected => $post['parent_id']
        ));

        $parent_id = $this->Plugins->getNum("parent_id", $post['parent_id']);
        $title = $this->Plugins->get("title", $post['title']);
        $desc = $this->Plugins->get("desc", $post['desc']);
        $buylong = $this->Plugins->get("buylong", $post['buylong']);
        $target1 = $this->Plugins->get("target1", $post['target1']);
        $target2 = $this->Plugins->get("target2", $post['target2']);
        $target3 = $this->Plugins->get("target3", $post['target3']);
        $r1 = $this->Plugins->get("r1", $post['r1']);
        $r2 = $this->Plugins->get("r2", $post['r2']);
        $stoploss = $this->Plugins->get("stoploss", $post['stoploss']);

        $this->view->id = $postId;
        $this->view->title = $title;
        $this->view->parent_id = $parent_id;
        $this->view->desc = $desc;
        $this->view->buylong = $buylong;
        $this->view->target1 = $target1;
        $this->view->target2 = $target2;
        $this->view->target3 = $target3;
        $this->view->stoploss = $stoploss;
        $this->view->r1 = $r1;
        $this->view->r2 = $r2;
    }

    public function formproAction() {
        $customer = $_SESSION['cp__customer'];
        $cats = $this->Model->get($this->tb_cat, "ORDER BY title");
        if (count($cats) == 0) {
            $this->view->message = "ERROR_NO_CATELOGY_EXISTS";
            return false;
        }
        $postId = $this->Plugins->getNum("ID", 0);
        $copyId = $this->Plugins->getNum("copy_id", 0);
        $post = [];
        if ($postId) {
            $post = $this->Model->getOne($this->tb_post, "WHERE `ID`='{$postId}' AND `customer_id`='{$customer['ID']}'");
        } elseif ($copyId) {
            $post = $this->Model->getOne($this->tb_post, "WHERE `ID`='{$copyId}'");
            unset($post['ID']);
        }

        $arrAiTrades = [];
        $arrAiTrades[] = [
            'ID' => "1",
            'title' => "RSI7 & Candlestick Model"
        ];
        $arrAiTrades[] = [
            'ID' => "2",
            'title' => "RSI7 & ParabolicSAR Model"
        ];
        $arrAiTrades[] = [
            'ID' => "3",
            'title' => "RSI14 & Candlestick Model"
        ];
        $arrAiTrades[] = [
            'ID' => "4",
            'title' => "RSI14 & ParabolicSAR Model"
        ];

        $arrAiTrades[] = [
            'ID' => "5",
            'title' => "ADX14 & ParabolicSAR Model"
        ];
        $this->view->itemAiTrades = $this
                ->Plugins
                ->getOptions(array(
            'items' => $arrAiTrades,
            'attr' => " name='view_id' class='selectpicker' ",
            'x' => "",
            'selected' => $post['view_id']
        ));

        $this->view->catOptions = $this
                ->Plugins
                ->getOptions(array(
            items => $cats,
            attr => " name='parent_id'  class='selectpicker' ",
            x => "-",
            selected => $post['parent_id']
        ));

        $parent_id = $this->Plugins->getNum("parent_id", $post['parent_id']);
        $title = $this->Plugins->get("title", $post['title']);
        $desc = $this->Plugins->get("desc", $post['desc']);
        $buylong = $this->Plugins->get("buylong", $post['buylong']);
        $target1 = $this->Plugins->get("target1", $post['target1']);
        $target2 = $this->Plugins->get("target2", $post['target2']);
        $target3 = $this->Plugins->get("target3", $post['target3']);
        $r1 = $this->Plugins->get("r1", $post['r1']);
        $r2 = $this->Plugins->get("r2", $post['r2']);
        $stoploss = $this->Plugins->get("stoploss", $post['stoploss']);

        $this->view->id = $postId;
        $this->view->title = $title;
        $this->view->parent_id = $parent_id;
        $this->view->desc = $desc;
        $this->view->buylong = $buylong;
        $this->view->target1 = $target1;
        $this->view->target2 = $target2;
        $this->view->target3 = $target3;
        $this->view->stoploss = $stoploss;
        $this->view->r1 = $r1;
        $this->view->r2 = $r2;
    }

    public function addpostAction() {
        $customer = $_SESSION['cp__customer'];

        $copyId = $this->Plugins->getNum("copy_id", 0);

        $post = $this->Model->getOne($this->tb_post, "WHERE ID='$copyId'");

        $this->view->post = $post;
        $this->view->copyId = $copyId;

        $tab = $this->Plugins->get("tab", 'df');
        $this->view->tab = $tab;
        $view_id = $this->Plugins->get("view_id", "");
        $this->view->view_id = $view_id;

        $coinIds = $this->Plugins->getArray("coin_ids");
        $this->view->coin_ids = $coinIds;

        if ($tab == 'bs') {
            $root_id = $this->Plugins->getNum("root_id", 0);
            $bs_rsi = $this->Plugins->get("bs_rsi", "");
            $bs_condition = $this->Plugins->get("bs_condition", "");
            $bs_percent = $this->Plugins->get("bs_percent", "");

            $this->view->root_id = $root_id;
            $this->view->bs_rsi = $bs_rsi;
            $this->view->bs_condition = $bs_condition;
            $this->view->bs_percent = $bs_percent;

            if ($this->isPost) {
                if (!$root_id) {
                    $this->view->message = "ERROR_CATELOGY_NOT_EXISTS";
                    return false;
                }

                $parent = $this->Model->getOne($this->tb_post, "WHERE ID='$root_id'");

                $data = $parent;
                unset($data['ID']);

                $data = array_merge($data, [
                    'root_id' => $root_id,
                    'bs_rsi' => $bs_rsi,
                    'view_id' => $view_id,
                    'bs_condition' => $bs_condition,
                    'bs_percent' => $bs_percent,
                    'coin_ids' => $coinIds ? $coinIds : null,
                    'method' => 'BASIC',
                    'customer_id' => $customer['ID']
                ]);
                $post = $this->Model->getOne($this->tb_post, "WHERE `title`='{$data['title']}' AND `parent_id`='{$data['parent_id']}' AND `customer_id`='{$customer['ID']}' AND `root_id`='{$root_id}'");
                if (!$post) {
                    //insert into data
                    $this->Model->insert($this->tb_post, $data);
                }
                $this->_redirect(BASE_URL . "/{$this->language}/work?tab=bs");
            }
        } else {
            $parent_id = $this->Plugins->getNum("parent_id", 0);
            $title = $this->Plugins->get("title", $post['title']);
            $desc = $this->Plugins->get("desc", $post['desc']);
            $buylong = $this->Plugins->get("buylong", $post['buylong']);
            $target1 = $this->Plugins->get("target1", $post['target1']);
            $target2 = $this->Plugins->get("target2", $post['target2']);
            $target3 = $this->Plugins->get("target3", $post['target3']);
            $r1 = $this->Plugins->get("r1", $post['r1']);
            $r2 = $this->Plugins->get("r2", $post['r2']);
            $stoploss = $this->Plugins->get("stoploss", $post['stoploss']);

            $this->view->title = $title;
            $this->view->parent_id = $parent_id;
            $this->view->desc = $desc;
            $this->view->buylong = $buylong;
            $this->view->target1 = $target1;
            $this->view->target2 = $target2;
            $this->view->target3 = $target3;
            $this->view->stoploss = $stoploss;
            $this->view->r1 = $r1;
            $this->view->r2 = $r2;
            $this->view->tab = $tab;

            //init post
            if ($this->isPost) {
                $title = $this->Plugins->get("title", "");
                if ($title == "") {
                    $title = "Name is required";
                }



                $parent = $this->Model->getOne($this->tb_cat, "WHERE ID='$parent_id'");

                if (!$parent) {
                    $this->view->message = "ERROR_CATELOGY_NOT_EXISTS";
                    return false;
                }

                if ($tab == 'pro' && !$coinIds) {
                    $this->view->message = "ERROR_COIN_NOT_EMPTY";
                    return false;
                }

                $desc = $_REQUEST['desc'];

                $data = array(
                    'title' => $title,
                    'view_id' => $view_id,
                    'model_name' => $title,
                    'parent_id' => $parent_id,
                    'parent_title' => $parent['title'],
                    'buylong' => $buylong,
                    'desc' => $desc,
                    'target1' => $target1,
                    'target2' => $target2,
                    'target3' => $target3,
                    'stoploss' => $stoploss,
                    'r1' => $r1,
                    'r2' => $r2,
                    'coin_ids' => $coinIds ? $coinIds : null,
                    'method' => $tab == 'pro' || $coinIds ? 'PRO' : 'NORMAL',
                    'date' => $this->Plugins->time(),
                    'date_post' => $this->Plugins->time(),
                    'lang' => 'vn',
                    'customer_id' => $_SESSION['cp__customer']['ID']
                );

                $post = $this->Model->getOne($this->tb_post, "WHERE `title`='{$title}' AND `parent_id`='{$parent_id}' AND `customer_id`='{$customer['ID']}'");
                if (!$post) {
                    //insert into data
                    $this->Model->insert($this->tb_post, $data);
                }

                if ($data['method'] == 'PRO') {
                    $tab = 'pro';
                }

                $this->_redirect(BASE_URL . "/{$this->language}/work?tab=$tab");
            }
        }
    }

    public function editpostAction() {
        $postId = $this->Plugins->getNum("ID", 0);
        $tab = $this->Plugins->get("tab", 'df');

        $post = $this->Model->getOne($this->tb_post, "WHERE ID='$postId'");
        $this->view->id = $postId;

        $this->view->post = $post;

        if ($post['method'] == 'PRO') {
            $tab = 'pro';
        } elseif ($post['method'] == 'BASIC') {
            $tab = 'bs';
        } else {
            $tab = 'df';
        }


        $this->view->tab = $tab;

        $view_id = $this->Plugins->get("view_id", $post['view_id']);
        $this->view->view_id = $view_id;
        $coinIds = $this->Plugins->getArray("coin_ids", $post['coin_ids']);

        $this->view->coin_ids = $coinIds;

        if ($tab == 'bs') {
            $root_id = $this->Plugins->getNum("root_id", $post['root_id']);
            $bs_rsi = $this->Plugins->get("bs_rsi", $post['bs_rsi']);
            $bs_condition = $this->Plugins->get("bs_condition", $post['bs_condition']);
            $bs_percent = $this->Plugins->get("bs_percent", $post['bs_percent']);
            $this->view->root_id = $root_id;
            $this->view->bs_rsi = $bs_rsi;
            $this->view->bs_condition = $bs_condition;
            $this->view->bs_percent = $bs_percent;

            if ($this->isPost) {
                if (!$root_id) {
                    $this->view->message = "ERROR_CATELOGY_NOT_EXISTS";
                    return false;
                }

                $parent = $this->Model->getOne($this->tb_post, "WHERE ID='$root_id'");

                $data = $parent;
                unset($data['ID']);

                $data = array_merge($data, [
                    'root_id' => $root_id,
                    'bs_rsi' => $bs_rsi,
                    'bs_condition' => $bs_condition,
                    'bs_percent' => $bs_percent,
                    'view_id' => $view_id,
                    'coin_ids' => $coinIds ? $coinIds : null,
                    'method' => 'BASIC'
                ]);

                $this->Model->update($this->tb_post, $data, "`ID`='{$postId}'");

                $this->_redirect(BASE_URL . "/{$this->language}/work?tab=bs");
            }
        } else {
            $parent_id = $this->Plugins->getNum("parent_id", $post['parent_id']);
            $title = $this->Plugins->get("title", $post['title']);
            $desc = $this->Plugins->get("desc", $post['desc']);
            $buylong = $this->Plugins->get("buylong", $post['buylong']);
            $target1 = $this->Plugins->get("target1", $post['target1']);
            $target2 = $this->Plugins->get("target2", $post['target2']);
            $target3 = $this->Plugins->get("target3", $post['target3']);
            $r1 = $this->Plugins->get("r1", $post['r1']);
            $r2 = $this->Plugins->get("r2", $post['r2']);
            $stoploss = $this->Plugins->get("stoploss", $post['stoploss']);

            $this->view->title = $title;
            $this->view->parent_id = $parent_id;
            $this->view->desc = $desc;
            $this->view->buylong = $buylong;
            $this->view->target1 = $target1;
            $this->view->target2 = $target2;
            $this->view->target3 = $target3;
            $this->view->stoploss = $stoploss;
            $this->view->r1 = $r1;
            $this->view->r2 = $r2;

            //init post
            if ($this->isPost) {
                $title = $this->Plugins->get("title", "");
                if ($title == "") {
                    $title = "Name is required";
                }



                $parent = $this->Model->getOne($this->tb_cat, "WHERE ID='$parent_id'");

                if (!$parent) {
                    $this->view->message = "ERROR_CATELOGY_NOT_EXISTS";
                    return false;
                }

                if ($tab == 'pro' && !$coinIds) {
                    $this->view->message = "ERROR_COIN_NOT_EMPTY";
                    return false;
                }


                $desc = $_REQUEST['desc'];

                $data = array(
                    'title' => $title,
                    'model_name' => $title,
                    'parent_id' => $parent_id,
                    'parent_title' => $parent['title'],
                    'buylong' => $buylong,
                    'desc' => $desc,
                    'target1' => $target1,
                    'target2' => $target2,
                    'target3' => $target3,
                    'stoploss' => $stoploss,
                    'r1' => $r1,
                    'r2' => $r2,
                    'view_id' => $view_id,
                    'coin_ids' => $coinIds ? $coinIds : null,
                    'method' => $tab == 'pro' || $coinIds ? 'PRO' : 'NORMAL',
                    'date' => $this->Plugins->time(),
                    'date_post' => $this->Plugins->time(),
                    'lang' => 'vn',
                    'customer_id' => $_SESSION['cp__customer']['ID']
                );

                //insert into data
                $this->Model->update($this->tb_post, $data, "`ID`='{$postId}'");

                $this->view->message = "Successful";
                if ($tab == 'pro' || $coinIds) {
                    $this->_redirect(BASE_URL . "/{$this->language}/work?tab=pro");
                } else {
                    $this->_redirect(BASE_URL . "/{$this->language}/work");
                }
            }
        }
    }

    public function deletepostAction() {
        $customer = $_SESSION['cp__customer'];
        $id = $_REQUEST["ID"];
        if (!is_array($id)) {
            if (is_numeric($id)) {
                $post = $this->Model->getOne($this->tb_post, "WHERE `ID`='$id' AND `customer_id`='{$customer['ID']}'");
                if ($post) {
                    $query = $this->Model->delete($this->tb_post, "`ID`='$id' AND `customer_id`='{$customer['ID']}'");
                }
            }
        } else {
            foreach ($id as $i) {
                if (!is_numeric($i))
                    continue;
                $post = $this->Model->getOne($this->tb_post, " WHERE  `ID`='$id' AND `customer_id`='{$customer['ID']}'");
                if ($post) {
                    $query = $this->Model->delete($this->tb_post, "`ID`='$id' AND `customer_id`='{$customer['ID']}'");
                }
            }
        }

        $this->_redirect(BASE_URL . "/{$this->language}/work");
    }

    public function faqAction() {

    }

}
