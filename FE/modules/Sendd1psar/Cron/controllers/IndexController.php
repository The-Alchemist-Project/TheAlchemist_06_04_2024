<?php

class Cron_IndexController extends Zend_Controller_Action {

    public function init() {

    }

    public function indexAction() {

        $customer = customer();

        $symbol = $this->Plugins->get("symbol");
       
        //$dateCheck = $this->Plugins->get("date", date("Y-m-d", strtotime(date("Y-m-d") . "-1 day")));
        $dateCheck = $this->Plugins->get("date", date("Y-m-d", strtotime(date("Y-m-d") . "-1 day")));
        $whereSymbol = ["'1'='1'"];
        //$whereSymbol[] = "`d1`='PENDING'";
        if ($symbol) {
            $whereSymbol[] = "`symbol`='{$symbol}'";
        }

        $cal = $this->Plugins->get("cal");
        $whereCal = ["`desc` IS NOT NULL"];
        if ($cal) {
            $whereCal[] = "`ID`='{$cal}'";
        }



        if ($customer && $customer['user'] != 'admin') {
            if (!$cal) {
                //die('Bạn không có quyền chạy công thức này');
            }
            $whereCal[] = "`customer_id`='{$customer['ID']}'";
        }


        $formulars = $this
                ->Model
                ->queryAll("SELECT * FROM `tb_product_post` WHERE `parent_title`='D1'
                    AND " . implode(" AND ", $whereCal) . "
                    ORDER BY `ID` DESC");

        $symboyAlls = $this
                ->Model
                ->queryAll("SELECT * FROM `stock`
                     WHERE " . implode(' AND ', $whereSymbol) . "
                         LIMIT 50");

        if (!$formulars) {
            die('Không có công thức thỏa mãn tính toán');
        }


        $sql = "";

        //$dateCheck = date("2023-04-04");
        $day = $cal ? 5 : 20;
        $dateOld = date_create($dateCheck);
        date_add($dateOld, date_interval_create_from_date_string("-{$day} days"));
        $dateOld = date_format($dateOld, "Y-m-d");
        //debug($dateCheck, $dateOld);
        $sql = "";
        foreach ($symboyAlls as $k => $s) {
            $symbol = $s['symbol'];
            //echo $symbol . "<br />";

            //for ($d = strtotime($dateCheck); $d >= strtotime($dateOld); $d = strtotime("-1 day", $d)) {
                for ($d = strtotime($dateCheck); $d >= strtotime($dateOld); $d = strtotime("-1 day", $d)) {
                //echo date("Y-m-d", $d) . "<br />";
                $date = date("Y-m-d", $d);

                //$datePrev = date("Y-m-d", strtotime($date) . '-5 days');

                $postSubs = $this
                        ->Model
                        ->queryAll("SELECT * FROM `stock_price`
                                WHERE `symbol` = '{$symbol}'
                                AND `date` <= '{$date}'
                                AND `date`< CURDATE()
                                AND `rsi` IS NOT NULL
                                AND `rsi` <=30 OR 'rsi'>=70
                                
                                ORDER BY `date` DESC LIMIT 5");

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
                            $dataPoints["{cd{$i}_open}"] = round($row['open'], 10);
                            $dataPoints["{cd{$i}_high}"] = round($row['high'], 10);
                            $dataPoints["{cd{$i}_low}"] = round($row['low'], 10);
                            $dataPoints["{cd{$i}_close}"] = round($row['close'], 10);
                        } else {
                            $dataPoints["{cd{$i}_open}"] = round($row['open'], 10);
                            $dataPoints["{cd{$i}_high}"] = round($row['high'], 10);
                            $dataPoints["{cd{$i}_low}"] = round($row['low'], 10);
                            $dataPoints["{cd{$i}_close}"] = round($row['close'], 10);
                        }

                        if ((double) $row['rsi'] == 0 || (double) $row['rsi14'] == 0) {
                            continue;
                        }
                        if ((double) $row['psar'] == 0) {
                            continue;
                        }


                        $dataPoints["{cd{$i}_rsi}"] = $row['rsi'] ?? 0;
                       // $dataPoints["{cd{$i}_rsi14}"] = $row['rsi14'] ?? 0;
                       // $dataPoints["{cd{$i}_psar}"] = $row['psar'] ?? 0;
                       // $dataPoints["{cd{$i}_psar_low}"] = $row['psar_low'] ?? 0;
                       // $dataPoints["{cd{$i}_psar_high}"] = $row['psar_high'] ?? 0;
                       // $dataPoints["{cd{$i}_ema89}"] = $row['ema89'] ?? 0;
                       // $dataPoints["{cd{$i}_ema89_h4}"] = $row['ema89_h4'] ?? 0;
                      //  $dataPoints["{cd{$i}_adx14}"] = $row['adx14'] ?? 0;
                        $i++;
                    }

                    if (!$dataPoints) {
                        continue;
                    }

                    foreach ($formulars as $fomular) {

                        //Xử lý với TH chỉ áp dụng cho 1 số  cặp tiền;
                        $coinIds = [];
                        if ($fomular['coin_ids']) {
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

                        //$desc = str_replace(array_keys($dataPoints), array_values($dataPoints), "{cd2_close}/{cd2_open} <= 1.002");
                        $result = 0;
                        try {
                            eval("\$result = $desc;");
                        } catch (ParseError $e) {
                            //debug($desc, '-', $row, '---', $fomular, $e);
                            continue;
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
                                eval("\$target3 = $target3;");
                                eval("\$stoploss = $stoploss;");

                                //Xử lý buylong khi có công thức
                                $flagBuyLong = explode(' - ', $buylong);
                                if ($flagBuyLong) {
                                    $arrBuyLongs = [];
                                    foreach ($flagBuyLong as $fg) {
                                        eval("\$ff = $fg;");

                                        if (preg_match('/USDT/', $symbol)) {
                                            $arrBuyLongs[] = round($ff, 4);
                                        } else {
                                            $arrBuyLongs[] = sprintf("%.08f", $ff);
                                        }
                                    }
                                    $buylong = implode(" - ", $arrBuyLongs);
                                }

                                if ($fomular['r1']) {
                                    eval("\$r1 = $r1;");
                                }
                                if ($fomular['r2']) {
                                    eval("\$r2 = $r2;");
                                }
                            } catch (ParseError $e) {
                                //debug($e, $desc, '--', $row, '---', $fomular);
                                continue;
                            }
                            $dataJsons = json_encode($dataPoints);

                            if (preg_match('/USDT/', $symbol)) {
                                $dataJsonViews = [
                                    'target1' => $target1,
                                    'target2' => $target2,
                                    'target3' => $target3,
                                    'stoploss' => $stoploss,
                                    'buylong' => $buylong,
                                    'r1' => round($r1, 4),
                                    'r2' => round($r2, 4)
                                ];
                            } else {
                                $dataJsonViews = [
                                    'target1' => sprintf("%.08f", $target1),
                                    'target2' => sprintf("%.08f", $target2),
                                    'target3' => sprintf("%.08f", $target3),
                                    'stoploss' => sprintf("%.08f", $stoploss),
                                    'buylong' => $buylong,
                                    'r1' => sprintf("%.08f", $r1),
                                    'r2' => sprintf("%.08f", $r2)
                                ];
                            }

                            $dataJsonViews = json_encode($dataJsonViews);

                            $type = $fomular['model_name'];

                            ///Xóa toàn bộ dữ liệu liên quan
                            $this->Model->delete('stock_price_details', "`post_id` = '$id' AND `type_id` = '{$fomular['ID']}'");
                            $dataInsert = [
                                'post_id' => $id,
                                'type_id' => $fomular['ID'],
                                'model_type' => preg_match('/Long - /', $type) ? 'UP' : 'DOWN',
                                'model_title' => $fomular['title'],
                                'json_datas' => $dataJsons,
                                'json_details' => $dataJsonViews,
                                'date' => $date,
                                'symbol' => $symbol,
                                'customer_id' => $fomular['customer_id'],
                            ];

                            $this->Model->insert('stock_price_details', $dataInsert);
                        } else {
                            $this->Model->delete('stock_price_details', "`post_id` = '$id' AND `type_id` = '{$fomular['ID']}'");
                        }
                    }



                    //Update lại bảng chính
                    $details = $this
                            ->Model
                            ->queryAll("SELECT `a`.`id`, `a`.`type_id`, `a`.`model_type`, `a`.`json_datas`, `a`.`json_details`, `a`.`customer_id`
                            FROM `stock_price_details` as `a`
                            WHERE `a`.`post_id` = '{$id}'");

                    if ($details) {
                        $customerIds = [];
                        $typeIds = [];
                        $normal = [];
                        foreach ($details as $dsub) {
                            if ($dsub['customer_id']) {
                                $customerIds[] = $dsub['customer_id'];
                            } else {
                                $normal = $dsub;
                            }
                            $typeIds[] = $dsub['type_id'];
                        }

                        $dataUpdate = [
                            'customer_ids' => $customerIds ? implode(',', array_filter(array_unique($customerIds))) : null,
                            'type_ids' => $typeIds ? implode(',', array_filter(array_unique($typeIds))) : null,
                            'model_details' => json_encode($details)
                        ];

                        if ($normal) {
                            $dataUpdate = array_merge($dataUpdate, [
                                'type_id' => $normal['type_id'],
                                'model_title' => $normal['model_title'],
                                'model_type' => $normal['model_type'],
                                'json_datas' => $normal['json_datas'],
                                'json_details' => $normal['json_details'],
                            ]);
                        }
                    } else {
                        $dataUpdate = [
                            'customer_ids' => null,
                            'type_ids' => null,
                            'model_details' => null
                        ];
                    }

                    $this->Model->update('stock_price', $dataUpdate, "`ID` = '$id'");
                    
                }
            }
            $this->Model->update('stock', [
                        'd1' => 'UPDATED'
                    ], "`id` = '{$s['id']}'");
        }

        die('Tính toán thành công;');
        //$this->_redirect("{$this->view->controllerUrl}/post");
    }

}
