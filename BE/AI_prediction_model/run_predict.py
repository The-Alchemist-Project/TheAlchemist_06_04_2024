from subprocess import Popen, PIPE, STDOUT
from connectDB import ConnectDB
from crawl_data_binance import crawlerDataBinance
import time

ls_coin_str = "('BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'XRPUSDT', 'ADAUSDT', 'DOGEUSDT', 'MATICUSDT', 'SOLUSDT', 'DOTUSDT', 'TRXUSDT', 'LTCUSDT', 'AVAXUSDT', 'LINKUSDT', 'ATOMUSDT', 'UNIUSDT', 'XRMUSDT', 'OKBUSDT', 'ETCUSDT', 'TONUSDT', 'XLMUSDT', 'KLAYUSDT')"

ls_coin_ls = ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'XRPUSDT', 'ADAUSDT', 'DOGEUSDT', 'MATICUSDT', 'SOLUSDT', 'DOTUSDT', 'TRXUSDT', 'LTCUSDT', 'AVAXUSDT', 'LINKUSDT', 'ATOMUSDT', 'UNIUSDT', 'XRMUSDT', 'OKBUSDT', 'ETCUSDT', 'TONUSDT', 'XLMUSDT', 'KLAYUSDT']

# ls_coin_str = "('KLAYUSDT')"
# ls_coin_ls = ['KLAYUSDT']



if __name__ == '__main__':
    start_time = time.time()
    # get data from Binance
    crawler = crawlerDataBinance()
    # crawler.insert_coin_info_to_db()
    crawler.insert_symbols_candlestick_data(syms=ls_coin_ls)
    print("Total time get data: %f"%(time.time() - start_time))
    start_time = time.time()
    # predict data
    DB = ConnectDB()
    # list_coin = DB.get_list_coin_info("ETH")
    list_coin = DB.get_coin_from_list(ls_coin_str)
    for i, coin in enumerate(list_coin):
        print(i, coin)
        try:
            p = Popen('python3 predict.py -id %s -symbol %s'%(coin[1], coin[0]), shell=True, 
                stdout=PIPE, stderr=STDOUT)
            retval = p.wait()
        except Exception as e:
            print(e)
    print("Total time predict: %f" %(time.time() - start_time))
