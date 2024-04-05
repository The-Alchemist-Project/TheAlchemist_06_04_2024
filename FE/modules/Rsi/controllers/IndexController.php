<?php

class Rsi_IndexController extends Zend_Controller_Action {

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
        $begandate = strtotime ( '-5 day' , strtotime ( $date ) ) ;
        //$checkDate = date('Y-m-d');

        foreach ($symboyAlls as $sb) {
            $symbol = $sb['symbol'];

            $dataPoints = $this
                ->Model
                ->queryAll("SELECT `a`.*,`a`.`id` as `ID` FROM `stock_price` as `a`
                        WHERE  `date`<'$curDate'
                            AND `date`>='$begandate'
                            AND `symbol`='{$symbol}'
                            
                            
                    ORDER BY `date` DESC LIMIT 15");
            if ($dataPoints) {
                $dataFlags = [];
                foreach ($dataPoints as $dd) {
                    $dataFlags[$dd['date']] = $dd;
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




                    if ($i < 8) { //8
                        $i++;
                        continue;
                    } elseif ($i == 8) {
                        if ($avgUp != 0) {
                            $avgUpR = $avgUp / 7;  // /7
                        }
                        if ($avgDown != 0) {
                            $avgDownR = $avgDown / 7;
                        }
                        $rsi = round (100 - (100 / (1 + $avgUpR / $avgDownR)), 4);

                        $arrDatas[$a1['ID']] = [
                            'rsi' => $rsi,
                            'date' => $a1['date'],
                            'rsi_old' => $a1['rsi28']
                        ];
                    } else {
                        $avgUpR = ($avgUpR * 6 + $up) / 7;  /// -6
                        $avgDownR = ($avgDownR * 6 + $down) / 7;
                        $rsi = round (100 - (100 / (1 + $avgUpR / $avgDownR)),4);
                        $arrDatas[$a1['ID']] = [
                            'rsi' => $rsi,
                            'date' => $a1['date'],
                            'rsi_old' => $a1['rsi28']
                        ];
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
                        $sql .= "UPDATE `stock_price` SET `rsi`='{$a['rsi']}' WHERE `ID`='{$k}';";
                        //$sql .= "UPDATE `stock_price` SET `rsi`='{$a['rsi14']}' WHERE `ID`='{$k}';";
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
?>
