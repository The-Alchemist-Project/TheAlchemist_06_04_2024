import pandas as pd
import numpy as np
import scipy
import json
from dataclasses import dataclass
from typing import Union
from math import log

from datetime import datetime, date
import pymysql
from sqlalchemy import create_engine


engine = create_engine("mysql+pymysql://dioxtfeq_haods:Syhaobn123@localhost/dioxtfeq_db")

@dataclass
class XABCD:
    XA_AB: Union[float, list, None]
    AB_BC: Union[float, list, None]
    BC_CD: Union[float, list, None]
    XA_AD: Union[float, list, None]
    name: str
    
# Define Patterns
GARTLEY = XABCD(0.618, [0.382, 0.886], [1.13, 1.618], 0.786, "Gartley")
BAT = XABCD([0.382, 0.50], [0.382, 0.886], [1.618, 2.618], 0.886, "Bat")
#ALT_BAT = XABCD(0.382, [0.382, 0.886], [2.0, 3.618], 1.13, "Alt Bat")
BUTTERFLY = XABCD(0.786, [0.382, 0.886], [1.618, 2.24], [1.27, 1.41], "Butterfly")
CRAB = XABCD([0.382, 0.618], [0.382, 0.886], [2.618, 3.618], 1.618, "Crab")
DEEP_CRAB = XABCD(0.886, [0.382, 0.886], [2.0, 3.618], 1.618, "Deep Crab")
CYPHER = XABCD([0.382, 0.618], [1.13, 1.41], [1.27, 2.00], 0.786, "Cypher")
SHARK = XABCD(None, [1.13, 1.618], [1.618, 2.24], [0.886, 1.13], "Shark")
ALL_PATTERNS = [GARTLEY, BAT, BUTTERFLY, CRAB, DEEP_CRAB, CYPHER, SHARK]
# ALL_PATTERNS = [GARTLEY]

@dataclass
class XABCDFound:
    X: int
    A: int
    B: int
    C: int
    D: int # Index of last point in pattern, the entry is on the close of D
    error: float # Error found
    name: str
    bull: bool

def get_error(actual_ratio: float, pattern_ratio: Union[float, list, None]):
     
    if pattern_ratio is None: # No requirement (Shark)
        return 0.0

    log_actual = log(actual_ratio + np.finfo(np.float32).eps)

    if isinstance(pattern_ratio, list): # Acceptable range
        log_pat0 = log(pattern_ratio[0])
        log_pat1 = log(pattern_ratio[1])
        assert(log_pat1 > log_pat0)

        if log_pat0 <= log_actual <= log_pat1:
            return 0.0
        #else:
        #    return 1e20

        err = min( abs(log_actual - log_pat0), abs(log_actual - log_pat1) )
        range_mult = 2.0 # Since range is already more lenient, punish harder. 
        err *= range_mult
        return err

    elif isinstance(pattern_ratio, float):
        err = abs(log_actual - log(pattern_ratio))
        return err
    else:
        raise TypeError("Invalid pattern ratio type")
        
def directional_change(close: np.array, high: np.array, low: np.array, ids: np.array, symbols: np.array, sigma: float):

    up_zig = True # Last extreme is a bottom. Next is a top. 
    tmp_max = high[0]
    tmp_min = low[0]
    tmp_max_i = 0
    tmp_min_i = 0

    tops = []
    bottoms = []

    for i in range(len(close)):
        if up_zig: # Last extreme is a bottom
            if high[i] > tmp_max:
                # New high, update 
                tmp_max = high[i]
                tmp_max_i = i
            elif close[i] < tmp_max - tmp_max * sigma: 
                # Price retraced by sigma %. Top confirmed, record it
                # top[0] = confirmation index
                # top[1] = index of top
                # top[2] = price of top
                top = [i, tmp_max_i, tmp_max, ids[i], symbols[i]]
                tops.append(top)

                # Setup for next bottom
                up_zig = False
                tmp_min = low[i]
                tmp_min_i = i
        else: # Last extreme is a top
            if low[i] < tmp_min:
                # New low, update 
                tmp_min = low[i]
                tmp_min_i = i
            elif close[i] > tmp_min + tmp_min * sigma: 
                # Price retraced by sigma %. Bottom confirmed, record it
                # bottom[0] = confirmation index
                # bottom[1] = index of bottom
                # bottom[2] = price of bottom
                bottom = [i, tmp_min_i, tmp_min, ids[i], symbols[i]]
                bottoms.append(bottom)

                # Setup for next top
                up_zig = True
                tmp_max = high[i]
                tmp_max_i = i

    return tops, bottoms


