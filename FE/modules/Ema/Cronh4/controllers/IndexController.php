<?php

class Cronh4_IndexController extends Zend_Controller_Action {

    public function init() {

    }

    public function indexAction() {
        $customer = customer();
        $symbol = $this->Plugins->get("symbol");
        $dateCheck = $this->Plugins->get("date", date("Y-m-d 07:00:00")."-4 hour");
       // $dateCheck = $this->Plugins->get("date", date("Y-m-d 07:00:00"));
        $whereSymbol = ["'1'='1'"];
        
        $whereSymbol[] = "`h4`='PENDING'";
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
                ->queryAll("SELECT * FROM `tb_product_post` WHERE `parent_title`='H4'
                    AND " . implode(" AND ", $whereCal) . "
                    ORDER BY `ID` DESC LIMIT 100");

        $symboyAlls = $this
                ->Model
                ->queryAll("SELECT * FROM `stock`
                     WHERE " . implode(' AND ', $whereSymbol) . "
                         ORDER BY `ID` DESC LIMIT 100 ");
                         if (!$formulars) {
                            die('Không có công thức thỏa mãn tính toán');
                        }
        $sql = "";
        $day = $cal ? 5 : 15;
        $dateOld = date_create($dateCheck);
        date_add($dateOld, date_interval_create_from_date_string("-$day days"));
        $dateOld = date_format($dateOld, "Y-m-d H:i:s");

        


        $sql = "";
        foreach ($symboyAlls as $k => $s) {
            $symbol = $s['symbol'];
            echo $symbol . "<br />";
            for ($d = strtotime($dateCheck); $d > strtotime($dateOld); $d = strtotime("-4 hours", $d)) {
                $date = date("Y-m-d H:i:s", $d);
                echo date("Y-m-d H:i:s", $d), ', ' . date("Y-m-d H:i:s", strtotime("-4 hours", $d)) . "<br />";
                $begandate = date("Y-m-d H:i:s" , strtotime ( '-24 Hour' , strtotime ( $date ) ));
                $postSubs = $this;
                        ->Model
                        ->queryAll("SELECT * FROM `stock_price_h4`
                            WHERE `symbol`='{$symbol}'
                            AND  `date_h4`<= '{$date}'
                            AND `date`>= '{$begandate}'
                            AND (`rsi` NOT BETWEEN '32' AND '68')
                            ORDER BY `date_h4` DESC ");
                            //AND `rsi` not NULL
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
                        $dataPoints["{cd{$i}_rsi14}"] = $row['rsi14'] ?? 0;
                        $dataPoints["{cd{$i}_psar}"] = $row['psar'] ?? 0;
                        $dataPoints["{cd{$i}_psar_low}"] = $row['psar_low'] ?? 0;
                        $dataPoints["{cd{$i}_psar_high}"] = $row['psar_high'] ?? 0;
                        $dataPoints["{cd{$i}_ema89}"] = $row['ema89'] ?? 0;
                        $dataPoints["{cd{$i}_ema89_h4}"] = $row['ema89_h4'] ?? 0;
                        $dataPoints["{cd{$i}_adx14}"] = $row['adx14'] ?? 0;
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
                            continue;
                            //debug($desc);
//                            print_r("Desc:");
//                            print_r("<br>");
//                            print_r($desc);
//                            print_r("<br>");
//                            print_r($dataPoints);
//                            print_r("<br>");
//                            print_r($fomular);
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
                                continue;
                                // debug($e);
//                                print_r($fomular['title']);
//                                print_r("<br>");
//                                print_r($fomular['buylong']);
//                                print_r("<br>");
                                //print_r($e->getMessage());
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
                            $this->Model->delete('stock_price_h4_details', "`post_id`='$id' AND `type_id`='{$fomular['ID']}'");
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
                                'date_created' => date("Y-m-d H:i:s"),
                                'date_find' => $date
                            ];

                            $this->Model->insert('stock_price_h4_details', $dataInsert);
                        } else {
                            $this->Model->delete('stock_price_h4_details', "`post_id`='$id' AND `type_id`='{$fomular['ID']}'");
                        }
                    }

                    //Update lại bảng chính
                    $details = $this
                            ->Model
                            ->queryAll("SELECT `a`.`id`,`a`.`type_id`,`a`.`model_type`,`a`.`json_datas`,`a`.`json_details`,`a`.`customer_id`
                                    FROM `stock_price_h4_details` as `a`
                                    WHERE `a`.`post_id` ='{$id}'");

                    if ($details) {
                        $customerIds = [];
                        $typeIds = [];
                        $normal = [];
                        foreach ($details as $d1) {
                            if ($d1['customer_id']) {
                                $customerIds[] = $d1['customer_id'];
                            } else {
                                $normal = $d1;
                            }
                            $typeIds[] = $d1['type_id'];
                        }

                        $dataUpdate = [
                            'customer_ids' => $customerIds ? implode(',', array_filter(array_unique($customerIds))) : null,
                            'type_ids' => $typeIds ? implode(',', array_filter(array_unique($typeIds))) : null,
                            'model_details' => json_encode($details)
                        ];
                        if ($normal) {
                            $dataUpdate = array_merge($dataUpdate, [
                                'type_id' => $normal['type_id'],
                                'model_type' => $normal['model_type'],
                                'json_datas' => $normal['json_datas'],
                                'json_details' => $normal['json_details'],
                                'model_title' => $normal['model_title'],
                            ]);
                        }
                    } else {
                        $dataUpdate = [
                            'customer_ids' => null,
                            'type_ids' => null,
                            'model_details' => null
                        ];
                    }

                    $this->Model->update('stock_price_h4', $dataUpdate, "`ID`='$id'");
                    //$sql .= "UPDATE `stock_price_h4_detail` SET `time_find`='{$a['time_find']}' WHERE `ID`='{$k}';";
                }
            }
            
            $this->Model->update('stock', [
                        'h4' => 'UPDATED'
                    ], "`id` = '{$s['id']}'");
            //$this->Model->UPDATE(`stock_price_h4_details` SET `date_find`= DATE_ADD(`date`, INTERVAL 0 HOUR));
        }
//        if ($sql) {
//            $this->Model->query($sql);
//            die($sql);
//        }

        die('Tính toán thành công;');
        //$this->_redirect("{$this->view->controllerUrl}/post");
    }

}
