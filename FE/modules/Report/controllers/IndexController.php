<?php

class Report_IndexController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function index1Action() {

        $dateEnd = $_REQUEST['date_to'] ? $_REQUEST['date_to'] : date('Y-m-d');
        $strDateEnd = strtotime($dateEnd) . '000';
        $posts = $this
            ->Model
            ->queryAll("SELECT * FROM `stock_histories`
                    WHERE `status` IN ('WAITING','DOING')
                    -- AND `id`='1499'
                    ORDER BY `id` DESC LIMIT 10");

        $date = $_REQUEST['date_from'] ? $_REQUEST['date_from'] : date('Y-m-d');
        $strDate = (strtotime($date) - 24 * 60 * 60) . '000';
        $strDateCheck = (strtotime($date) - 100) . '000';

        foreach ($posts as $k => $post) {
            $entry = $post['entryzone'];
            $dca1 = $post['DCA1'];
            $dca2 = $post['DCA2'];
            $stoploss = $post['stoploss'];
            $targetzone = $post['targetzone'];
            //$date = $post['date'];

            $symbol = $post['symbol'];
            $priceReal = 0;
            //Lấy giá open hiện tại
            $url = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval=1m&startTime=" . $strDateCheck . "&endTime=" . $strDateEnd;
            $res = static::load($url, []);
            if (!$res['error']) {
                $dataFlag = json_decode($res['response']);
                foreach ($dataFlag as $a) {
                    $priceReal = $a[1];
                }
            }
            $type = preg_match("/Bullish/", $post['model_title']);
            $status = 'WAITING';

            $url = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval=1d&startTime=" . $strDate . "&endTime=" . $strDateEnd;
            $res = static::load($url, []);
            if (!$res['error']) {
                $dataFlag = json_decode($res['response']);
                foreach ($dataFlag as $a) {
                    $a1 = date("Y-m-d H:i:s", $a[0] / 1000);
                    $high = $a[2];
                    $low = $a[3];

                    $profit = 0;
                    $ev = 0;
                    if ($type) {//Up
                        if ($high <= $stoploss) {
                            $status = 'FAIL';
                            $ev = ($entry + $dca1 + $dca2) / 3;
                            $profit = ($stoploss - $ev) / $ev * 100;
                        } elseif ($dca2 >= $high && $high > $stoploss) {
                            if ($low <= $stoploss) {
                                $status = 'FAIL';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                $profit = ($stoploss - $ev) / $ev * 100;
                            } else {
                                $status = 'DOING';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                continue;
                            }
                        } elseif ($dca1 >= $high && $high > $dca2) {
                            if ($low <= $stoploss) {
                                $status = 'FAIL';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                $profit = ($stoploss - $ev) / $ev * 100;
                            } elseif ($dca2 >= $low && $low > $stoploss) {
                                $status = 'DOING';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                continue;
                            } elseif ($dca2 < $low) {
                                $status = 'DOING';
                                $ev = $ev > 0 ? min($ev, ($entry + $dca1) / 2) : ($entry + $dca1) / 2;
                                continue;
                            }
                        } elseif ($entry >= $high && $high > $dca1) {
                            if ($low <= $stoploss) {
                                $status = 'FAIL';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                $profit = ($stoploss - $ev) / $ev * 100;
                            } elseif ($dca2 >= $low && $low > $stoploss) {
                                $status = 'DOING';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                continue;
                            } elseif ($dca1 >= $low && $low > $dca2) {
                                $status = 'DOING';
                                $ev = $ev > 0 ? min($ev, ($entry + $dca1) / 2) : ($entry + $dca1) / 2;
                                continue;
                            } elseif ($dca1 < $low) {
                                $status = 'DOING';
                                $ev = $ev > 0 ? min($ev, $entry) : $entry;
                                continue;
                            }
                        } elseif ($targetzone >= $high && $high > $entry) {
                            if ($low <= $stoploss) {
                                $status = 'LOSS';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                $profit = ($stoploss - $ev) / $ev * 100;
                            } elseif ($dca2 >= $low && $low > $stoploss) {
                                $status = 'DOING';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                continue;
                            } elseif ($dca1 >= $low && $low > $dca2) {
                                $status = 'DOING';
                                $ev = $ev > 0 ? min($ev, ($entry + $dca1) / 2) : ($entry + $dca1) / 2;
                                continue;
                            } elseif ($entry >= $low && $low > $dca1) {
                                $status = 'DOING';
                                $ev = $ev > 0 ? min($ev, $entry) : $entry;
                                continue;
                            } elseif ($entry < $low) {
                                continue;
                            }
                        } elseif ($targetzone < $high) {
                            if ($low <= $stoploss) {
                                continue;
                            } elseif ($dca2 >= $low && $low > $stoploss) {
                                $status = 'WIN';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                $profit = ($targetzone - $ev) / $ev * 100;
                            } elseif ($dca1 >= $low && $low > $dca2) {
                                $status = 'WIN';
                                $ev = $ev > 0 ? min($ev, ($entry + $dca1) / 2) : ($entry + $dca1) / 2;
                                $profit = ($targetzone - $ev) / $ev * 100;
                            } elseif ($entry >= $low && $low > $dca1) {
                                $status = 'WIN';
                                $ev = $ev > 0 ? min($ev, $entry) : $entry;
                                $profit = ($targetzone - $ev) / $ev * 100;
                            } elseif ($entry < $low) {
                                if ($ev > 0) {
                                    $status = 'WIN';
                                    $profit = ($targetzone - $ev) / $ev * 100;
                                } else {
                                    continue;
                                }
                            }
                        }
                    } else {
                        if ($low >= $stoploss) {
                            $status = 'LOSS';
                            $ev = ($entry + $dca1 + $dca2) / 3;
                            $profit = ($ev - $stoploss) / $ev * 100;
                        } elseif ($dca2 <= $low && $low < $stoploss) {
                            if ($high >= $stoploss) {
                                $status = 'LOSS';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                $profit = ($ev - $stoploss) / $ev * 100;
                            } elseif ($high < $stoploss) {
                                $status = 'DOING';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                continue;
                            }
                        } elseif ($dca1 <= $low && $low < $dca2) {
                            if ($high >= $stoploss) {
                                $status = 'LOSS';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                $profit = ($ev - $stoploss) / $ev * 100;
                            } elseif ($dca2 <= $high && $high < $stoploss) {
                                $status = 'DOING';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                continue;
                            } elseif ($dca2 > $high) {
                                $status = 'DOING';
                                $ev = $ev > 0 ? max($ev, ($entry + $dca1) / 2) : ($entry + $dca1) / 2;
                                continue;
                            }
                        } elseif ($entry <= $low && $low < $dca1) {
                            if ($high >= $stoploss) {
                                $status = 'LOSS';
                                ;
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                $profit = ($ev - $stoploss) / $ev * 100;
                            } elseif ($dca2 <= $high && $high < $stoploss) {
                                $status = 'DOING';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                continue;
                            } elseif ($dca1 <= $high && $high < $dca2) {
                                $status = 'DOING';
                                $ev = $ev > 0 ? max($ev, ($entry + $dca1) / 2) : ($entry + $dca1) / 2;
                                continue;
                            } elseif ($dca1 > $high) {
                                $status = 'DOING';
                                $ev = $ev > 0 ? max($ev, $entry) : $entry;
                                continue;
                            }
                        } elseif ($targetzone <= $low && $low < $entry) {
                            if ($high >= $stoploss) {
                                $status = 'LOSS';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                ;
                                $profit = ($ev - $stoploss) / $ev * 100;
                            } elseif ($dca2 <= $high && $high < $stoploss) {
                                $status = 'DOING';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                continue;
                            } elseif ($dca1 <= $high && $high < $dca2) {
                                $status = 'DOING';
                                $ev = $ev > 0 ? max($ev, ($entry + $dca1) / 2) : ($entry + $dca1) / 2;
                                continue;
                            } elseif ($entry <= $high && $high < $dca1) {
                                $status = 'DOING';
                                $ev = $ev > 0 ? max($ev, $entry) : $entry;
                                continue;
                            } elseif ($entry > $high) {
                                continue;
                            }
                        } elseif ($targetzone > $low) {
                            if ($high >= $stoploss) {
                                continue;
                            } elseif ($dca2 <= $high && $high < $stoploss) {
                                $status = 'WIN';
                                $ev = ($entry + $dca1 + $dca2) / 3;
                                $profit = ($ev - $targetzone) / $ev * 100;
                            } elseif ($dca1 <= $high && $high < $dca2) {
                                $status = 'WIN';
                                $ev = $ev > 0 ? max($ev, ($entry + $dca1) / 2) : ($entry + $dca1) / 2;
                                $profit = ($ev - $targetzone) / $ev * 100;
                            } elseif ($entry <= $high && $high < $dca1) {
                                $status = 'WIN';
                                $ev = $ev > 0 ? max($ev, $entry) : $entry;
                                $profit = ($ev - $targetzone) / $ev * 100;
                            } elseif ($entry > $high) {
                                if ($ev > 0) {
                                    $status = 'WIN';
                                    $profit = ($ev - $targetzone) / $ev * 100;
                                } else {
                                    continue;
                                }
                            }
                        }
                    }
                    //////
                    if (!$ev) {
                        continue;
                    }

                    if (!$profit && $post['json_details'] && $status = 'DOING') {
                        $dd = json_decode($post['json_details']);
                        $profit = ($dd->r2) * 100;
                    }


                    $dataUpdate = [
                        'status' => $status,
                        'profit' => $profit
                    ];
					
					
					if($post['id']){
						$this->Model->update('stock_histories', [
							'status' => "'".$status."'",
							'profit' => $profit
						], "`id`='{$post['id']}'");
					}
                }
            }
        }
        die('OK');
    }

