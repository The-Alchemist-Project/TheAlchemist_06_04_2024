from binance.client import Client
import numpy as np
import pandas as pd
import time
from datetime import datetime, date
import pymysql
from sqlalchemy import create_engine


glob_start_timestamp = 1684108800000 - 1 # int(datetime.fromisoformat("2023-05-15").timestamp()) * 1000

engine = create_engine("mysql+pymysql://dioxtfeq_haods:Syhaobn123@localhost/dioxtfeq_db")

crawl_features = ["open_time", "open", "high", "low", "close", "volume", \
            "close_time", "asset_volume", "number_trade", "base_asset", \
            "quote_asset", "ignore"]
            
ls_coin_ls = ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'XRPUSDT', 'ADAUSDT', 'DOGEUSDT', 'MATICUSDT', 'SOLUSDT', 'DOTUSDT', 'TRXUSDT', 'LTCUSDT', 'AVAXUSDT', 'LINKUSDT', 'ATOMUSDT', 'UNIUSDT', 'XRMUSDT', 'OKBUSDT', 'ETCUSDT', 'TONUSDT', 'XLMUSDT']



class Crawler(object):
    def __init__(self):
        self.client = Client()
        self.run_sample = True
        
    
    def get_coin_info(self):
        query_str = "select symbol from stock"
        fdf = pd.read_sql(query_str, con=engine)
        
        if self.run_sample:
            fdf = fdf.query("symbol in @ls_coin_ls").reset_index(drop=True)
        
        return fdf.symbol.values.flatten()
        
    def get_last_update_timestamp(self, symbol):
        query_str = f'select max(open_time) from test_stock_price where symbol = "{symbol}"'
        fdf = pd.read_sql(query_str, con=engine)
        
        return int(fdf.values.flatten()[0])
        
        
    def get_klines(self, symbol, start=None):
        return self.client.get_klines(symbol=symbol,
                interval=self.client.KLINE_INTERVAL_1DAY, 
                startTime=start,
                limit=500)
                
    def run_crawl_stock_price(self):
        symbols = self.get_coin_info()
        
        cnt_dump = 0
        
        for symbol in symbols:
            print(symbol)
            last_update_timestamp = self.get_last_update_timestamp(symbol)
            start_timestamp = glob_start_timestamp
            
            if last_update_timestamp > glob_start_timestamp: 
                start_timestamp = last_update_timestamp
                
            klines = self.get_klines(symbol, start_timestamp + 1)
            klines_df = pd.DataFrame(data=klines, columns=crawl_features)
            dates = klines_df.open_time.apply(lambda x: datetime.fromtimestamp(int(x) / 1000).date())
            klines_df.insert(0, "stock_id", 1)
            klines_df.insert(1, "date", dates)
            klines_df.insert(2, "symbol", symbol)
            klines_df.insert(klines_df.shape[1], "date_created", datetime.now().strftime("%Y/%m/%d %H:%M:%S"))
            
            # write to db
            klines_df.to_sql('test_stock_price', con=engine, if_exists='append', chunksize=500, index=False)
            
            cnt_dump += klines_df.shape[0]
            
        print(f"Done writing {cnt_dump} rows to db!")
                

def main():
    crawler = Crawler()
    print(glob_start_timestamp)
    crawler.run_crawl_stock_price()


if __name__ == '__main__':
    main()