def get_extremes(ohlc: pd.DataFrame, sigma: float):
    tops, bottoms = directional_change(ohlc['close'].values, ohlc['high'].values, ohlc['low'].values, ohlc['id'].values, ohlc['symbol'].values, sigma)
    tops = pd.DataFrame(tops, columns=['conf_i', 'ext_i', 'ext_p', 'ids', 'symbol'])
    bottoms = pd.DataFrame(bottoms, columns=['conf_i', 'ext_i', 'ext_p', 'ids', 'symbol'])
    tops['type'] = 1
    bottoms['type'] = -1
    extremes = pd.concat([tops, bottoms])
    extremes = extremes.set_index('conf_i')
    extremes = extremes.sort_index()
    return extremes

def find_xabcd(ohlc: pd.DataFrame, extremes: pd.DataFrame, err_thresh: float = 0.2):
    extremes['seg_height'] = (extremes['ext_p'] - extremes['ext_p'].shift(1)).abs()
    extremes['retrace_ratio'] = extremes['seg_height'] / extremes['seg_height'].shift(1) 
    
    output = {}
    for pat in ALL_PATTERNS:
        pat_data = {}
        pat_data['bull_signal'] = np.zeros(len(ohlc))
        pat_data['bull_patterns'] = []
        pat_data['bear_signal'] = np.zeros(len(ohlc))
        pat_data['bear_patterns'] = []
 
        output[pat.name] = pat_data
    
    first_conf = extremes.index[0]
    extreme_i = 0
        
    entry_taken = 0
    pattern_used = None
    for i in range(first_conf, len(ohlc)):
        
        if extremes.index[extreme_i + 1] == i:
            entry_taken = 0
            extreme_i += 1
        
        if entry_taken != 0:
            if entry_taken == 1:
                output[pattern_used]['bull_signal'][i] = 1
            else:
                output[pattern_used]['bear_signal'][i] = -1
            continue
        
        if extreme_i + 1 >= len(extremes):
            break
        
        if extreme_i < 3:
            continue

        ext_type = extremes.iloc[extreme_i]['type']
        last_conf_i = extremes.index[extreme_i]

        
        if extremes.iloc[extreme_i]['type'] > 0.0:  
            # Last extreme was a top, meaning we're on a leg down currently.
            # We are checking for bull patterns
            D_price = ohlc.iloc[i]['low']
            # Check that the current low is the lowest since last confirmed top 
            if ohlc.iloc[last_conf_i:i]['low'].min() < D_price:
                continue
        else:
            # Last extreme was a bottom, meaning we're on a leg up currently.
            # We are checking for bear patterns
            D_price = ohlc.iloc[i]['high']
            # Check that the current high is the highest since last confirmed bottom 
            if ohlc.iloc[last_conf_i:i]['high'].max() > D_price:
                continue

         
        # D_Price set, get ratios
        dc_retrace = abs(D_price - extremes.iloc[extreme_i]['ext_p']) / extremes.iloc[extreme_i]['seg_height'] 
        xa_ad_retrace = abs(D_price - extremes.iloc[extreme_i - 2]['ext_p']) / extremes.iloc[extreme_i - 2]['seg_height']
        
        best_err = 1e30
        best_pat = None
        for pat in ALL_PATTERNS:
            err = 0.0
            err += get_error(extremes.iloc[extreme_i]['retrace_ratio'], pat.AB_BC)
            err += get_error(extremes.iloc[extreme_i - 1]['retrace_ratio'], pat.XA_AB)
            err += get_error(dc_retrace, pat.BC_CD)
            err += get_error(xa_ad_retrace, pat.XA_AD)
            if err < best_err:
                best_err = err
                best_pat = pat.name
        
        if best_err <= err_thresh:
            pattern_data = XABCDFound(
                    int(extremes.iloc[extreme_i - 3]['ext_i']), 
                    int(extremes.iloc[extreme_i - 2]['ext_i']), 
                    int(extremes.iloc[extreme_i - 1]['ext_i']), 
                    int(extremes.iloc[extreme_i]['ext_i']), 
                    i, 
                    best_err, best_pat, True
            )

            pattern_used = best_pat
            if ext_type > 0.0:
                entry_taken = 1
                pattern_data.pretty_name = "Bullish Harmonic " + pattern_data.name
                pattern_data.name = "Bull" + pattern_data.name
                pattern_data.bull = True
                output[pattern_used]['bull_signal'][i] = 1
                output[pattern_used]['bull_patterns'].append(pattern_data)
            else:
                entry_taken = -1
                pattern_data.pretty_name = "Bearish Harmonic " + pattern_data.name
                pattern_data.name = "Bear" + pattern_data.name
                pattern_data.bull = False
                output[pattern_used]['bear_signal'][i] = -1
                output[pattern_used]['bear_patterns'].append(pattern_data)

    return output


