<?php

class Aipredictionsdashboard_IndexController extends Zend_Controller_Action
{

    public function init()
    {


    }

    public function indexAction()
    {
        $request = $this->getRequest();
        $models = $this->Model;
        $view = $this->view;
        $baseCoins = ["'BTC'", "'ETH'", "'SOL'","'DOT'","'KLAY'"];
        if (in_array(strtoupper($request->getParam('coin')), ['BTC', 'ETH', 'SOL','DOT','KLAY'])) {
            $coin = $request->getParam('coin');
            $baseCoins = ["'$coin'"];
        }
        $quoteCoin = "USDT";
        $startDate = "-1 day";
        if (in_array(strtolower($request->getParam('day')), ['day', 'month', 'week', 'year'])) {
            $startDate = '-1 ' . $request->getParam('day');
        }
        $dayBetween = [strtotime($startDate), strtotime("now")];
        $list_trades = $this->getListTrades($baseCoins, $quoteCoin, $dayBetween);
        $view->list_trades = $list_trades;
        $getTotalTrades = $this->getTotalTrades($baseCoins, $quoteCoin, $dayBetween);
        $total_trades = [
            'wins' => array_sum(array_column($getTotalTrades, 'wins')),
            'losts' => array_sum(array_column($getTotalTrades, 'losts')),
            'max_price_wins' => max(array_filter(array_column($getTotalTrades, 'max_price_wins'))),
            'sum_prices' => array_sum(array_column($getTotalTrades, 'sum_prices')),
            'min_price_wins' => min(array_filter(array_column($getTotalTrades, 'min_price_wins'))),
            'sum_price_preidct_last' => array_sum(array_column($getTotalTrades, 'sum_price_preidct_last')),
        ];
         $priceFirsts = 0;
            foreach ($baseCoins as $coin){
                $coin = str_replace("'","",$coin);
               $arrayCoins = array_filter($list_trades,function ($i) use ($coin){return $i['baseAsset']==$coin;});
               if(!empty($arrayCoins)) {
                   $priceFirsts += end($list_trades)['price_actual_previous'];
               }
            }
        $total_trades['percent_sum_price'] = end($list_trades)['price_actual_previous'] != 0 ? sprintf('%f', $total_trades['sum_prices'] / $priceFirsts) * 100 : 0;

        $view->total_trades = $total_trades;
        $view->listTradeShowCharts = $this->showCharts($getTotalTrades);
        $view->request = $request;
    }

    private function showCharts($getTotalTrades){
        $request = $this->getRequest();
        $labels= [];
        switch (strtolower($request->getParam('day'))){
            case 'week':
                for ($i = 7 ;$i >=0 ; $i--){
                    $time = date('Y-m-d',strtotime("- $i day"));
                    $dayExists = array_values(array_filter($getTotalTrades,function ($item) use ($time){return $item['time_create_format'] == $time;}));
                    $labels[] = [
                      'all_trade'=>!empty($dayExists) ? $dayExists[0]['all_trade'] : 0,
                      'win_trade'=>!empty($dayExists) ? $dayExists[0]['wins'] : 0,
                      'lost_trade'=>!empty($dayExists) ? $dayExists[0]['losts'] : 0,
                       'date'=>date('d/m',strtotime("- $i day"))
                    ];
                }
                break;
            case 'month':
                for ($i = 30 ;$i >=0 ; $i--){
                    $time = date('Y-m-d',strtotime("- $i day"));
                    $dayExists = array_values(array_filter($getTotalTrades,function ($item) use ($time){return $item['time_create_format'] == $time;}));
                    $labels[] = [
                        'all_trade'=>!empty($dayExists) ? $dayExists[0]['all_trade'] : 0,
                        'win_trade'=>!empty($dayExists) ? $dayExists[0]['wins'] : 0,
                        'lost_trade'=>!empty($dayExists) ? $dayExists[0]['losts'] : 0,
                        'date'=>date('d/m',strtotime("- $i day"))
                    ];
                }
                break;
            case 'year':
                for ($i=12; $i >=0;$i--){
                    $time = date('Y-m',strtotime("- $i month"));
                    $dayExists = array_values(array_filter($getTotalTrades,function ($item) use ($time){return $item['time_create_format'] == $time;}));
                    $labels[] = [
                        'all_trade'=>!empty($dayExists) ? $dayExists[0]['all_trade'] : 0,
                        'win_trade'=>!empty($dayExists) ? $dayExists[0]['wins'] : 0,
                        'lost_trade'=>!empty($dayExists) ? $dayExists[0]['losts'] : 0,
                        'date'=>date('m/Y',strtotime("- $i month"))
                    ];
                }
                break;
            default :
                for ($i = 24 ;$i >=0 ; $i--){
                    $time = date('Y-m-d H:00',strtotime("- $i hour"));
                    $dayExists = array_values(array_filter($getTotalTrades,function ($item) use ($time){return $item['time_create_format'] == $time;}));
                    $labels[] = [
                        'all_trade'=>!empty($dayExists) ? $dayExists[0]['all_trade'] : 0,
                        'win_trade'=>!empty($dayExists) ? $dayExists[0]['wins'] : 0,
                        'lost_trade'=>!empty($dayExists) ? $dayExists[0]['losts'] : 0,
                        'date'=>date('H:00',strtotime("- $i hour"))
                    ];
                }
                break;
        }
        return $labels;
    }

