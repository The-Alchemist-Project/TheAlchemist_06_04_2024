<?php

class Predictions_IndexController extends Zend_Controller_Action {

    public function indexAction() {
        $predictions = $this->Model->queryAll('SELECT *, `b`.`symbol` FROM `historical_price_predictions` as `a` INNER JOIN `coin_info` as `b` ON `a`.`id_coin` = `b`.`ID` ORDER BY `b`.`symbol`') ?? [];
        $arrs = [];
        $special = [];
        if ($predictions ?? false) {
            foreach ($predictions as $item) {
                $arrs[$item['id_coin']][] = $item;
            }

            if ($arrs ?? false) {
                foreach ($arrs as $idCoin => &$arr) {
                    $maxTime = max(array_column($arr, 'time_create'));
                    foreach ($arr as $a) {
                        if ($a['symbol'] == 'BTCUSDT') {
                            $a['icon'] = "/style/css/images/predictions/{$a['symbol']}.png";
                            $special = $a;
                            unset($arrs[$idCoin]);
                        }
                        if ($a['time_create'] >= $maxTime) {
//                            $a['icon'] = strtolower($a['symbol']);
                            $a['icon'] = "/style/css/images/predictions/{$a['symbol']}.png";
                            $arr = $a;
                        }
                    }
                    
                }
                unset($arr); 
                $arrs = array_merge([$special], $arrs);
                $this->view->datas = $arrs;
            }
        }
    }
}