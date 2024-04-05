<?php

class Adx14_IndexController extends Zend_Controller_Action {

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
                        WHERE `date`>='2023-04-01'
                    AND `symbol`='{$symbol}'
                    ORDER BY `date` DESC");
            if ($dataPoints) {
                $dataFlags = [];
                foreach ($dataPoints as $dd) {
                    $dataFlags[$dd['date']] = $dd;
                }

                $arrDatas = [];
                foreach ($dataPoints as $k => $a1) {

                    $avgUp = 0;
                    $avgDown = 0;
                    $cc = 0;

                    if ($k > count($dataPoints) - 29) {
                        continue;
                    }
                    $datePrev = date('Y-m-d', strtotime($a1['date'] . ' -1 day'));

                    $dataPrev = $dataFlags[$datePrev];

                    $arrSub = [];
                    if ($dataPrev['high'] <= $a1['high']) {
                        $avgUp += $a1['high'] - $dataPrev['high'];
                        $arrSub[$a1['date']]['up'] = $a1['high'] - $dataPrev['high'];
                        $arrSub[$a1['date']]['log'] = $a1['high'] . "- " . $dataPrev['high'];
                    } else if ($dataPrev['low'] > $a1['low']) {
                        $avgDown += $dataPrev['low'] - $a1['low'];
                        $arrSub[$a1['date']]['down'] = $dataPrev['low'] - $a1['low'];
                        $arrSub[$a1['date']]['log'] = $a1['low'] . "-" . $dataPrev['low'];
                    }
                    $arrr[] = $a1['close'];
                    foreach ($dataPoints as $h => $a) {
                        if ($h <= $k) {
                            continue;
                        }

                        if ($cc > 27) {
                            continue;
                        }

                        $datePrev = date('Y-m-d', strtotime($a['date'] . ' -1 day'));

                        $dataPrev = $dataFlags[$datePrev];

                        if (!$dataPrev) {
                            continue;
                        }


                        $arrr[] = $a['close'];

                        if ($dataPrev['high'] <= $a['high']) {
                            $avgUp += $a['high'] - $dataPrev['high'];
                            $arrSub[$a['date']]['up'] = $a['high'] - $dataPrev['high'];
                            $arrSub[$a['date']]['log'] = $a['high'] . "-" . $dataPrev['high'];
                        } else {
                            $avgDown += $dataPrev['low'] - $a['low'];
                            $arrSub[$a['date']]['down'] = $dataPrev['low'] - $a['low'];
                            $arrSub[$a['date']]['log'] = $a['low'] . "-" . $dataPrev['low'];
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
                        $sql .= "UPDATE `stock_price` SET `adx14`='{$a}' WHERE `ID`='{$k}';";
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