    public function indexAction() {

        $dateEnd = $_REQUEST['date_to'] ? $_REQUEST['date_to'] : date('Y-m-d');
        $strDateEnd = strtotime($dateEnd) . '000';
        $posts = $this
            ->Model
            ->queryAll("SELECT * FROM `stock_histories`
                    WHERE `status` IN ('WAITING','DOING')
                    -- AND `id`='1499'
                    ORDER BY `id` DESC LIMIT 10");

        $date = $_REQUEST['date_from'] ? $_REQUEST['date_from'] : date('Y-m-d');
        $strDate = (strtotime($date) - 24 * 60 * 60) . '000';
        $strDateCheck = (strtotime($date) - 100) . '000';

        foreach ($posts as $k => $post) {
            $entry = $post['entryzone'];
            $dca1 = $post['DCA1'];
            $dca2 = $post['DCA2'];
            $stoploss = $post['stoploss'];
            $targetzone = $post['targetzone'];
            //$date = $post['date'];

            $symbol = $post['symbol'];
            $priceReal = 0;
            //Lấy giá open hiện tại
            $url = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval=1m&startTime=" . $strDateCheck . "&endTime=" . $strDateEnd;
            $res = static::load($url, []);
            if (!$res['error']) {
                $dataFlag = json_decode($res['response']);
                foreach ($dataFlag as $a) {
                    $priceReal = $a[1];
                }
            }
            $type = preg_match("/Bullish/", $post['model_title']);
            $status = 'WAITING';

            $url = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval=1d&startTime=" . $strDate . "&endTime=" . $strDateEnd;
            $res = static::load($url, []);
            if (!$res['error']) {
                $dataFlag = json_decode($res['response']);
                foreach ($dataFlag as $a) {
                    $a1 = date("Y-m-d H:i:s", $a[0] / 1000);
                    $high = $a[2];
                    $low = $a[3];

                    $profit = 0;
                    $ev = 0;
                    if ($type) {//Up
                        //if ($dca1 < $high && $high <= $entry) { // entry > Giá ($low) >dca1
                        if ($dca1 < $high) { // entry > Giá ($low) >dca1
                            if ($stoploss < $low) {
                                $ev = $entry;
                            }
                        } elseif ($dca1 > $high && $high > $dca2) { // entry > Giá ($low) >dca2
                            if ($stoploss < $high) {
                                $ev = ($entry + $dca1) / 2;
                            }
                        } elseif ($dca2 > $high) { // entry > Giá ($low) >dca2
                            if ($stoploss < $high) {
                                $ev = ($entry + $dca1 + $dca2) / 2;
                            }
                        }


                        if ($targetzone <= $high) {
                            $ev = $entry;
                            $status = 'WON';
                        }
                        $profit = ($targetzone - $ev) / $ev * 100;

                        if ($stoploss > $low) {
                            $status = 'FAIL';
                        }
                    } else {//Down
                        if ($dca1 > $low && $dca1 > $entry) { // entry > Giá ($low) >dca1
                            if ($stoploss > $low) {
                                $ev = $entry;
                            }
                        } elseif ($dca1 < $low && $low < $dca2) { // entry > Giá ($low) >dca2
                            if ($stoploss > $low) {
                                $ev = ($entry + $dca1) / 2;
                            }
                        } elseif ($dca2 < $low) { // entry > Giá ($low) >dca2
                            if ($stoploss > $low) {
                                $ev = ($entry + $dca1 + $dca2) / 2;
                            }
                        }
                    }

                    if (!$ev) {
                        continue;
                    }

                    if (!$profit && $post['json_details'] && $status = 'DOING') {
                        $dd = json_decode($post['json_details']);
                        $profit = ($dd->r2) * 100;
                    }


                    $dataUpdate = [
                        'status' => $status,
                        'profit' => $profit
                    ];

                    $this->Model->update('stock_histories', $dataUpdate, "`id`='{$post['id']}'");
                }
            }
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

}
