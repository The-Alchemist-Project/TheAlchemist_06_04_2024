<?php

class Ema89h4_IndexController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function indexAction() {



        $symboyAlls = $this
                ->Model
                ->queryAll("SELECT * FROM `stock`
                    WHERE `symbol`='FILUSDT'");

        $sql = "";

        foreach ($symboyAlls as $sb) {
            $symbol = $sb['symbol'];
            $dataPoints = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,`a`.`id` as `ID` 
                        FROM `stock_price_h4` as `a` WHERE `date`>'2020-12-01'
                        AND (`time`='00:00:00' || `time`>='22:00:00')
                    AND `symbol`='{$symbol}'
                    ORDER BY `date` DESC");
            $arrDatas = [];
            foreach ($dataPoints as $k => $a1) {
                $date = $a1['date'];
                //Tính toán update vào cây nến cuối cùng của ngày hôm trước sang
                if ($a1['time'] == '00:00:00') {
                    $arrDatas[$date] = $a1['ema'];
                } else {
                    $date = date("Y-m-d", strtotime($date) . "-1 day");
                    $arrDatas[$date] = $a1['ema'];
                }
            }

            if ($arrDatas) {
                foreach ($arrDatas as $k => $a) {
                    $sql .= "UPDATE `stock_price` SET `ema89_h4`='{$a}' WHERE `date`='{$k}';";
                }
            }
        }
        if ($sql) {
            $this->Model->query($sql);
        }
        die($sql);
        die('Ok');
    }

}
