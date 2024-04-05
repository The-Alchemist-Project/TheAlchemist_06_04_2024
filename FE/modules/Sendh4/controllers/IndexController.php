<?php

class Sendh4_IndexController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function indexAction() {
        $this->view->symboyAlls = $this
                ->Model
                ->queryAll("SELECT * FROM `stock`");

    }

    public function index2Action() {

        $date = $_REQUEST['date'] ? $_REQUEST['date'] : date('Y-m-d H:00:00');
        $dateEnd = $_REQUEST['date_to'] ? $_REQUEST['date_to'] : date('Y-m-d H:00:00');
        
        
        

        $datas = [];

        //Lấy 4h trước khung 3,7,11,15,19,23

        $h = date("H", strtotime($date));

        $tTime = 3;
        if ($h <= 3) {
            $date = date("Y-m-d H:i:00", strtotime($date . '-4 hours'));
            $tTime = 23;
        } elseif ($h < 7) {
            $tTime = 3;
        } elseif ($h < 11) {
            $tTime = 7;
        } elseif ($h < 15) {
            $tTime = 11;
        } elseif ($h < 19) {
            $tTime = 15;
        } elseif ($h < 23) {
            $tTime = 19;
        } elseif ($h > 23) {
            $tTime = 23;
        }
 
        $date=date("Y-m-d", strtotime($date))." $tTime:00:00";
        
        $dateEnd = date("Y-m-d H:00:00", strtotime($date . '+4 hours'));
        
        
        $strDate = strtotime($date) . '000';
        $strDateEnd = strtotime($dateEnd) . '000';

        $limit = 20;
        $path = "h4_{$date}_time{$tTime}.txt";
        $file = getfile($path);
        $file = json_decode($file);

        if ($file) {
            $symboyAlls = $this
                ->Model
                ->queryAll("SELECT * FROM `stock` WHERE `symbol` NOT IN ('" . implode("','", $file) . "') LIMIT {$limit}");
        } else {
            $symboyAlls = $this
                ->Model
                ->queryAll("SELECT * FROM `stock` LIMIT {$limit}");
        }

        $total = $this
            ->Model
            ->getTotal("stock", "WHERE 1=1");

        if (count($file) == $total) {
            die('OK');
        }

        $sql = [];
        foreach ($symboyAlls as $k => $cur) {

            $symbol = $cur['symbol'];

            if ($file && in_array($symbol, $file)) {
                continue;
            }


            $url = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval=4h&startTime=" . $strDate . "&endTime=" . $strDateEnd;
            $res = static::load($url, []);
            if (!$res['error']) {
                $a = json_decode($res['response'])[0];

                if (!$a) {
                    $file[] = $cur['symbol'];
                    continue;
                }


                $flag = true;

                $a['date_created'] = date('Y-m-d H:i:s');
                $a['date'] = date("Y-m-d", $a[0] / 1000);
                $a['stock_id'] = 1;
                $a['date_h4'] = date("Y-m-d H:i:s", $a[0] / 1000);
                $a['time'] = date("H:i:s", $a[0] / 1000, '+4 hour');
                $arrInsert = array_merge([$symbol], $a);
                if (strtotime($a['date_h4']) >= strtotime(date("Y-m-d H:i:s") . '-1 hours')) {
                    $flag = false;
                    continue;
                }

//                if (strtotime($a['date']) != strtotime('2022-08-12') && strtotime($a['date']) != strtotime('2022-08-11') && strtotime($a['date']) != strtotime('2022-08-12') && strtotime($a['date']) != strtotime('2022-08-09') && strtotime($a['date']) != strtotime('2022-08-07') && strtotime($a['date']) != strtotime('2022-08-06') && strtotime($a['date']) != strtotime('2022-08-03') && strtotime($a['date']) != strtotime('2022-08-01')
//                ) {
//                    continue;
//                }


                $sql .= "DELETE FROM `stock_price_h4` WHERE `date`='{$a['date']}' AND `date_h4`='{$a['date_h4']}' AND `symbol`='{$symbol}';";
                $sql .= "INSERT INTO `stock_price_h4` (`symbol`, `open_time`, `open`,`high`,`low`,`close`,`volume`,`close_time`,`asset_volume`,`number_trade`,`base_asset`,`quote_asset`,`ignore`,`date_created`,`date`,`stock_id`,`date_h4`,`time`)
                VALUES ('" . implode("','", $arrInsert) . "');";

                if ($flag)
                    $file[] = $cur['symbol'];
            }
        }


        if ($sql) {
            $this->Model->query(implode(' ', $sql));
        }

        putfile($path, json_encode($file));

        die("Thành công " . count($file) . " trên tổng " . $total);
    }

    public function index3Action() {

        $date = $_REQUEST['date'] ? $_REQUEST['date'] : date('Y-m-d H:i:00');
        $dateEnd = $_REQUEST['date_to'] ? $_REQUEST['date_to'] : date('Y-m-d');
        $strDate = strtotime($date) . '000';
        $strDateEnd = strtotime($dateEnd) . '000';

        $datas = [];

        $date = date("Y-m-d", strtotime($date . '-16 hours'));

        $symboyAlls = $this
            ->Model
            ->queryAll("SELECT * FROM `stock`");
        foreach ($symboyAlls as $k => $cur) {

            $symbol = $cur['symbol'];

            $url = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval=4h&startTime=" . $strDate . "&endTime=" . $strDateEnd;
            $res = static::load($url, []);

            if (!$res['error']) {
                $dataFlag = json_decode($res['response']);
                $sql = "";
                foreach ($dataFlag as $a) {
                    $a['date_created'] = date('Y-m-d H:i:s');
                    $a['date'] = date("Y-m-d", $a[0] / 1000);
                    $a['stock_id'] = 1;
                    $a['date_h4'] = date("Y-m-d H:i:s", $a[0] / 1000);
                    $a['time'] = date("H:i:s", $a[0] / 1000);
                    $arrInsert = array_merge([$symbol], $a);

                    $sql .= "DELETE FROM `stock_price_h4` WHERE `date`='{$a['date']}' AND `date_h4`='{$a['date_h4']}' AND `symbol`='{$symbol}';";
                    $sql .= "INSERT INTO `stock_price_h4` (`symbol`, `open_time`, `open`,`high`,`low`,`close`,`volume`,`close_time`,`asset_volume`,`number_trade`,`base_asset`,`quote_asset`,`ignore`,`date_created`,`date`,`stock_id`,`date_h4`,`time`)
                VALUES ('" . implode("','", $arrInsert) . "');";
                }
                if ($sql) {
                    $this->Model->query($sql);
                    die($sql);
                }
            }

            sleep(10);
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
