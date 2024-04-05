<?php

class Ema_IndexController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function indexAction() {



        $symboyAlls = $this
                ->Model
                ->queryAll("SELECT * FROM `stock`");

        $sql = "";

        foreach ($symboyAlls as $sb) {
            $symbol = $sb['symbol'];
            $dataPoints = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,`a`.`id` as `ID` FROM `stock_price` as `a` 
                        WHERE `date`>='2020-09-01'
                    AND `symbol`='{$symbol}'
                    ORDER BY `date`");
            if ($dataPoints) {
                $arr = [];
                $arr[$symbol] = $dataPoints;

                foreach ($arr as $s => $dataPoint) {

                    $arrDatas = [];
                    foreach ($dataPoint as $k => $a1) {

                        foreach ($dataPoint as $h => $a) {
                            if ($h < $k) {
                                continue;
                            }
                            if ($h - 89 >= $k) {
                                continue;
                            }
                            $sum += $a['close'];
                        }

                        $arrDatas[$a1['ID']] = $sum / 89;
                    }


                    if ($arrDatas) {
                        foreach ($arrDatas as $k => $a) {
                            $sql .= "UPDATE `stock_price` SET `ema`='{$a}' WHERE `ID`='{$k}';";
                        }
                    }
                }
            }
        }
        if ($sql) {
            $this->Model->query($sql);
        }
        die('Ok');
    }

}
