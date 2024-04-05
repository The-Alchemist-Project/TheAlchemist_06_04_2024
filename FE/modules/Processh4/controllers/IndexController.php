<?php

class Processh4_IndexController extends Zend_Controller_Action {

    public function init() {

    }

    public function indexAction() {



        $this->view->symboyAlls = $this
                ->Model
                ->queryAll("SELECT * FROM `stock`");

        $data = $_POST['data'];
        $symbol = $_POST['symbol'];

        if (!$symbol || !$data) {
            die(json_encode([
                'error' => true,
                'msg' => 'Có lỗi xảy ra1'
            ]));
        }

        $sql = "";

        $dates = [];

        if ($data) {
            foreach ($data as $a) {
                $a['date_created'] = date('Y-m-d H:i:s');
                $a['date'] = date("Y-m-d", $a[0] / 1000);
                $a['stock_id'] = 1;
                $a['date_h4'] = date("Y-m-d H:i:s", $a[0] / 1000);
                $a['time'] = date("H:i:s", $a[0] / 1000);
                $arrInsert = array_merge([$symbol], $a);
                if (strtotime($a['date_h4']) >= strtotime(date("Y-m-d H:i:s") . '-1 hours')) {
                    continue;
                }

//                if (strtotime($a['date']) != strtotime('2022-08-12') && strtotime($a['date']) != strtotime('2022-08-11') && strtotime($a['date']) != strtotime('2022-08-12') && strtotime($a['date']) != strtotime('2022-08-09') && strtotime($a['date']) != strtotime('2022-08-07') && strtotime($a['date']) != strtotime('2022-08-06') && strtotime($a['date']) != strtotime('2022-08-03') && strtotime($a['date']) != strtotime('2022-08-01')
//                ) {
//                    continue;
//                }

                $dates[] = $a['date'];

                $sql .= "DELETE FROM `stock_price_h4` WHERE `date`='{$a['date']}' AND `date_h4`='{$a['date_h4']}' AND `symbol`='{$symbol}';";
                $sql .= "INSERT INTO `stock_price_h4` (`symbol`, `open_time`, `open`,`high`,`low`,`close`,`volume`,`close_time`,`asset_volume`,`number_trade`,`base_asset`,`quote_asset`,`ignore`,`date_created`,`date`,`stock_id`,`date_h4`,`time`)
                VALUES ('" . implode("','", $arrInsert) . "');";
                
            }
            if ($sql) {
                $sql .= "UPDATE `stock` SET `h4`='PENDING' WHERE  `symbol`='{$symbol}';";
                $this->Model->query($sql);
                die(json_encode([
                    'error' => false,
                    'msg' => 'Thành công'
                ]));
            }
        }
    }

}
