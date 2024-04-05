<?php

class Check_IndexController extends Zend_Controller_Action {

    public function init() {

    }

    public function indexAction() {
        $queryD1 = $this->Model->queryAll("SELECT * FROM `stock_price` WHERE `date`>'2023-03-01'
            ORDER BY `date` DESC");
        if ($queryD1) {
            $arrDates = [];
            foreach ($queryD1 as $a) {
                $arrDates[$a['symbol']][$a['date']] = $a['date'];
            }
            $arrUpdates = [];
            foreach ($arrDates as $k => $posts) {
                $date = null;

                foreach ($posts as $d => $p) {
                    if (!$date) {
                        $date = $d;
                        continue;
                    }
                    $dateSub = date('Y-m-d', strtotime($d . '+ 1 days'));
                    if ($dateSub != $date) {
                        $arrUpdates[$date][] = $k;
                    }
                    $date = $d;
                }
            }
            echo "<h1>============================ERROR D1============================</h1>";
            echo '<pre>';
            print_r($arrUpdates);
            echo '</pre>';
            $sql = "";
            echo(count($arrUpdates) . ' ngày lỗi');
			
            $c = 0;
            foreach ($arrUpdates as $date => $symbols) {
                if (count($symbols) > 200)
                    continue;
                $strDateCheck = strtotime($date) - 60 * 60 * 24 . '000';
                $strDateEnd = strtotime($date) . '000';
                foreach ($symbols as $symbol) {
                    $url = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval=1d&startTime=" . $strDateCheck . "&endTime=" . $strDateEnd;

                    $res = static::load($url, []);
                    if (!$res['error']) {
                        $data = json_decode($res['response']);

                        if ($data) {
                            foreach ($data as $k => $a) {
                                $a['date_created'] = date('Y-m-d H:i:s');
                                $a['date'] = date("Y-m-d", $a[0] / 1000);
                                $a['stock_id'] = 1;
                                $arrInsert = array_merge([$symbol], $a);
                                if (strtotime($a['date']) >= strtotime(date("Y-m-d"))) {
                                    continue;
                                } elseif (strtotime($a['date']) == strtotime(date("Y-m-d") . '-1 day')) {
                                    if (strtotime(date("Y-m-d 04:00:00")) > strtotime(date("Y-m-d H:i:s"))) {
                                        continue;
                                    }
                                }


                                $sql .= "DELETE FROM `stock_price` WHERE `date`='{$a['date']}' AND `symbol`='{$symbol}';";
                                $sql .= "INSERT INTO `stock_price` (`symbol`, `open_time`, `open`,`high`,`low`,`close`,`volume`,`close_time`,`asset_volume`,`number_trade`,`base_asset`,`quote_asset`,`ignore`,`date_created`,`date`,`stock_id`)
                                    VALUES ('" . implode("','", $arrInsert) . "');";
                            }
                        }
                    }
                }
            }

            if ($sql) {
                $this->Model->query($sql);
            }

            die('OK');
        }
        die('OK');
    }

    public function h4Action() {
        $dateCheck = $this->Plugins->get("date", date("Y-m-d"));
        $count = $this->Plugins->get("cc", 1);

        $dateSub = date('Y-m-d', strtotime($dateCheck . '-3 day'));
        $queryD1 = $this->Model->queryAll("SELECT * FROM `stock_price_h4`
            WHERE `date` <= '{$dateCheck}' AND `date` > '{$dateSub}'
            ORDER BY `date_h4` DESC");

        if ($queryD1) {
            $arrDates = [];
            foreach ($queryD1 as $a) {
                $arrDates[$a['symbol']][$a['date_h4']] = $a['date_h4'];
            }
            $arrUpdates = [];
            foreach ($arrDates as $k => $posts) {
                $date = null;

                foreach ($posts as $d => $p) {
                    if (!$date) {
                        $date = $d;
                        continue;
                    }
                    $dateSub = date('Y-m-d H:i:s', strtotime($d . '+ 4 hours'));
                    if (strtotime($dateSub) != strtotime($date)) {
                        $arrUpdates[$date][] = $k;
                    }
                    $date = $d;
                }
            }
            echo "<h1>============================ERROR H4============================</h1>";
            echo '<pre>';
            print_r($arrUpdates);
            echo '</pre>';
            die('Liên hệ ngay tôi :D nếu còn lỗi');
            $sql = "";
            foreach ($arrUpdates as $date => $symbols) {
                $strDateCheck = strtotime($date) - 60 * 60 * 4 . '000';
                $strDateEnd = strtotime($date) . '000';
                foreach ($symbols as $symbol) {
                    $url = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval=4h&startTime=" . $strDateCheck . "&endTime=" . $strDateEnd;

                    $res = static::load($url, []);
                    if (!$res['error']) {
                        $data = json_decode($res['response']);
                        if ($data) {
                            foreach ($data as $k => $a) {
                                $a['date_created'] = date('Y-m-d H:i:s');
                                $a['date'] = date("Y-m-d", $a[0] / 1000);
                                $a['date_h4'] = date("Y-m-d H:i:s", $a[0] / 1000);
                                $a['time'] = date("H:i:s", $a[0] / 1000);
                                $a['stock_id'] = 1;
                                $arrInsert = array_merge([$symbol], $a);
                                if (strtotime($a['date_h4']) >= strtotime(date("Y-m-d H:i:s") . '-4 hours')) {
                                    continue;
                                }


                                $sql .= "DELETE FROM `stock_price_h4` WHERE `date`='{$a['date']}' AND `date_h4`='{$a['date_h4']}' AND `symbol`='{$symbol}';";
                                $sql .= "INSERT INTO `stock_price_h4` (`symbol`, `open_time`, `open`,`high`,`low`,`close`,`volume`,`close_time`,`asset_volume`,`number_trade`,`base_asset`,`quote_asset`,`ignore`,`date_created`,`date`,`stock_id`,`date_h4`,`time`)
                                VALUES ('" . implode("','", $arrInsert) . "');";
                            }
                        }
                    }
                }
            }

            if ($sql) {
                $this->Model->query($sql);
            }
        }
        if ($count >= 10) {
            $this->_redirect("{$this->view->controllerUrl}?date=" . $dateSub . "&cc=" . $count);
        }
        die('OK');
    }

    public static function load($url, $params = [], $isPost = false, $overOptions = []) {
        $m = null;
        $port = null;
        if (preg_match("/^(.*):(\d+)$/is", $url, $m)) {
            $url = $m[1];
            $port = $m[2];
        }


        $ch = curl_init($url);
        $options = [];

        if ($port) {
            $options[CURLOPT_PORT] = $port;
        }


        if (preg_match('#^http:\/\/#is', $url)) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = false;
        }

        $options[CURLOPT_HEADER] = false;
        $options[CURLOPT_VERBOSE] = true;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_USERAGENT] = "Mozilla/5.0 (Windows; U; Windows NT 5.1; vi-VN; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
        $options[CURLOPT_TIMEOUT] = 180;
        $options[CURLOPT_ENCODING] = true;
        $options[CURLOPT_FOLLOWLOCATION] = true;

        if ($isPost) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_URL] = $url;

