<?php

class Processd1_IndexController extends Zend_Controller_Action {

    public function init() {

    }

    public function indexAction() {
        $data = $_POST['data'];
        $symbol = $_POST['symbol'];
        $sql = "";

        $dates = [];

        if (!$symbol || !$data) {
            die("");
        }

        if ($data) {
            foreach ($data as $k => $a) {
                $a['date_created'] = date('Y-m-d H:i:s');
                $a['date'] = date("Y-m-d", $a[0] / 1000);
                $a['stock_id'] = 1;
                $arrInsert = array_merge([$symbol], $a);
                $dates[] = $a['date'];
                if (strtotime($a['date']) >= strtotime(date("Y-m-d"))) {
                    continue;
                } elseif (strtotime($a['date']) == strtotime(date("Y-m-d") . '-1 day')) {
                    if (strtotime(date("Y-m-d 04:00:00")) > strtotime(date("Y-m-d H:i:s"))) {
                        continue;
                    }
                }

//                if (strtotime($a['date']) != strtotime('2022-10-07')) {
//                    continue;
//                }

                $sql .= "DELETE FROM `stock_price` WHERE `date`='{$a['date']}' AND `symbol`='{$symbol}';";
                $sql .= "INSERT INTO `stock_price` (`symbol`, `open_time`, `open`,`high`,`low`,`close`,`volume`,`close_time`,`asset_volume`,`number_trade`,`base_asset`,`quote_asset`,`ignore`,`date_created`,`date`,`stock_id`)
                VALUES ('" . implode("','", $arrInsert) . "');";
                
            }
            if ($sql) {
                $sql .= "UPDATE `stock` SET `d1`='PENDING' WHERE  `symbol`='{$symbol}';";
                $this->Model->query($sql);
                die($sql);
            }
        }
    }

}
