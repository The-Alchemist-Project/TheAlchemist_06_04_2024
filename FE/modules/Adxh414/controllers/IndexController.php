<?php

class Adxh414_IndexController extends Zend_Controller_Action {

    public function init() {

    }

    public function indexAction() {



        $symboyAlls = $this
                ->Model
                ->queryAll("SELECT * FROM `stock`");

        $sql = "";

        //$dateCheck= $this->Plugins->get("date",date("Y-m-d 20:00:00"));

        foreach ($symboyAlls as $sb) {
            $symbol = $sb['symbol'];
            $dataPoints = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,`a`.`id` as `ID` FROM `stock_price_h4` as `a`
                        WHERE `date`>='2022-04-25'
                    AND `symbol`='{$symbol}'
                    ORDER BY `date_h4` DESC LIMIT 30");
            if ($dataPoints) {

                $arrDatas = [];
                $dataFlags = [];
                foreach ($dataPoints as $dd) {
                    $dataFlags[$dd['date_h4']] = $dd;
                }
                foreach ($dataPoints as $k => $a1) {

                    $avgUp = 0;
                    $avgDown = 0;
                    $countUp = 0;
                    $countDown = 0;
                    $cc = 0;

                    if ($k > count($dataPoints) - 29) {
                        continue;
                    }
                    $arrSub = [];

                    foreach ($dataPoints as $h => $a) {
                        if ($h < $k) {
                            continue;
                        }

                        if ($cc > 27) {
                            continue;
                        }

                        $datePrev = date('Y-m-d H:i:s', strtotime($a['date_h4'] . ' -4 hour'));

                        $dataPrev = $dataFlags[$datePrev];

                        if (!$dataPrev) {
                            continue;
                        }

                        $arrSub[$a['date']] = $dataPrev['close'] . '-' . $datePrev . '-' . $a['close'];

                        if ($dataPrev['high'] < $a['high']) {
                            $avgUp += $a['close'] - $dataPrev['close'];
                            $countUp++;
                        } else {
                            $avgDown += $dataPrev['low'] - $a['low'];
                            $countDown++;
                        }
                        $cc++;
                    }

                    if ($avgUp != 0) {
                        $avgUp = $avgUp / 28;
                    }
                    if ($avgDown != 0) {
                        $avgDown = $avgDown / 28;
                    }


                    $rsi = 100 - (100 / (1 + $avgUp / $avgDown));

                    $arrDatas[$a1['ID']] = $rsi;
                }



                if ($arrDatas) {
                    foreach ($arrDatas as $k => $a) {
                        $sql .= "UPDATE `stock_price_h4` SET `adx14`='{$a}' WHERE `ID`='{$k}';";
                    }
                }
            }
        }
        if ($sql) {
            $this->Model->query($sql);
            //die($sql);
        }
        die('Ok');
    }

}