            if (!empty($params)) {
                $options[CURLOPT_POSTFIELDS] = http_build_query($params);
            }
        } else {
            $options[CURLOPT_URL] = static:: build($url, $params);
        }


        if ($overOptions)
            $options = array_merge($options, $overOptions);


        foreach ($options as $prop => $val) {
            curl_setopt($ch, $prop, $val);
        }



        $res = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        return [
            'url' => $url,
            'params' => $params,
            'error' => $error,
            'errno' => $errno,
            'response' => $res
        ];
    }

    public static function build($url) {
        $params = [];
        $args = func_get_args();
        array_shift($args);

        foreach ($args as $ps) {
            $params = array_merge($params, $ps);
        }

        if (count($params) == 0) {
            return $url;
        }

        $url .= preg_match('/\?/u', $url) ? '&' : '?';
        $url .= encodeQuery($params);
        return $url;
    }

    public static function encodeQuery($data) {
        $str = http_build_query($data, PHP_QUERY_RFC1738);
        $str = str_ireplace("%5B", "[", $str);
        $str = str_ireplace("%5D", "]", $str);
        $str = str_ireplace("%2C", ",", $str);
        $str = str_ireplace("%40", "@", $str);
        return $str;
    }

    public function deleteAction() {
        $dateCheck = $this->Plugins->get("date", date("Y-m-d"));
        $wheres[] = "`date`>='{$dateCheck}'";
        $symbol = $this->Plugins->get("symbol");
        if ($symbol) {
            $wheres[] = "`symbol`='{$symbol}'";
        }


        $this->Model->delete('stock_price_details', implode(" AND ", $wheres));
        $this->Model->update('stock_price', [
            'customer_ids' => null,
            'type_ids' => null,
            'model_details' => null
                ], implode(" AND ", $wheres));

        die('OK');
    }

}