    private function getListTrades($baseCoins, $quoteCoin = null, $dayBetween = [])
    {
        $models = $this->Model;
        $baseCoin = implode(',', $baseCoins);
        $caseWin1 = " (price_preidct_last - price_actual_previous >= 0 AND price_actual_last - price_actual_previous >=0) ";
        $caseWin2 = " (price_preidct_last - price_actual_previous < 0 AND price_actual_previous - price_actual_last >=0) ";
        $caseLost1 = " (price_preidct_last - price_actual_previous >= 0 AND price_actual_last - price_actual_previous < 0) ";
        $caseLost2 = " (price_preidct_last - price_actual_previous < 0 AND price_actual_previous - price_actual_last < 0) ";
        $query = "SELECT a.*, c.symbol,c.baseAsset,CASE  WHEN $caseWin1 THEN (price_actual_last - price_actual_previous)
            WHEN $caseWin2 THEN (price_actual_previous - price_actual_last)
            WHEN $caseLost1 THEN (price_actual_last - price_actual_previous)
            WHEN $caseLost2 THEN (price_actual_previous - price_actual_last) END as price_last FROM historical_price_predictions  a
        JOIN coin_info c ON a.id_coin = c.id where a.price_actual_last is not null and c.baseAsset IN ({$baseCoin})";
        if ($quoteCoin) {
            $query .= " and c.quoteAsset='{$quoteCoin}'";
        }
        if (count($dayBetween) > 1) {
            $query .= " and time_create >=" . $dayBetween[0] . " and time_create <=" . $dayBetween[1];
        }
        $query .= " order by time_create desc";
        return $models->queryAll($query);
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
        SUM(CASE WHEN $caseLost1 OR $caseLost2 THEN 1 ELSE 0 END) as losts,
       SUM(CASE
            WHEN $caseWin1 THEN (price_actual_last - price_actual_previous)
            WHEN $caseWin2 THEN (price_actual_previous - price_actual_last)
            END) as sum_price_wins,
        SUM(CASE
            WHEN $caseLost1 THEN (price_actual_last - price_actual_previous)
            WHEN $caseLost2 THEN (price_actual_previous - price_actual_last)
            END) as sum_price_losts,
         SUM(CASE
            WHEN $caseWin1 THEN (price_actual_last - price_actual_previous)
            WHEN $caseWin2 THEN (price_actual_previous - price_actual_last)
            WHEN $caseLost1 THEN (price_actual_last - price_actual_previous)
            WHEN $caseLost2 THEN (price_actual_previous - price_actual_last)
            ELSE 0 END) as sum_prices,
        MAX(CASE
            WHEN $caseWin1 THEN (price_actual_last - price_actual_previous)
            WHEN $caseWin2 THEN (price_actual_previous - price_actual_last)
            END) as max_price_wins,
        MIN(CASE
            WHEN $caseWin1 THEN (price_actual_last - price_actual_previous)
            WHEN $caseWin2 THEN (price_actual_previous - price_actual_last)
            END) as min_price_wins,
       SUM(price_actual_previous) as sum_price_preidct_last,
        FROM_UNIXTIME(time_create , '$dateGroupFormat') as time_create_format
        FROM historical_price_predictions  a
        JOIN coin_info c ON a.id_coin = c.id where a.price_actual_last is not null and c.baseAsset IN({$baseCoin})";

        if (count($dayBetween) > 1) {
            $query .= " and time_create >=" . $dayBetween[0] . " and time_create <=" . $dayBetween[1];
        }
        if ($quoteCoin) {
            $query .= " and c.quoteAsset='{$quoteCoin}'";
        }
        $query .= " GROUP BY time_create_format";
        return $models->queryAll($query);
    }

    private function groupBy($array, $function)
    {
        $dictionary = [];
        if ($array) {
            foreach ($array as $item) {
                $dictionary[$function($item)][] = $item;
            }
        }
        return $dictionary;
    }
}
