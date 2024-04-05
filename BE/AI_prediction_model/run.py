from subprocess import Popen, PIPE, STDOUT
from connectDB import ConnectDB
import time

ls_coin_str = "('BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'XRPUSDT', 'ADAUSDT', 'DOGEUSDT', 'MATICUSDT', 'SOLUSDT', 'DOTUSDT', 'TRXUSDT', 'LTCUSDT', 'AVAXUSDT', 'LINKUSDT', 'ATOMUSDT', 'UNIUSDT', 'XRMUSDT', 'OKBUSDT', 'ETCUSDT', 'TONUSDT', 'XLMUSDT', 'DOTUSDT', 'KLAYUSDT')"

if __name__ == '__main__':
    start_time_main = time.time()
    DB = ConnectDB()
    # list_coin = DB.get_list_coin_info("ETH")
    list_coin = DB.get_coin_from_list(ls_coin_str)
    for coin in list_coin:
        print(coin)
        try:
            start_time = time.time()
            p = Popen('python3 train.py -id %s -symbol %s'%(coin[1], coin[0]), shell=True, 
                stdout=PIPE, stderr=STDOUT)
            retval = p.wait()
            print("Time train: %f" %(time.time() - start_time))
            time.sleep(1)
        except Exception as e:
            print(e)
        
    print("Total time train: %f" %(time.time() - start_time_main))