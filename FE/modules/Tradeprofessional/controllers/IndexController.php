<?php

class TradeProfessional_IndexController extends Zend_Controller_Action {

    public function init() {
        $customer = $_SESSION['cp__customer'];
//        if ($customer) {
//            if ($customer['type_id'] == 1) {
//                $this->_redirect(BASE_URL . "/{$this->language}/tin-tuc");
//            }
//        }
    }

    public function indexAction() {
        $customer = $_SESSION['cp__customer'];
        $date = date('Y-m-d');
        $ID = $this->Plugins->getNum('ID', 0);
        $symbol = $this->Plugins->get('symbol', "");
        $tab = $this->Plugins->get('tab', "USDT");
        $type = $this->Plugins->get('type', "D1");
        $aclist = $this->Plugins->get('aclist', "st");
        $study = $this->Plugins->get('study', "RSI");
        $dateSearch = '2023-06-01';
        if ($type == 'H4') {
            $table = 'stock_price_h4';
            $table_detail = 'stock_price_h4_details';
            $order = 'date_h4';
            $parentTitle = 'H4';
            $dateSearch = '2023-06-15';
        } elseif ($type == 'H1' || $type == '30m') {
            $table = 'stock_price';
            $order = 'date';
            $parentTitle = 'D1';
            $table_detail = 'stock_price_details';
        } else {
            $table = 'stock_price';
            $order = 'date';
            $parentTitle = 'D1';
            $table_detail = 'stock_price_details';
        }

        $arrAiTrades = [];
        $arrAiTrades[] = [
            'ID' => "1",
            'title' => "RSI7 & Candlestick Model"
        ];
        $arrAiTrades[] = [
            'ID' => "2",
            'title' => "RSI7 & ParabolicSAR Model"
        ];
        $arrAiTrades[] = [
            'ID' => "3",
            'title' => "RSI14 & Candlestick Model"
        ];
        $arrAiTrades[] = [
            'ID' => "4",
            'title' => "RSI14 & ParabolicSAR Model"
        ];

        if ($tab == 'BTC') {
            $symbolS = 'BTC';
        } else {
            $symbolS = 'USDT';
        }

        $wheres = ['1=1'];
        if ($study == 'RSI') {
            $wheres[] = "(`a`.`view_id`='1' OR `a`.`view_id` IS NULL)";
        } elseif ($study == 'RSI,PSAR') {
            $wheres[] = "(`a`.`view_id`='2' OR `a`.`view_id` IS NULL)";
        } elseif ($study == 'RSI14') {
            $wheres[] = "(`a`.`view_id`='3' OR `a`.`view_id` IS NULL)";
        } elseif ($study == 'RSI14,PSAR') {
            $wheres[] = "(`a`.`view_id`='4' OR `a`.`view_id` IS NULL)";
        }

        $whereStandar = ["1=1"];
        $whereMyList = ["FIND_IN_SET('{$customer['ID']}',`a`.`customer_ids`)"];
        $whereProList = ["FIND_IN_SET('{$customer['ID']}',`a`.`customer_ids`)"];

        $tbPosts = $this
                ->Model
                ->queryAll("SELECT `a`.`id`,`a`.`coin_ids`,`a`.`customer_id`,`a`.`view_id`
                FROM `tb_product_post` as `a`
                WHERE `a`. `ID` <>0 AND " . implode(' AND ', $wheres) . "");
				
				

        $idPosts = [0];
        $whereIdsPro = [0];
        $idsPro = [0];
        $idsMy = [0];
        $idsAll = [0];
        $whereIds = [];
        $whereIdsMy = [];
        if ($tbPosts) {
            $idPosts = array_column($tbPosts, 'id');

            foreach ($tbPosts as $a) {
                if ($a['coin_ids']) {
                    $whereIdsPro[] = "FIND_IN_SET('{$a['id']}',`a`.`type_ids`)";
                    $idsPro[] = $a['id'];
                } elseif ($a['customer_id']) {
                    if ($study == 'RSI' && $a['view_id'] == '1') {
                        $whereIdsMy[] = "FIND_IN_SET('{$a['id']}',`a`.`type_ids`)";
                        $idsMy[] = $a['id'];
                    } elseif ($study == 'RSI,PSAR' && $a['view_id'] == '2') {
                        $whereIdsMy[] = "FIND_IN_SET('{$a['id']}',`a`.`type_ids`)";
                        $idsMy[] = $a['id'];
                    } elseif ($study == 'RSI14' && $a['view_id'] == '3') {
                        $whereIdsMy[] = "FIND_IN_SET('{$a['id']}',`a`.`type_ids`)";
                        $idsMy[] = $a['id'];
                    } elseif ($study == 'RSI14,PSAR' && $a['view_id'] == '4') {
                        $whereIdsMy[] = "FIND_IN_SET('{$a['id']}',`a`.`type_ids`)";
                        $idsMy[] = $a['id'];
                    }
                } else {
                    $whereIds[] = "FIND_IN_SET('{$a['id']}',`a`.`type_ids`)";
                    $idsAll[] = $a['id'];
                }
            }


            if ($whereIdsMy) {
                $whereMyList[] = "(" . implode(' OR ', $whereIdsMy) . ")";
            } else {
                $whereMyList[] = "1=2";
            }
            if ($whereIds) {
                $whereStandar[] = "(" . implode(' OR ', $whereIds) . ")";
            }
            if ($whereIdsPro) {
                $whereProList[] = "(" . implode(' OR ', $whereIdsPro) . ")";
            } else {
                $whereProList[] = "1=2";
            }
        }

        if ($_REQUEST['sp'] == '1') {
            debug($whereIdsMy,$whereStandar);
        }


        if ($type == 'H1' || $type == '30m') {
            $whereStandar[] = "`ID`='-1'";
            $whereMyList[] = "`ID`='-1'";
            $whereProList[] = "`ID`='-1'";
        }
		
        
        $posts = $this
                ->Model
                ->queryAll("SELECT `a`.*,`a`.`id` as `ID`
                FROM `{$table}` as `a`
                WHERE `a`.`rsi` > 0
                AND  `a`.`type_ids` IS NOT NULL
                AND `a`.`date`>='{$dateSearch}'
                AND `a`.`symbol` like '%{$symbolS}'
                AND " . implode(' AND ', $whereStandar) . "
            ORDER BY `{$order}` DESC LIMIT 100");
			
        if ($customer) {
            $postMyLists = $this
                    ->Model
                    ->queryAll("SELECT `a`.*,`a`.`id` as `ID`
                FROM `{$table}` as `a`
                WHERE `a`.`rsi` > 0
                AND  `a`.`type_ids` IS NOT NULL
                AND `a`.`date`>='{$dateSearch}'
                AND `a`.`symbol` like '%{$symbolS}'
                AND " . implode(' AND ', $whereMyList) . "
            ORDER BY `{$order}` DESC LIMIT 100");

            if ($_REQUEST['sql']) {
                debug("SELECT `a`.*,`a`.`id` as `ID`
                FROM `{$table}` as `a`
                WHERE `a`.`rsi` > 0
                AND  `a`.`type_ids` IS NOT NULL
                AND `a`.`date`>='{$dateSearch}'
                AND `a`.`symbol` like '%{$symbolS}'
                AND " . implode(' AND ', $whereMyList) . "
            ORDER BY `{$order}` DESC LIMIT 100");
            }


            if ($whereProList) {
                $postMyProLists = $this
                        ->Model
                        ->queryAll("SELECT `a`.*,`a`.`id` as `ID`
                FROM `{$table}` as `a`
                WHERE `a`.`rsi` > 0
                AND  `a`.`type_ids` IS NOT NULL
                AND `a`.`date`>='{$dateSearch}'
                AND `a`.`symbol` like '%{$symbolS}'
                AND " . implode(' AND ', $whereProList) . "
            ORDER BY `{$order}` DESC LIMIT 100");
            }
        } else {
            $postMyLists = [];
            $postMyProLists = [];
        }
        $formulars = $this
                ->Model
                ->queryAll("SELECT * FROM `tb_product_post` WHERE `parent_title`='{$parentTitle}'");

        $arrDatas = [];
        if ($formulars) {
            foreach ($formulars as $a) {
                $arrDatas[$a['ID']] = $a;
            }
        }

        $this->view->formulars = $arrDatas;

        $postCurrent = [];

        if ($ID) {
            $postCurrent = $this
                    ->Model
                    ->getOne($table, "WHERE  `ID`='$ID'");
        }

        if ($posts) {
            foreach ($posts as $k => $a) {
                if ($a['ID'] == $ID || (!$postCurrent && $k == 0)) {
                    $postCurrent = $a;
                }
            }
        }

        if (!$symbol && $postCurrent) {
            $symbol = $postCurrent['symbol'];
        }


        if ($postCurrent) {
            //Xử lý các thông số
            if ($postCurrent['customer_ids']) {
                if ($aclist == 'pro') {
                    $detail = $this->Model->getOne($table_detail, "WHERE `post_id`='{$postCurrent['id']}'
                        AND `customer_id`='{$customer['ID']}' AND `type_id` IN (" . implode(',', $idsPro) . ")");
                } elseif ($aclist == 'my') {
                    $detail = $this->Model->getOne($table_detail, "WHERE `post_id`='{$postCurrent['id']}'
                        AND `customer_id`='{$customer['ID']}' AND `type_id` IN (" . implode(',', $idsMy) . ")");
                } else {
                    $detail = $this->Model->getOne($table_detail, "WHERE `post_id`='{$postCurrent['id']}' AND `customer_id` IS NULL
                        AND `type_id` IN (" . implode(',', $idsAll) . ") ");
                }
            } else {

                $detail = $this->Model->getOne($table_detail, "WHERE `post_id`='{$postCurrent['id']}' AND `customer_id` IS NULL
                        AND `type_id` IN (" . implode(',', $idsAll) . ") ");
            }


            if ($detail) {

                $postCurrent['json_details'] = $detail['json_details'];
                $postCurrent['type_id'] = $detail['type_id'];
                $postCurrent['model_title'] = $detail['model_title'];
                $postCurrent['subID'] = $detail['id'];
                $postCurrent['type'] = $type;
            }
        }



        $this->view->posts = $posts;
        $this->view->postMyLists = $postMyLists;
        $this->view->postMyProLists = $postMyProLists;
        $this->view->postCurrent = $postCurrent;
        $this->view->symbol = $symbol;
        $this->view->tab = $tab;
        $this->view->type = $type;
        $this->view->aclist = $aclist;
        $this->view->histories = $this
                ->Model
                ->queryAll("SELECT *, `id` as `ID`
                    FROM `{$table}`
                    WHERE `type_ids` IS NOT NULL
                    AND `rsi` > 0
                    AND `symbol` = '{$symbol}'
                    AND `ID`<>'{$postCurrent['ID']}'
                    ORDER BY `open_time` DESC LIMIT 20");
        $this->view->save = $this
                ->Model
                ->getOne("stock_histories", "WHERE `customer_id` = '{$customer['ID']}' AND `detail_id` = '{$detail['id']}'");
    }

    public function saveAction() {
        $customer = $_SESSION['cp__customer'];
      
        if (!$customer) {
            die(json_encode([
                'error' => true,
                'notice' => 'You can not save this trade because the price was out of range'
            ]));
        }
        $ID = $this->Plugins->getNum('ID', 0);
        $type = $this->Plugins->get('type', "");

        if ($type == 'H4') {
            $table_detail = 'stock_price_h4_details';
        } else {
            $type = 'D1';
            $table_detail = 'stock_price_details';
        }

        $detail = $this->Model->getOne($table_detail, "WHERE `ID` = '{$ID}'");

        if (!$detail) {
            die(json_encode([
                'error' => true,
                'notice' => 'You can not save this trade because the price was out of range'
            ]));
        }


        $checkHistory = $this->Model->getOne('stock_histories', "WHERE `detail_id` = '{$ID}' AND `customer_id`='{$customer['ID']}'");
        if ($checkHistory) {
            die(json_encode([
                'error' => true,
                'notice' => 'You can not save this trade because the exists in My Dashboard'
            ]));
        }

        $jsonDetails = json_decode($detail['json_details']);

        $date = date('Y-m-d');
        $dateEnd = $_REQUEST['date_to'] ? $_REQUEST['date_to'] : date('Y-m-d');
        $strDateEnd = strtotime($dateEnd) . '000';
        $strDateCheck = (strtotime($date) - 100) . '000';

        $symbol = $detail['symbol'];
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


        $type = preg_match("/Bullish/", $detail['model_title']);
        $entry = $jsonDetails->buylong;
        $target = $jsonDetails->target3;
        $stoploss = $jsonDetails->stoploss;
        $checkReturn = true;
        if ($type) {//Up
            if ($priceReal > $target || $priceReal < $stoploss) {
                $checkReturn = false;
            }
        } else {
            if ($priceReal < $target || $priceReal > $stoploss) {
                $checkReturn = false;
            }
        }


        if (!$checkReturn) {
            die(json_encode([
                'error' => true,
                'notice' => 'You can not save this trade because the price was out of range'
            ]));
        }

        $data = [
            'post_id' => $detail['post_id'],
            'stock_id' => $detail['stock_id'],
            'date' => $detail['date'],
            'symbol' => $detail['symbol'],
        ];
        $data['date_created'] = date("Y-m-d H:i:s");
        $data['type'] = $type;
        $data['status'] = 'WAITING';
        $data['customer_id'] = $customer['ID'];
        $data['detail_id'] = $detail['id'];
        unset($data['id']);

        $data = array_merge($data, [
            'entryzone' => $jsonDetails->buylong,
            'DCA1' => $jsonDetails->target1,
            'DCA2' => $jsonDetails->target2,
            'targetzone' => $target,
            'stoploss' => $stoploss,
        ]);
        $this->Model->insert('stock_histories', $data);

        die(json_encode([
            'error' => false,
            'notice' => 'Your investment is being analyzed in My Dashboard'
        ]));
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
