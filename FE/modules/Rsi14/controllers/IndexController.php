<?php

class Rsi14_IndexController extends Zend_Controller_Action {

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

        $curDate = date('Y-m-d');
        $begandate = strtotime ( '-10 day' , strtotime ( $date ) ) ;
        //$checkDate = date('Y-m-d');

        foreach ($symboyAlls as $sb) {
            $symbol = $sb['symbol'];

            $dataPoints = $this
                ->Model
                ->queryAll("SELECT `a`.*,`a`.`id` as `ID` FROM `stock_price` as `a`
                        WHERE  `date`<'$curDate'
                            AND `date`>='$begandate'
                            AND `symbol`='{$symbol}'
                            
                            
                    ORDER BY `date` DESC LIMIT 100");
            if ($dataPoints) {
                $dataFlags = [];
                foreach ($dataPoints as $dd) {
                    $dataFlags[$dd['date']] = $dd;
                }

                $arrDatas = [];
                $arrDataChecks = [];
                $i = 1;
                $avgUp = 0;
                $avgDown = 0;
                foreach ($dataPoints as $k => $a1) {



                    if ($i == 1) {
                        $i++;
                        continue;
                    }


                    $datePrev = date('Y-m-d', strtotime($a1['date'] . ' -1 day'));

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
                        $rsi14 = round (100 - (100 / (1 + $avgUpR / $avgDownR)), 4);


                        $arrDatas[$a1['ID']] = [
                            'rsi14' => $rsi14,
                            'date' => $a1['date'],
                            'rsi_old' => $a1['rsi28']
                        ];

                        $arrDataChecks[] = "'{$a1['date']}' , '{$rsi14}'";
                    } else {
                        $avgUpR = ($avgUpR * 13 + $up) / 14;  /// -6
                        $avgDownR = ($avgDownR * 13 + $down) / 14;
                        $rsi14 = round (100 - (100 / (1 + $avgUpR / $avgDownR)), 4);

                        $arrDatas[$a1['ID']] = [
                            'rsi14' => $rsi14,
                            'date' => $a1['date'],
                            'rsi_old' => $a1['rsi28']
                        ];

                        $arrDataChecks[] = "'{$a1['date']}' , '{$rsi14}'";
                    }
                    $i++;
                }
                if ($arrDatas) {
                    $count = 0;
                    foreach ($arrDatas as $k => $a) {
//                        if ($a['rsi_old']) {
//                            continue;
//                        }
//                        if ($count > 200) {
//                            break;
//                        }
                        $count++;
                        $sql .= "UPDATE `stock_price` SET `rsi14`='{$a['rsi14']}' WHERE `ID`='{$k}' ; ";
                        //AND `date`>='2023-03-25';
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
