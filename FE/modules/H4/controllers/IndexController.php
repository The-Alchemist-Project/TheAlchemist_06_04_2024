<?php

class H4_IndexController extends Zend_Controller_Action {

    public function init() {
        $customer = $_SESSION['cp__customer'];
        if ($customer) {
            if ($customer['type_id'] == 1) {
                $this->_redirect(BASE_URL . "/{$this->language}/kien-thuc");
            }
        }
    }

    public function indexAction() {
        $date = date('Y-m-d');
        $ID = $this->Plugins->getNum('ID', 0);
        $symbol = $this->Plugins->get('symbol', "");
        $pposts = $this
                ->Model
                ->queryAll("SELECT *,`id` as `ID`
                FROM `stock_price_h4` 
                WHERE `rsi` > 0
                AND `symbol` like '%USDT'
                AND  `model_type` IS NOT NULL
            ORDER BY `date_h4` DESC LIMIT 21");

        $ppostBtcs = $this
                ->Model
                ->queryAll("SELECT *,`id` as `ID`
                FROM `stock_price_h4` 
                WHERE `model_type` IS NOT NULL
                AND `rsi` > 0
                AND `symbol` like '%BTC'
            ORDER BY `date_h4` DESC LIMIT 20");


        $formulars = $this
                ->Model
                ->queryAll("SELECT * FROM `tb_product_post` WHERE `parent_title`='H4'");

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
                    ->getOne("stock_price_h4", "WHERE  ID='$ID'");
            $postCurrent = [
                'ID' => $ccCurrent['id'],
                'date' => $ccCurrent['date'],
                'date_h4' => $ccCurrent['date_h4'],
                'open' => round($ccCurrent['open'], 4),
                'high' => round($ccCurrent['high'], 4),
                'low' => round($ccCurrent['low'], 4),
                'close' => round($ccCurrent['close'], 4),
                'model_name' => $ccCurrent['model_title'],
                'model_type' => $ccCurrent['model_type'],
                'rsi' => $ccCurrent['rsi'],
                'json_details' => $ccCurrent['json_details'],
                'type_id' => $ccCurrent['type_id'],
                'title' => $ccCurrent['symbol']
            ];
        }

        $posts = [];
        if ($pposts) {
            foreach ($pposts as $row) {
                $data = [
                    'ID' => $row['ID'],
                    'title' => $row['symbol'],
                    'date_h4' => $row['date_h4'],
                    'date' => $row['date'],
                    'open' => round($row['open'], 4),
                    'high' => round($row['high'], 4),
                    'low' => round($row['low'], 4),
                    'close' => round($row['close'], 4),
                    'model_name' => $row['model_title'],
                    'model_type' => $row['model_type'],
                    'json_details' => $row['json_details'],
                    'type_id' => $row['type_id'],
                    'rsi' => $row['rsi'],
                    'x' => $row['open_time'] / 1000,
                    'y' => [(double) $row['open'] / 1000, (double) $row['high'] / 1000, (double) $row['low'] / 1000, (double) $row['close'] / 1000]
                ];

                if ($data['ID'] == $ID && $ID && !$postCurrent) {
                    $postCurrent = [
                        'ID' => $data['ID'],
                        'date_h4' => $data['date_h4'],
                        'date' => $data['date'],
                        'open' => $data['open'],
                        'high' => $data['high'],
                        'low' => $data['low'],
                        'close' => $data['close'],
                        'model_name' => $data['model_name'],
                        'model_type' => $data['model_type'],
                        'rsi' => $data['rsi'],
                        'json_details' => $row['json_details'],
                        'type_id' => $row['type_id'],
                        'title' => $data['title']
                    ];
                }

                $modelTitle = $data['model_name'];



                if (strtotime($data['date']) >= strtotime('2020-12-01')) {
                    if ($modelTitle) {
                        $pId = $data['ID'];
                        $posts[$data['title']][$data['date_h4']] = [
                            'ID' => $pId,
                            'title' => $data['title'],
                            'sub' => 'High: ' . (double) $data['high'] . ' - ' . 'Low: ' . (double) $data['low'],
                            //'desc' => date("d/m/Y", strtotime($data['date'])),
                            'desc' => static::smartTime($data['date'] . ' 00:00:00', 'd/m/Y'),
                            'date' => $data['date'],
                            'date_h4' => $data['date_h4'],
                            'open' => $data['open'],
                            'close' => $data['close'],
                            'model_name' => $modelTitle,
                            'model_type' => $data['model_type'],
                            'rsi' => $data['rsi'],
                            'json_details' => $row['json_details'],
                            'type_id' => $row['type_id']
                        ];

                        if (!$ID && !$postCurrent) {
                            $postCurrent = [
                                'ID' => $data['ID'],
                                'date_h4' => $data['date_h4'],
                                'date' => $data['date'],
                                'open' => $data['open'],
                                'high' => $data['high'],
                                'low' => $data['low'],
                                'close' => $data['close'],
                                'model_name' => $modelTitle,
                                'model_type' => $data['model_type'],
                                'rsi' => $data['rsi'],
                                'json_details' => $row['json_details'],
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
                    'date_h4' => $row['date_h4'],
                    'date' => $row['date'],
                    'open' => round($row['open'], 4),
                    'high' => round($row['high'], 4),
                    'low' => round($row['low'], 4),
                    'close' => round($row['close'], 4),
                    'model_name' => $row['model_title'],
                    'model_type' => $row['model_type'],
                    'json_details' => $row['json_details'],
                    'type_id' => $row['type_id'],
                    'rsi' => $row['rsi'],
                    'x' => $row['open_time'] / 1000,
                    'y' => [(double) $row['open'] / 1000, (double) $row['high'] / 1000, (double) $row['low'] / 1000, (double) $row['close'] / 1000]
                ];

                if ($data['ID'] == $ID && $ID && !$postCurrent) {
                    $postCurrent = [
                        'ID' => $data['ID'],
                        'date_h4' => $data['date_h4'],
                        'date' => $data['date'],
                        'open' => $data['open'],
                        'high' => $data['high'],
                        'low' => $data['low'],
                        'close' => $data['close'],
                        'model_name' => $data['model_name'],
                        'model_type' => $data['model_type'],
                        'rsi' => $data['rsi'],
                        'json_details' => $row['json_details'],
                        'type_id' => $row['type_id'],
                        'title' => $data['title']
                    ];
                }

                $modelTitle = $data['model_name'];



                if (strtotime($data['date']) >= strtotime('2020-12-01')) {
                    if ($modelTitle) {
                        $pId = $data['ID'];
                        $postBtcs[$data['title']][$data['date_h4']] = [
                            'ID' => $pId,
                            'title' => $data['title'],
                            'sub' => 'High: ' . (double) $data['high'] . ' - ' . 'Low: ' . (double) $data['low'],
                            //'desc' => date("d/m/Y", strtotime($data['date'])),
                            'desc' => static::smartTime($data['date'] . ' 00:00:00', 'd/m/Y'),
                            'date' => $data['date'],
                            'date_h4' => $data['date_h4'],
                            'open' => $data['open'],
                            'close' => $data['close'],
                            'model_name' => $modelTitle,
                            'model_type' => $data['model_type'],
                            'rsi' => $data['rsi'],
                            'json_details' => $row['json_details'],
                            'type_id' => $row['type_id']
                        ];

                        if (!$ID && !$postCurrent) {
                            $postCurrent = [
                                'ID' => $data['ID'],
                                'date_h4' => $data['date_h4'],
                                'date' => $data['date'],
                                'open' => $data['open'],
                                'high' => $data['high'],
                                'low' => $data['low'],
                                'close' => $data['close'],
                                'model_name' => $modelTitle,
                                'model_type' => $data['model_type'],
                                'rsi' => $data['rsi'],
                                'json_details' => $row['json_details'],
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
                ->queryAll("SELECT *,`id` as `ID`
                FROM `stock_price_h4` 
                WHERE `model_type` IS NOT NULL
                AND `rsi` > 0
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
