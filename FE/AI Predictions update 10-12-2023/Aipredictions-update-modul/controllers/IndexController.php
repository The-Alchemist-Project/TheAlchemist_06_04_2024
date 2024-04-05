<?php

class Aipredictions_IndexController extends Zend_Controller_Action {

    public function init() {
		
        
    }

    public function indexAction() {
        $request = $this->getRequest();
        $models = $this->Model;
        $view = $this->view;
        $quoteCoin = "USDT";
        $startDate = "-1 day";
        $coinsSelect = $this->coinsSelect($quoteCoin);
        $baseCoins = [];
        if(empty($_SESSION['cp__customer'])){
            $baseCoins = ["'BTC'", "'ETH'", "'SOL'","'DOT'","'KLAY'"];
        }
        if (in_array(strtolower($request->getParam('day')), ['day', 'month', 'week', 'year'])) {
            $startDate = '-1 ' . $request->getParam('day');
        }
        $dayBetween = [strtotime($startDate), strtotime("now")];
        $getTotalTrades = $this->getTotalTrades($baseCoins, $quoteCoin, $dayBetween);
        $view->getTotalTrades = $getTotalTrades;
        $view->request = $request;
        $getCoinTradeLasts = $this->getCoinsTradeLasts($getTotalTrades);
        $view->getCoinTradeLasts= $getCoinTradeLasts;
        $view->coinsSelect = $coinsSelect;
        $view->quoteCoin = $quoteCoin;
    }


    private function getTotalTrades($baseCoins, $quoteCoin = null, $dayBetween = [])
    {
        $baseCoin = implode(',', $baseCoins);
        $models = $this->Model;
        $caseWin1 = " (price_preidct_last - price_actual_previous >= 0 AND price_actual_last - price_actual_previous >=0) ";
        $caseWin2 = " (price_preidct_last - price_actual_previous < 0 AND price_actual_previous - price_actual_last >=0) ";
        $caseLost1 = " (price_preidct_last - price_actual_previous >= 0 AND price_actual_last - price_actual_previous < 0) ";
        $caseLost2 = " (price_preidct_last - price_actual_previous < 0 AND price_actual_previous - price_actual_last < 0) ";

        $request = $this->getRequest();
        switch (strtolower($request->getParam('day'))){
            case 'week':
                $dateGroupFormat = "%Y-%m-%d";
                break;
            case 'month':
                $dateGroupFormat = "%Y-%m-%d";
                break;
            case 'year':
                $dateGroupFormat = "%Y-%m";
                break;
            default :
                $dateGroupFormat = "%Y-%m-%d %H:00";
                break;
        }

        $query = "SELECT
        Count(*) as all_trade,
        SUM(CASE WHEN $caseWin1 OR $caseWin2 THEN 1 ELSE 0 END) as wins,
       MAX(a.time_create) as time_create,
       Max(price_actual_previous) as price_previous,
        c.symbol as symbol,
        id_coin
        FROM historical_price_predictions  a
        JOIN coin_info c ON a.id_coin = c.id where a.price_actual_last is not null ";

//        if (count($dayBetween) > 1) {
//            $query .= " and time_create >=" . $dayBetween[0] . " and time_create <=" . $dayBetween[1];
//        }
        if(!empty($baseCoins)){
            $query.="  and c.baseAsset IN ({$baseCoin}) ";
        }
        if ($quoteCoin) {
            $query .= " and c.quoteAsset='{$quoteCoin}'";
        }
        $query .= " GROUP BY symbol,id_coin order by price_previous desc";
        return $models->queryAll($query);
    }

    private function getCoinsTradeLasts($getTotalTrades){
        $array = [];
        $models = $this->Model;
       foreach ($getTotalTrades as $trade){
           $idCoin = $trade['id_coin'];
           $time_create = $trade['time_create'];
           $query = "SELECT * FROM historical_price_predictions where id_coin=$idCoin and time_create=$time_create";
           $array[$idCoin] = $models->queryOne($query);
       }
       return $array;
    }

    private function coinsSelect($quoteCoin)
    {
        $query = "SELECT DISTINCT c.baseAsset as 'key' FROM coin_info c JOIN  historical_price_predictions  a ON a.id_coin = c.id where c.quoteAsset='{$quoteCoin}' ";
        $result = $this->Model->queryAll($query);
        $coinInfos = require(APPLICATION_PATH . '/coin_info.php');
        return array_map(function ($item) use ($coinInfos) {
            $item['name'] = $coinInfos[$item['key']];
            $item['img'] = BASE_URL."/style/img/coin_image/{$item['key']}.svg";
            return $item;
        }, $result);
    }
}
