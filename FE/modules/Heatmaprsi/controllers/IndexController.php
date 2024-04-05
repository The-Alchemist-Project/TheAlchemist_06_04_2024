<?php

class Heatmaprsi_IndexController extends Zend_Controller_Action
{

    public function init()
    {


    }

    public function indexAction()
    {
        $request = $this->getRequest();
        $models = $this->Model;
        $view = $this->view;
        $view->request = $request;
        $date = $this->getDateLast();
        $typeRsi = "rsi";
        $getTopOverSold = $this->getTopOverSold($date, $typeRsi);
        $getTopOverBought = $this->getTopOverBought($date, $typeRsi);
        $view->getTopOverSold = $getTopOverSold;
        $view->getTopOverBought = $getTopOverBought;
        $getListCoins = $this->getListCoins($date,$typeRsi);
        $view->getListCoins = $getListCoins;
       $view->getRsiChangen = $this->getRsiChangen($date,$typeRsi);
       $view->request = $request;
       $id =  $request->getParam('ID') ?? $getTopOverSold[0]['id'] ?? $getTopOverBought[0]['id'];
       $view->coinActive = $this->findStockPrice($id,$typeRsi);
    }

    private function getDateLast()
    {
        $query = "SELECT date FROM stock_price ORDER BY date DESC";
        $result = $this->Model->queryOne($query);
        return !empty($result['date']) ? $result['date'] : date('Y-m-d');
    }

    private function getRsiChangen($date,$typeRsi)
    {
        $query = "SELECT
                today.symbol,
                today.$typeRsi AS rsi_today,
                yesterday.$typeRsi AS rsi_yesterday, 
        Case WHEN yesterday.$typeRsi = 0 THEN 100
            WHEN today.$typeRsi =0 THEN -100
            ELSE ((today.$typeRsi * 100 / yesterday.$typeRsi ) - 100 ) END AS rsi_changen FROM  stock_price today
                JOIN stock_price yesterday ON today.symbol = yesterday.symbol WHERE today.date = '$date'AND yesterday.date = '$date' - INTERVAL 1 DAY order  by symbol";
        $result = $this->Model->queryAll($query);
        return $result;
    }


    private function getListCoins($date,$typeRsi)
    {
        $query = "SELECT *,$typeRsi as rsi_value FROM stock_price where date = '$date' order  by symbol";
        $result = $this->Model->queryAll($query);
        return $result;
    }

    private function getTopOverSold($date, $typeRsi)
    {
//        $query = "SELECT *,$typeRsi as rsi_value FROM stock_price where {$typeRsi} <=30 and date='$date' order by $typeRsi desc limit 30 ";
        $query = "SELECT *,$typeRsi as rsi_value FROM stock_price where {$typeRsi} <=30  order by date desc, $typeRsi desc limit 30 ";
        $result = $this->Model->queryAll($query);
        return $result;
    }

    private function getTopOverBought($date, $typeRsi)
    {
//        $query = "SELECT *,$typeRsi as rsi_value FROM stock_price where {$typeRsi} >=70 and date='$date' order by $typeRsi desc limit 30 ";
        $query = "SELECT *,$typeRsi as rsi_value FROM stock_price where {$typeRsi} >=70  order by date desc, $typeRsi desc limit 30 ";
        $result = $this->Model->queryAll($query);
        return $result;
    }

    private function findStockPrice($id,$typeRsi){
        $query = "SELECT *, $typeRsi as rsi_value FROM stock_price where stock_price.id = '$id'";
        $result = $this->Model->queryOne($query);
        return $result;
    }

}
