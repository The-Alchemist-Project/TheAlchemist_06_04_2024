<?php

class Sendd1psar_IndexController extends Zend_Controller_Action {

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

//$dateCheck= $this->Plugins->get("date",date("Y-m-d 20:00:00"));

        $dateCheck = '2022-04-01';
        $dateCur = date('Y-m-d');

        foreach ($symboyAlls as $sb) {
            $symbol = $sb['symbol'];
            $dataPoints = $this
                ->Model
                ->queryAll("SELECT `a`.*,`a`.`id` as `ID` FROM `stock_price` as `a`
                        WHERE `date`<='$dateCur'
                         AND `date`>='$dateCheck'
                    AND `symbol`='{$symbol}'
                    ORDER BY `date` ASC");

            if ($dataPoints) {

                $dataFlags = [];
                foreach ($dataPoints as $dt) {
                    $dataFlags[$dt['date']] = $dt;
                }

                $i = 0.02;
                $arrDatas = [];
                $psarUp = [
                    'min' => 0,
                    'max' => 0
                ];
                $psarDown = [
                    'min' => 0,
                    'max' => 0
                ];
                $sql = [];
                foreach ($dataPoints as $k => $a) {
                    $dateCurrent = $a['date'];
                    $dateSub = date("Y-m-d", strtotime($dateCurrent . "-1 days"));
                    $dateSub2 = date("Y-m-d", strtotime($dateCurrent . "-2 days"));
                    $dataSub = $dataFlags[$dateSub];
                    $dataSub2 = $dataFlags[$dateSub2];
                    if ($k == 0) {
                        if ($a['close'] > $a['open']) {
                            $checkMin = true;
                            $checkMax = false;
                            $ep0 = $a['high'];
                            $psar0 = $a['low'];
                            $af0 = 0.02;
                            $psar = $psar0;
                        } else {
                            $checkMin = false;
                            $checkMax = true;
                            $ep0 = $a['low'];
                            $psar0 = $a['high'];
                            $af0 = 0.02;
                            $psar = $psar0;
                        }
                    } else {
                        $psar = $psar + $af0 * ($ep0 - $psar);
                        if ($checkMin) {
                            if ($a['high'] > $ep0) {
                                $ep0 = $a['high'];
                                $af0 = min($af0 + 0.02, 0.2);
                            } else {
                                
                            }
                        } else {
                            if ($a['low'] < $ep0) {
                                $ep0 = $a['low'];
                                $af0 = min($af0 + 0.02, 0.2);
                            } else {
                                
                            }
                        }

                        if ($checkMin) {
                            if ($psar < $a['low'] && (($psar < min($dataSub['low'], $dataSub2['low']) && $dataSub && $dataSub2) || ($psar < min($dataSub['low']) && $dataSub && !$dataSub2))) {
                                
                            } elseif ($psar < $a['low'] && (($psar > min($dataSub['low'], $dataSub2['low']) && $dataSub && $dataSub2) || ($psar > min($dataSub['low']) && $dataSub && !$dataSub2))) {
                                if (!$dataSub2) {
                                    $psar = $dataSub['low'];
                                } else {
                                    $psar = min($dataSub['low'], $dataSub2['low']);
                                }
                            } else {
                                $psar = $ep0;
                                $af0 = 0.02;
                                $ep0 = $a['low'];
                                $checkMin = false;
                                $checkMax = true;
                            }
                            if ($checkMax) {
                                if ($sql) {
                                    foreach ($sql as $ks => &$s) {
                                        if (!in_array($ks, $psarUp['ID'])) {
                                            continue;
                                        }
                                        $s['psar_low'] = $psarUp['min'];
                                        $s['psar_high'] = $psarUp['max'];
                                    }
                                    unset($s);
                                }
                            }
                        } elseif ($checkMax) {


                            if ($psar > $a['high'] && $psar > max($dataSub['high'] ?? 0, $dataSub2['high'] ?? 0)) {
                                
                            } elseif ($psar > $a['high'] && $psar < max($dataSub['high'] ?? 0, $dataSub2['high'] ?? 0)) {
                                $psar = max($dataSub['high'], $dataSub2['high']);
                            } else {
                                $psar = $ep0;
                                $af0 = 0.02;
                                $ep0 = $a['high'];
                                $checkMin = true;
                                $checkMax = false;
                            }



                            if ($checkMin) {
                                if ($sql) {
                                    foreach ($sql as $ks => &$s) {
                                        if (!in_array($ks, $psarDown['ID'])) {
                                            continue;
                                        }
                                        $s['psar_low'] = $psarDown['min'];
                                        $s['psar_high'] = $psarDown['max'];
                                    }
                                    unset($s);
                                }
                            }
                        }
                    }

                    $i += 0.02;

                    if ($i == 0.2) {
                        $i = 0.02;
                    }

                    $arrDatas[$a['ID']] = $psar;

                    if ($checkMin) {
                        if ($psar < $psarUp['min'] || $psarUp['min'] == 0) {
                            $psarUp['min'] = $psar;
                        }
                        if ($psar > $psarUp['max'] || $psarUp['max'] == 0) {
                            $psarUp['max'] = $psar;
                        }
                        $psarUp['ID'][] = $a['ID'];
                        $psarDown = [
                            'min' => 0,
                            'max' => 0,
                            'ID' => []
                        ];
                    } else {
                        if ($psar < $psarDown['min'] || $psarDown['min'] == 0) {
                            $psarDown['min'] = $psar;
                        }
                        if ($psar > $psarDown['max'] || $psarDown['max'] == 0) {
                            $psarDown['max'] = $psar;
                        }
                        $psarDown['ID'][] = $a['ID'];
                        $psarUp = [
                            'min' => 0,
                            'max' => 0,
                            'ID' => []
                        ];
                    }

                    if ($psar) {
                        $sql[$a['ID']] = [
                            'psar' => $psar,
                            'psar_type' => $checkMin ? 'UP' : 'DOWN',
                            'psar_old' => $a['psar']
                        ];
                    }
                    if ($k == count($dataPoints) - 1) {
                        if ($checkMin) {
                            if ($sql) {
                                foreach ($sql as $ks => &$s) {
                                    if (!in_array($ks, $psarUp['ID'])) {
                                        continue;
                                    }
                                    $s['psar_low'] = $psarUp['min'];
                                    $s['psar_high'] = $psarUp['max'];
                                }
                                unset($s);
                            }
                        } else {
                            if ($sql) {
                                foreach ($sql as $ks => &$s) {
                                    if (!in_array($ks, $psarDown['ID'])) {
                                        continue;
                                    }
                                    $s['psar_low'] = $psarDown['min'];
                                    $s['psar_high'] = $psarDown['max'];
                                }
                                unset($s);
                            }
                        }
                    }
                }
                if ($sql) {
                    $str = [];
                    $count = 0;
                    foreach ($sql as $ks => $s) {
//                        if ($s['psar_old']) {
//                            continue;
//                        }
//                        if ($count > 100) {
//                            break;
//                        }

                        $count++;
                        $str[] = "UPDATE `stock_price`
                                SET `psar`='{$s['psar']}',
                                    `psar_type`='{$s['psar_type']}',
                                    `psar_low`='{$s['psar_low']}',
                                    `psar_high`='{$s['psar_high']}'
                                    WHERE `ID`='{$ks}'";
                    }
                    

                    $this->Model->query(implode(';', $str));
                    //print_r(implode(';', $str));
                }
            }
        }


        die('Ok');
    }

}
