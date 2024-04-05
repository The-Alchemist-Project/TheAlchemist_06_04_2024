from binance.client import Client
import numpy as np
import pandas as pd
import time
from datetime import datetime, date
import pymysql
from sqlalchemy import create_engine
from connectDB import ConnectDB

from config_db import config_db


glob_start_timestamp = 1684108800000 - 1 # int(datetime.fromisoformat("2023-05-15").timestamp()) * 1000

engine = create_engine("mysql+pymysql://dioxtfeq_haods:Syhaobn123@localhost/dioxtfeq_db")

crawl_features = ["stock_id", "date", "symbol", "open_time", "open", "high", "low", "close", "volume", \
            "close_time", "asset_volume", "number_trade", "base_asset", "quote_asset", "ignore", "date_created", "rsi", "rsi14"]
            
ls_coin_ls = ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'XRPUSDT', 'ADAUSDT', 'DOGEUSDT', 'MATICUSDT', 'SOLUSDT', 'DOTUSDT', 'TRXUSDT', 'LTCUSDT', 'AVAXUSDT', 'LINKUSDT', 'ATOMUSDT', 'UNIUSDT', 'XRMUSDT', 'OKBUSDT', 'ETCUSDT', 'TONUSDT', 'XLMUSDT']


def update_rsi(open_time, symbol, rsi, rsi14):
    flag = 0
    cnx = config_db()
    cursor = cnx.cursor()
    query_string = "UPDATE test_stock_price \
        SET	rsi = %s, \
            rsi14 = %s \
        WHERE open_time = '%s' \
        AND symbol = '%s'" %(rsi, rsi14, open_time, symbol)
    try:
        cursor.execute(query_string)
        cnx.commit()
        flag = 1
    except mysql.Error as err:
        cnx.rollback()
        print("Something went wrong: {}".format(err))
    cursor.close()
    cnx.close()
    del cursor
    del cnx
    return flag
    

class Processor(object):
    def __init__(self):
        self.client = Client()
        self.db = ConnectDB()
        self.run_sample = True
        
    def compute_rsi(self, ohlc: pd.DataFrame, period: int = 14):
        delta = ohlc["close"].astype(float).diff()
    
        up, down = delta.copy(), delta.copy()
        up[up < 0] = 0
        down[down > 0] = 0
    
        _gain = up.ewm(com=(period - 1), min_periods=period).mean()
        _loss = down.abs().ewm(com=(period - 1), min_periods=period).mean()
    
        rs = _gain / _loss
        
        return (100 - (100 / (1 + rs))).values
        
    def run_compute_rsi(self):
        open_time_ls = []
        symbol_ls = []
        rsi_ls = []
        rsi14_ls = []
        update_cnt = 0
        
        df = pd.read_sql("select * from test_stock_price where open_time >= 1684108800000 order by symbol asc, open_time asc", engine)
        df = df[crawl_features]
        
        for _, symbol_df in df.groupby("symbol"):
            symbol_df = symbol_df.sort_values("open_time").reset_index(drop=True)
            
            if symbol_df.iloc[-2, -1] != None:
                if symbol_df.iloc[-1, -1] == None:
                    symbol_df = symbol_df.tail(15)
                else:
                    symbol_df = symbol_df.head(0)
                
 
            symbol_df["rsi"] = self.compute_rsi(symbol_df, 7)
            symbol_df["rsi14"] = self.compute_rsi(symbol_df, 14)
            
            if symbol_df.shape[0] == 15:
                update_item = symbol_df.iloc[-1, :]
                open_time_ls.append(update_item.open_time)
                symbol_ls.append(update_item.symbol)
                rsi_ls.append(update_item.rsi)
                rsi14_ls.append(update_item.rsi14)
            else: # this run only the first time
                for _, it in symbol_df.iterrows(): 
                    open_time_ls.append(it.open_time)
                    symbol_ls.append(it.symbol)
                    rsi_ls.append(it.rsi)
                    rsi14_ls.append(it.rsi14)
        
        # write to db
        update_cnt = 0
        for open_time, symbol, rsi, rsi14 in zip(open_time_ls, symbol_ls, rsi_ls, rsi14_ls):
            print(symbol, open_time)
            if np.isnan(rsi): rsi = "NULL"
            if np.isnan(rsi14): rsi14 = "NULL"
            update_cnt += update_rsi(open_time, symbol, rsi, rsi14)
            
        print(f"Done updating {update_cnt} rows to db!")


def main():
    processor = Processor()
    print(glob_start_timestamp)
    
    df = processor.run_compute_rsi()


if __name__ == '__main__':
    main()