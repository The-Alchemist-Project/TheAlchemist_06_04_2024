<?php

class Cronh4psar_IndexController extends Zend_Controller_Action {

    public function init() {

    }

    public function indexAction() {

        $formulars = $this
                ->Model
                ->queryAll("SELECT * FROM `tb_product_post` WHERE `parent_title`='H4PSAR'");

        $symboyAlls = $this
                ->Model
                ->queryAll("SELECT * FROM `stock`
                    LIMIT 250");

        $sql = "";

        $dateCheck = $this->Plugins->get("date", date("Y-m-d H:00:00"));

        $dateOld = date_create($dateCheck);
        date_add($dateOld, date_interval_create_from_date_string("-3 days"));
        $dateOld = date_format($dateOld, "Y-m-d H:i:s");

        $sql = "";
        foreach ($symboyAlls as $k => $s) {
            $symbol = $s['symbol'];
            //echo $symbol. "<br />";
            for ($d = strtotime($dateCheck); $d > strtotime($dateOld); $d = strtotime("-2 hours", $d)) {
                //echo date("Y-m-d H:i:s", $d) . "<br />";
                $date = date("Y-m-d H:i:s", $d);

                $postSubs = $this
                        ->Model
                        ->queryAll("SELECT * FROM `stock_price_h4`
                            WHERE `symbol`='{$symbol}'
                            AND  `date_h4`<='{$date}'
                                ORDER BY `date_h4` DESC
                            LIMIT 5");

                $dataPoints = [];
                $subs = [];
                if ($postSubs) {
                    if (count($postSubs) < 5) {
                        continue;
                    }
                    $i = 1;
                    $id = 0;
                    foreach ($postSubs as $r => $row) {
                        if (!$r) {
                            $id = $row['id'];
                            //continue;
                        }

                        if (preg_match('/USDT/', $symbol)) {
                            $dataPoints["{cd{$i}_open}"] = round($row['open'], 4);
                            $dataPoints["{cd{$i}_high}"] = round($row['high'], 4);
                            $dataPoints["{cd{$i}_low}"] = round($row['low'], 4);
                            $dataPoints["{cd{$i}_close}"] = round($row['close'], 4);
                            $dataPoints["{cd{$i}_psar}"] = round($row['psar'], 4);
                            $dataPoints["{cd{$i}_psar_high}"] = round($row['psar_high'], 4);
                            $dataPoints["{cd{$i}_psar_low}"] = round($row['psar_low'], 4);
                        } else {
                            $dataPoints["{cd{$i}_open}"] = round($row['open'], 8);
                            $dataPoints["{cd{$i}_high}"] = round($row['high'], 8);
                            $dataPoints["{cd{$i}_low}"] = round($row['low'], 8);
                            $dataPoints["{cd{$i}_close}"] = round($row['close'], 8);
                            $dataPoints["{cd{$i}_psar}"] = round($row['psar'], 8);
                            $dataPoints["{cd{$i}_psar_high}"] = round($row['psar_high'], 8);
                            $dataPoints["{cd{$i}_psar_low}"] = round($row['psar_low'], 8);
                        }

                        $dataPoints["{cd{$i}_rsi}"] = $row['rsi'] ?? 0;
                        $dataPoints["{cd{$i}_ema89}"] = $row['ema89'] ?? 0;
                        $dataPoints["{cd{$i}_ema89_h4}"] = $row['ema89_h4'] ?? 0;
                        $i++;
                    }


                    foreach ($formulars as $fomular) {
                        //Xử lý với TH chỉ áp dụng cho 1 số  cặp tiền;
                        $coinIds = [];
                        if ($fomular && $fomular['coin_ids']) {
                            $coinIds = explode(',', $fomular['coin_ids']);
                        }

                        if ($coinIds) {
                            if (!in_array($s['id'], $coinIds)) {
                                continue;
                            }
                        }
                        ////////Kết thúc////


                        $flagBuyLong = explode(' - ', $fomular['buylong']);

                        $desc = str_replace(array_keys($dataPoints), array_values($dataPoints), $fomular['desc']);

                        //$desc = str_replace(array_keys($dataPoints), array_values($dataPoints), "{cd3_rsi} < 20 && {cd3_close} < {cd3_open}");

                        $result = 0;
                        try {
                            eval("\$result=  $desc;");
                        } catch (ParseError $e) {
                            print_r("Desc:");
                            print_r("<br>");
                            print_r($desc);
                            print_r("<br>");
                            print_r($dataPoints);
                            print_r("<br>");
                            print_r($fomular);
                            //print_r($e->getMessage());
                        }

                        if ($result) {
                            $target1 = str_replace(array_keys($dataPoints), array_values($dataPoints), $fomular['target1']);
                            $target2 = str_replace(array_keys($dataPoints), array_values($dataPoints), $fomular['target2']);
                            $target3 = str_replace(array_keys($dataPoints), array_values($dataPoints), $fomular['target3']);
                            $buylong = str_replace(array_keys($dataPoints), array_values($dataPoints), $fomular['buylong']);
                            $stoploss = str_replace(array_keys($dataPoints), array_values($dataPoints), $fomular['stoploss']);
                            $r1 = str_replace(array_keys($dataPoints), array_values($dataPoints), $fomular['r1']);
                            $r2 = str_replace(array_keys($dataPoints), array_values($dataPoints), $fomular['r2']);

                            try {
                                eval("\$target1 = $target1;");
                                eval("\$target2 = $target2;");
                                eval("\$target3= $target3;");
                                eval("\$stoploss= $stoploss;");

                                if ($fomular['r1']) {
                                    eval("\$r1 = $r1;");
                                }
                                if ($fomular['r2']) {
                                    eval("\$r2 = $r2;");
                                }

                                //Xử lý buylong khi có công thức
                                $flagBuyLong = explode(' - ', $buylong);
                                if ($flagBuyLong) {
                                    $arrBuyLongs = [];
                                    foreach ($flagBuyLong as $fg) {
                                        eval("\$ff= $fg;");
                                        if (preg_match('/USDT/', $symbol)) {
                                            $arrBuyLongs[] = round($ff, 4);
                                        } else {
                                            $arrBuyLongs[] = sprintf("%.08f", $ff);
                                        }
                                    }
                                    $buylong = implode(" - ", $arrBuyLongs);
                                }
                            } catch (ParseError $e) {
                                print_r($fomular['title']);
                                print_r("<br>");
                                print_r($fomular['buylong']);
                                print_r("<br>");
                                //print_r($e->getMessage());
                            }
                            $dataJsons = json_encode($dataPoints);
                            if (preg_match('/USDT/', $symbol)) {
                                $dataJsonViews = json_encode([
                                    'target1' => $target1,
                                    'target2' => $target2,
                                    'target3' => $target3,
                                    'stoploss' => $stoploss,
                                    'buylong' => $buylong,
                                    'r1' => round($r1, 4),
                                    'r2' => round($r2, 4)
                                ]);
                            } else {
                                $dataJsonViews = json_encode([
                                    'target1' => sprintf("%.08f", $target1),
                                    'target2' => sprintf("%.08f", $target2),
                                    'target3' => sprintf("%.08f", $target3),
                                    'stoploss' => sprintf("%.08f", $stoploss),
                                    'buylong' => $buylong,
                                    'r1' => sprintf("%.08f", $r1),
                                    'r2' => sprintf("%.08f", $r2)
                                ]);
                            }



                            $type = $fomular['model_name'];
                            if (preg_match('/Long - /', $type)) {
                                $sql .= "UPDATE `stock_price_h4`
                                        SET `psar_model_title`='{$type}',
                                            `psar_model_type`='UP',
                                            `psar_json_datas`= '{$dataJsons}',
                                            `psar_json_details`= '{$dataJsonViews}',
                                            `psar_type_id` ='{$fomular['ID']}'
                                WHERE `date_h4`='{$date}' AND `symbol`='{$symbol}' AND `id`='{$id}';";
                            } else {
                                $sql .= "UPDATE `stock_price_h4`
                                        SET `psar_model_title`='{$type}',
                                            `psar_model_type`='DOWN',
                                            `psar_json_datas`= '{$dataJsons}',
                                            `psar_json_details`= '{$dataJsonViews}',
                                            `psar_type_id` ='{$fomular['ID']}'
                                WHERE `date_h4`='{$date}' AND `symbol`='{$symbol}' AND `id`='{$id}';";
                            }
                        }
                    }
                }
            }
        }
        if ($sql) {
            $this->Model->query($sql);
            die($sql);
        }

        die('Tính toán thành công;');
        //$this->_redirect("{$this->view->controllerUrl}/post");
    }

}