def get_xabcd_json(ohlc: pd.DataFrame, pattern_data):
    X, A, B, C, D = pattern_data.X, pattern_data.A, pattern_data.B, pattern_data.C, pattern_data.D
    filt = ohlc.iloc[[X, A, B, C, D], :].reset_index(drop=True)
    filt["point"] = ["X", "A", "B", "C", "D"]
    filt = filt.set_index("point")
    
    return json.loads(filt.to_json(orient="index"))
    
    
# data
harmonic_df = None
try:
    harmonic_df = pd.read_sql("select * from harmonic_pattern_d1", engine)
except:
    pass

df = pd.read_sql("select id, symbol, open, high, low, close, date from stock_price where open_time >= 1684108800000 order by symbol asc, open_time asc", engine)

df[["open", "high", "low", "close"]] = df[["open", "high", "low", "close"]].astype(np.float32)

out_data = {
    "pattern": [],
    "pattern_name": [],
    "symbol": [],
    "XABCD_details": [],
    "id_X": [],
    "date_found": []
}

for symbol_name, symbol in df.groupby("symbol"):
    symbol = symbol.sort_values("date", ascending=True)[["id", "symbol", "open", "high", "low", "close", "date"]].reset_index(drop=True)
    last_pat = None
    try:
        hsdf = pd.read_sql(f"select max(id_X) as maxa from harmonic_pattern_d1 where symbol = '{symbol_name}'", engine)
        last_pat = hsdf.maxa.values[0]
    except:
        pass
    
    if last_pat is not None:
        symbol = symbol.query("id >= @last_pat")

    sigma = 0.01
    extremes = get_extremes(symbol, sigma)
    
    if (extremes.shape[0] < 3): continue

    output =  find_xabcd(symbol, extremes, 0.5)
    
    
    for pat in ALL_PATTERNS:
        all_pats = output[pat.name]["bear_patterns"] + output[pat.name]["bull_patterns"]
    
        for xabcd in all_pats:
            json_dat = get_xabcd_json(symbol, xabcd)
            
            out_data["pattern"].append(xabcd.name)
            out_data["pattern_name"].append(xabcd.pretty_name)
            out_data["symbol"].append(json_dat["X"]["symbol"])
            out_data["XABCD_details"].append(str(json_dat))
            out_data["id_X"].append(json_dat["X"]["id"])
            out_data["date_found"].append(datetime.now().strftime("%m/%d/%Y, %H:%M:%S"))
 
out_df = pd.DataFrame(data=out_data)
out_df.to_sql("harmonic_pattern_d1", index=False, con=engine, if_exists="append") 
