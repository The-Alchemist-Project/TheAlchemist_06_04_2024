<?php

class MyDashboard_IndexController extends Zend_Controller_Action {

    public function init() {
        $customer = $_SESSION['cp__customer'];
//        if ($customer) {
//            if ($customer['type_id'] == 1) {
//                $this->_redirect(BASE_URL . "/{$this->language}/MyDashboard");
//            }
//        }
    }

    public function indexAction() {
        $customer = $_SESSION['cp__customer'];
        $date = date('Y-m-d');
        $ID = $this->Plugins->getNum('ID', 0);
        $symbol = $this->Plugins->get('symbol', "");
        $tab = $this->Plugins->get('tab', "USDT");
        $type = $this->Plugins->get('type', "D1");
        $aclist = $this->Plugins->get('aclist', "pending");
        $study = $this->Plugins->get('study', "");
        if ($type == 'H4') {
            $table = 'stock_histories';
            $table_detail = 'stock_histories';
            $order = 'date_h4';
            $parentTitle = 'H4';
        } else {
            $table = 'stock_histories';
            $order = 'date';
            $parentTitle = 'D1';
            $table_detail = 'stock_price_details';
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

        if ($tab == 'BTC') {
            $symbolS = 'BTC';
        } else {
            $symbolS = 'USDT';
        }

        $wheres = ['1=1'];
        if ($study == 'RSI') {
            $wheres[] = "(`a`.`view_id`='1' OR `a`.`view_id` IS NULL)";
        } elseif ($study == 'RSI,PSAR') {
            $wheres[] = "(`a`.`view_id`='2' OR `a`.`view_id` IS NULL)";
        } elseif ($study == 'RSI14') {
            $wheres[] = "(`a`.`view_id`='3' OR `a`.`view_id` IS NULL)";
        } elseif ($study == 'RSI14,PSAR') {
            $wheres[] = "(`a`.`view_id`='4' OR `a`.`view_id` IS NULL)";
        }

        $strWhere = [];
        if ($aclist == 'win') {
            $strWhere[] = "`a`.`status`='WON'";
        } elseif ($aclist == 'loss') {
            $strWhere[] = "`a`.`status`='FAIL'";
        } elseif ($aclist == 'watch' || $aclist == 'match') {
            $strWhere[] = "`a`.`status` IN ('DOING')";
        } else {
            $strWhere[] = "`a`.`status` IN ('pending','WAITING')";
        }

        $posts = $this
                ->Model
                ->queryAll("SELECT `a`.*,`a`.`id` as `ID`
                FROM `{$table}` as `a`
                WHERE `a`.`symbol` like '%{$symbolS}'
                    AND `customer_id`='{$customer['ID']}'
                        AND  " . implode(' AND ', $strWhere) . "
            ORDER BY `{$order}` DESC LIMIT 100");

        if ($_REQUEST['sql']) {
            debug("SELECT `a`.*,`a`.`id` as `ID`
                FROM `{$table}` as `a`
                WHERE `a`.`symbol` like '%{$symbolS}'
                    AND `customer_id`='{$customer['ID']}'
                        AND  " . implode(' AND ', $strWhere) . "
            ORDER BY `{$order}` DESC LIMIT 100");
        }

        $formulars = $this
                ->Model
                ->queryAll("SELECT * FROM `tb_product_post` WHERE `parent_title`='{$parentTitle}'");

        $arrDatas = [];
        if ($formulars) {
            foreach ($formulars as $a) {
                $arrDatas[$a['ID']] = $a;
            }
        }

        $this->view->formulars = $arrDatas;

        $postCurrent = [];

        if ($ID) {
            $postCurrent = $this
                    ->Model
                    ->getOne($table, "WHERE  `ID`='$ID'");
        }

        if ($posts) {
            foreach ($posts as $k => $a) {
                if ($a['ID'] == $ID || (!$postCurrent && $k == 0)) {
                    $postCurrent = $a;
                }
            }
        }

        if (!$symbol && $postCurrent) {
            $symbol = $postCurrent['symbol'];
        }


        $this->view->posts = $posts;
        $this->view->postCurrent = $postCurrent;
        $this->view->symbol = $symbol;
        $this->view->tab = $tab;
        $this->view->type = $type;
        $this->view->aclist = $aclist;
        $this->view->histories = $this
                ->Model
                ->queryAll("SELECT *,`id` as `ID`
                FROM `{$table}`
                WHERE `symbol` ='{$symbol}'
                    AND `ID`<>'{$postCurrent['ID']}'
            ORDER BY `date` DESC LIMIT 20");
    }

    public function saveAction() {
        die('Ok');
    }

}
