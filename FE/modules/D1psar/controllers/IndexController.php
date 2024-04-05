<?php

class D1psar_IndexController extends Zend_Controller_Action {

    public function init() {
        if(!$_SESSION['cp__customer'])
        $this->_redirect( BASE_URL."/{$this->language}/kien-thuc" );
    }

    public function indexAction() {
        $date = date('Y-m-d');
        $ID = $this->Plugins->getNum('ID', 0);
        $symbol = $this->Plugins->get('symbol', "");
        $pposts = $this
                ->Model
                ->queryAll("SELECT *,`id` as `ID`
                FROM `stock_price` 
                WHERE `rsi` > 0
                AND `symbol` like '%USDT'
                AND  `psar_model_title`<>''
            ORDER BY `date` DESC LIMIT 21");

        $ppostBtcs = $this
                ->Model
                ->queryAll("SELECT *,`id` as `ID`
                FROM `stock_price` 
                WHERE `psar_model_title`<>''
                AND `symbol` like '%BTC'
            ORDER BY `date` DESC LIMIT 20");


        $formulars = $this
                ->Model
                ->queryAll("SELECT * FROM `tb_product_post` WHERE `parent_title`='D1PSAR'");

        $arrDatas = [];
        if ($formulars) {
            foreach ($formulars as $a) {
                $arrDatas[$a['ID']] = $a;
            }
        }

        $this->view->formulars = $arrDatas;


        $posts = [];
        $postCurrent = [];


        if ($ID) {
            $ccCurrent = $this
                    ->Model
                    ->getOne("stock_price", "WHERE  ID='$ID'");
            $postCurrent = [
                'ID' => $ccCurrent['id'],
                'date' => $ccCurrent['date'],
                'open' => round($ccCurrent['open'], 4),
                'high' => round($ccCurrent['high'], 4),
                'low' => round($ccCurrent['low'], 4),
                'close' => round($ccCurrent['close'], 4),
                'close_time' => ($ccCurrent['close_time'] + 1) / 1000,
                'rsi' => $ccCurrent['rsi'],
                'model_name' => $ccCurrent['psar_model_title'],
                'model_type' => $ccCurrent['psar_model_type'],
                'json_details' => $ccCurrent['psar_json_details'],
                'type_id' => $ccCurrent['psar_type_id'],
                'title' => $ccCurrent['symbol']
            ];
        }

        $posts = [];
        if ($pposts) {
            foreach ($pposts as $row) {
                $data = [
                    'ID' => $row['ID'],
                    'title' => $row['symbol'],
                    'date' => $row['date'],
                    'open' => round($row['open'], 4),
                    'high' => round($row['high'], 4),
                    'low' => round($row['low'], 4),
                    'close' => round($row['close'], 4),
                    'close_time' => ($row['close_time'] + 1) / 1000,
                    'model_name' => $row['psar_model_title'],
                    'model_type' => $row['psar_model_type'],
                    'json_details' => $row['psar_json_details'],
                    'type_id' => $row['psar_type_id'],
                    'rsi' => $row['rsi'],
                    'x' => $row['open_time'] / 1000,
                    'y' => [(double) $row['open'] / 1000, (double) $row['high'] / 1000, (double) $row['low'] / 1000, (double) $row['close'] / 1000]
                ];

                if ($data['ID'] == $ID && $ID && !$postCurrent) {
                    $postCurrent = [
                        'ID' => $data['ID'],
                        'date' => $data['date'],
                        'open' => $data['open'],
                        'high' => $data['high'],
                        'low' => $data['low'],
                        'close' => $data['close'],
                        'close_time' => ($row['close_time'] + 1) / 1000,
                        'model_name' => $data['model_name'],
                        'model_type' => $data['model_type'],
                        'rsi' => $data['rsi'],
                        'json_details' => $row['psar_json_details'],
                        'type_id' => $row['psar_type_id'],
                        'title' => $data['title']
                    ];
                }

                $modelTitle = $data['model_name'];



                if (strtotime($data['date']) >= strtotime('2020-12-01')) {
                    if ($modelTitle) {
                        $pId = $data['ID'];
                        $posts[$data['title']][$data['date']] = [
                            'ID' => $pId,
                            'title' => $data['title'],
                            'sub' => 'High: ' . (double) $data['high'] . ' - ' . 'Low: ' . (double) $data['low'],
                            //'desc' => date("d/m/Y", strtotime($data['date'])),
                            'desc' => static::smartTime($data['date'] . ' 00:00:00', 'd/m/Y'),
                            'date' => $data['date'],
                            'open' => $data['open'],
                            'close' => $data['close'],
                            'close_time' => ($row['close_time'] + 1) / 1000,
                            'model_name' => $modelTitle,
                            'model_type' => $data['model_type'],
                            'rsi' => $data['rsi'],
                            'json_details' => $row['json_details'],
                            'type_id' => $row['psar_type_id']
                        ];

                        if (!$ID && !$postCurrent) {
                            $postCurrent = [
                                'ID' => $data['ID'],
                                'date' => $data['date'],
                                'open' => $data['open'],
                                'high' => $data['high'],
                                'low' => $data['low'],
                                'close' => $data['close'],
                                'close_time' => ($row['close_time'] + 1) / 1000,
                                'model_name' => $modelTitle,
                                'model_type' => $data['model_type'],
                                'type_id' => $data['type_id'],
                                'rsi' => $data['rsi'],
                                'json_details' => $row['psar_json_details'],
                                'title' => $data['title']
                            ];
                        }
                    }
                }
            }
        }

        $postBtcs = [];
        if ($ppostBtcs) {
            foreach ($ppostBtcs as $row) {
                $data = [
                    'ID' => $row['ID'],
                    'title' => $row['symbol'],
                    'date' => $row['date'],
                    'open' => round($row['open'], 4),
                    'high' => round($row['high'], 4),
                    'low' => round($row['low'], 4),
                    'close' => round($row['close'], 4),
                    'close_time' => ($row['close_time'] + 1) / 1000,
                    'model_name' => $row['psar_model_title'],
                    'model_type' => $row['psar_model_type'],
                    'json_details' => $row['psar_json_details'],
                    'type_id' => $row['psar_type_id'],
                    'rsi' => $row['rsi'],
                    'x' => $row['open_time'] / 1000,
                    'y' => [(double) $row['open'] / 1000, (double) $row['high'] / 1000, (double) $row['low'] / 1000, (double) $row['close'] / 1000]
                ];

                if ($data['ID'] == $ID && $ID && !$postCurrent) {
                    $postCurrent = [
                        'ID' => $data['ID'],
                        'date' => $data['date'],
                        'open' => $data['open'],
                        'high' => $data['high'],
                        'low' => $data['low'],
                        'close' => $data['close'],
                        'close_time' => ($row['close_time'] + 1) / 1000,
                        'model_name' => $data['model_name'],
                        'model_type' => $data['model_type'],
                        'type_id' => $data['type_id'],
                        'rsi' => $data['rsi'],
                        'json_details' => $row['psar_json_details'],
                        'type_id' => $row['psar_type_id'],
                        'title' => $data['title']
                    ];
                }

                $modelTitle = $data['model_name'];



                if (strtotime($data['date']) >= strtotime('2020-12-01')) {
                    if ($modelTitle) {
                        $pId = $data['ID'];
                        $postBtcs[$data['title']][$data['date']] = [
                            'ID' => $pId,
                            'title' => $data['title'],
                            'sub' => 'High: ' . (double) $data['high'] . ' - ' . 'Low: ' . (double) $data['low'],
                            //'desc' => date("d/m/Y", strtotime($data['date'])),
                            'desc' => static::smartTime($data['date'] . ' 00:00:00', 'd/m/Y'),
                            'date' => $data['date'],
                            'open' => $data['open'],
                            'close' => $data['close'],
                            'close_time' => ($row['close_time'] + 1) / 1000,
                            'model_name' => $modelTitle,
                            'model_type' => $data['model_type'],
                            'rsi' => $data['rsi'],
                            'json_details' => $row['psar_json_details'],
                            'type_id' => $row['psar_type_id']
                        ];

                        if (!$ID && !$postCurrent) {
                            $postCurrent = [
                                'ID' => $data['ID'],
                                'date' => $data['date'],
                                'open' => $data['open'],
                                'high' => $data['high'],
                                'low' => $data['low'],
                                'close' => $data['close'],
                                'close_time' => ($row['close_time'] + 1) / 1000,
                                'model_name' => $modelTitle,
                                'model_type' => $data['model_type'],
                                'type_id' => $data['type_id'],
                                'rsi' => $data['rsi'],
                                'json_details' => $row['psar_json_details'],
                                'title' => $data['title']
                            ];
                        }
                    }
                }
            }
        }

        if (!$symbol && $postCurrent) {
            $symbol = $postCurrent['title'];
        }

        $this->view->posts = $posts;
        $this->view->postBtcs = $postBtcs;
        $this->view->postCurrent = $postCurrent;
        $this->view->symbol = $symbol;
        $this->view->histories = $this
                ->Model
                ->queryAll("SELECT *,`psar_model_type` as `model_type`,`id` as `ID`
                FROM `stock_price` 
                WHERE `psar_model_type` IS NOT NULL
                AND `psar` > 0
                AND `symbol` ='{$symbol}'
                    AND `ID`<>'{$postCurrent['ID']}'
            ORDER BY `open_time` DESC LIMIT 20");
    }

    public static function smartTime($time, $father = 30, $format = 'd/m/Y') {
        if ($time === null) {
            return '';
        }

        if (!is_numeric($time))
            $time = strtotime($time);

        $diff = time() - $time;

        if ($diff > $father * 24 * 3600)
            return date($format, $time);

        if ($diff < 0) {
            return date($format, $time);
        } else if ($diff == 0) {
            return "vừa mới";
        } else if ($diff < 60) {
            return (floor($diff) . ' giây trước');
        } else if ($diff < 60 * 60) {
            return (floor($diff / 60) . ' phút trước');
        } else if ($diff < 60 * 60 * 24) {
            return (floor($diff / (60 * 60)) . ' giờ trước');
        } else if ($diff < 60 * 60 * 24 * 30) {
            $num_day = $diff / (60 * 60 * 24);
            if ($num_day < 2 && $num_day >= 1)
                return 'Hôm qua';
            if ($num_day >= 2 && $num_day < 3)
                return 'Hôm trước';
            return floor($num_day) . ' ngày trước';
        } else if ($diff < 60 * 60 * 24 * 30 * 12) {
            return (floor($diff / (60 * 60 * 24 * 30)) . ' tháng trước');
        } else {
            return (floor($diff / (60 * 60 * 24 * 30 * 12)) . ' năm trước');
        }

        return date($format, $time);
    }

}
