<?php

class Rsih414_IndexController extends Zend_Controller_Action {

    public function init() {

    }

    public function indexAction() {




        $symbol = $this->Plugins->get("symbol");

        $whereSyms = ["1=1"];
        if ($symbol) {
            $whereSyms[] = "`symbol`='{$symbol}'";
        }

        $symboyAlls = $this
                ->Model
                ->queryAll("SELECT * FROM `stock`
                      WHERE  " . implode(" AND ", $whereSyms) . "
                    ");

        $sql = "";

        $curDate = date('Y-m-d H:m:s');
        //$checkDate = date('Y-m-d');

        foreach ($symboyAlls as $sb) {
            $symbol = $sb['symbol'];

            $dataPoints = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,`a`.`id` as `ID` FROM `stock_price_h4` as `a`
                        WHERE  `date_h4`<='$curDate'
                            AND `date`>='2023-04-14'
                            AND `symbol`='{$symbol}'
                            
                    ORDER BY `date_h4` DESC LIMIT 15");
            if ($dataPoints) {
                $dataFlags = [];
                foreach ($dataPoints as $dd) {
                    $dataFlags[$dd['date_h4']] = $dd;
                }

                $arrDatas = [];
                $i = 1;
                $avgUp = 0;
                $avgDown = 0;
                foreach ($dataPoints as $k => $a1) {



                    if ($i == 1) {
                        $i++;
                        continue;
                    }


                    $datePrev = date('Y-m-d H:i:s', strtotime($a1['date_h4'] . ' -4 hour'));

                    $dataPrev = $dataFlags[$datePrev];

                    if ($dataPrev['close'] < $a1['close']) {
                        $up = $a1['close'] - $dataPrev['close'];
                        $down = 0;
                        $avgUp += $a1['close'] - $dataPrev['close'];
                    } else {
                        $down = $dataPrev['close'] - $a1['close'];
                        $up = 0;
                        $avgDown += $down;
                    }




                    if ($i < 15) { //8
                        $i++;
                        continue;
                    } elseif ($i == 15) {
                        if ($avgUp != 0) {
                            $avgUpR = $avgUp / 14;  // /7
                        }
                        if ($avgDown != 0) {
                            $avgDownR = $avgDown / 14;
                        }
                        $rsi14 = round(100 - (100 / (1 + $avgUpR / $avgDownR)),4);

                        $arrDatas[$a1['ID']] = [
                            'rsi14' => $rsi14,
                            'date_h4' => $a1['date_h4'],
                            'rsi_old' =>$a1['rsi28']
                        ];
                    } else {
                        $avgUpR = ($avgUpR * 13 + $up) / 14;  /// -6
                        $avgDownR = ($avgDownR * 13 + $down) / 14;
                        $rsi14 = round(100 - (100 / (1 + $avgUpR / $avgDownR)),4);
                        $arrDatas[$a1['ID']] = [
                            'rsi14' => $rsi14,
                            'date_h4' => $a1['date_h4'],
                            'rsi_old' =>$a1['rsi28']
                        ];
                    }
                    $i++;
                }


                if ($arrDatas) {
                      $count=0;
                    foreach ($arrDatas as $k => $a) {
//                        if($a['rsi_old']){
//                            continue;
//                        }
//                        if($count>100){
//                            continue;
//                        }
                        if(strtotime($a['date_h4'])<= strtotime("2023-04-11 00:00:00")){
                            continue;
                        }
                        $count++;
                        
                        $sql .= "UPDATE `stock_price_h4` SET `rsi14`='{$a['rsi14']}' WHERE `ID`='{$k}';";
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
