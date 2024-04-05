<?php

class Send_IndexController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function indexAction() {


        $this->view->symboyAlls = $this
                ->Model
                ->queryAll("SELECT * FROM `stock`");
        //truy vấn từ bảng stock
    }

    public function index2Action() {
        //kiểm tra ngày quét dữ liệu

        debug(date('Y-m-d H:i:s'));
        $date = $_REQUEST['date'] ? $_REQUEST['date'] : date('Y-m-d H:i:00');
        //$date = $_REQUEST['date'] ? $_REQUEST['date'] : date('Y-m-d');
        $dateEnd = $_REQUEST['date_to'] ? $_REQUEST['date_to'] : date('Y-m-d');

        $strDateEnd = strtotime($dateEnd) . '000';

        $datas = [];

       // $date = date("Y-m-d", strtotime($date . '-1 day'));
       
        //$newdate = date('Y-m-d');
        //$date = strtotime ( '+1 day' , strtotime ( $newdate ) ) ;
        //$date = date ( 'Y-m-d' , $date );
        //$date = date("Y-m-d",'+1 day', strtotime($date));
        //$date = date('Y-m-d');
        //$date = strtotime ( '+3599 minute' , strtotime ( $date ) ) ;
        //$begandate = strtotime ( '-5 day' , strtotime ( $date ) ) ;
        $date = date("Y-m-d", strtotime($date));
        //$strDate = strtotime($date) . '000';
        $strDate = strtotime ( '-3 day' , strtotime ( $date ) ) ;
        //số lượng bản ghi 
        // $limit = 20;
        $limit = 150;
        $path = "d1_{$date}.txt";
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
        //gán danh sách total bằng  danh sách trong stock
        $total = $this
            ->Model
            ->getTotal("stock", "WHERE 1=1");
        //so sánh bảng ghi tên mã nếu lấy hết dữ liệu binance

        if (count($file) == $total) {
            die('Đã hoàn thành việc lấy dữ liệu của ' . $total . ' mã');
        }

        $sql = [];
        foreach ($symboyAlls as $k => $cur) {

            $symbol = $cur['symbol'];

            if ($file && in_array($symbol, $file)) {
                continue;
            }

            //truyền dữ liệu vaò biến từ date đến date-end
            
            $url = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval=1d&startTime=" . $strDate . "&endTime=" . $strDateEnd;
            $res = static::load($url, []);
            if (!$res['error']) {
               $a = json_decode($res['response'])[0];
           


                if (!$a) {
                    $file[] = $cur['symbol'];
                    continue;
                }


                $flag = true;
                $a['open_time'] = date('Y-m-d H:i:s');
                $a['close_time'] = date('Y-m-d H:i:s');
                $a['date_created'] = date('Y-m-d H:i:s');
                $a['date'] = date("Y-m-d", $a[0] / 1000 );
                $a['stock_id'] = 1;
                $arrInsert = array_merge([$symbol], $a);


                if (strtotime($a['date']) >= strtotime(date("Y-m-d"))) {
                    $flag = false;
                    continue;
                    //nếu ngày lấy dữ liệu lớn hơn ngày hiện tại -> dừng và lưu
                } 
                //elseif (strtotime($a['date']) == strtotime(date("Y-m-d") . '-1 day'))
                elseif (strtotime($a['date']) == strtotime(date("Y-m-d") . '-1 day'))
                {
                    if (strtotime(date("Y-m-d 04:00:00")) > strtotime(date("Y-m-d H:i:s"))) {
                        $flag = false;
                        continue;
                    // nếu ngày lấy dữ liệu bằng ngày hôm qua -> dừng và lưu
                    }
                }

                               if (strtotime($a['date']) != strtotime('2023-04-12')) {
                                    continue;
                               }
                    //import database

                $sql[] = "DELETE FROM `stock_price` WHERE `date`='{$a['date']}' AND `symbol`='{$symbol}';";
                $sql[] = "INSERT INTO `stock_price` (`symbol`, `open_time`, `open`,`high`,`low`,`close`,`volume`,`close_time`,`asset_volume`,`number_trade`,`base_asset`,`quote_asset`,`ignore`,`date_created`,`date`,`stock_id`)
                    VALUES ('" . implode("','", $arrInsert) . "');";

                if ($flag)
                    $file[] = $cur['symbol'];
            }
        }


        if ($sql) {
            $this->Model->query(implode(' ', $sql));
        }

        putfile($path, json_encode($file));

        $p = round($file / $total, 2) * 100;
        die("Thành công  " . $p . "% (" . count($file) . " trên tổng " . $total . ")");
    }
    //chart-data
     
    

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
