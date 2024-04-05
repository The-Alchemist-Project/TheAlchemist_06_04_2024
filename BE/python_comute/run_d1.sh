#!/bin/bash
cd $HOME

source virtualenv/alchemist/3.7/bin/activate

cd predict_cryptos

python crawl_stock_price.py > /tmp/crawler_stock_price.log 2>&1 &
echo "Done crawl stock price h1"

python run_compute_rsi.py > /tmp/compute_rsi.log 2>&1 &
echo "Done compute rsi"

cd $HOME/algo_compute

python search_user_strats.py > /tmp/search_user_strats.log 2>&1 &
echo "Done search user strats"

