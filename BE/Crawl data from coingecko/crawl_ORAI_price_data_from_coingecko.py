import requests
import pandas as pd
from datetime import datetime
from sqlalchemy import create_engine


engine = create_engine("mysql+pymysql://dioxtfeq_haods:Syhaobn123@localhost/dioxtfeq_db")
id = "orai"


def get_realtime_price_gecko(id: str, vs='usd'):
    url = 'https://api.coingecko.com/api/v3/simple/price'
    params = {
        'ids': id,
        'vs_currencies': vs,
        'include_market_cap': 'true',
        'include_24hr_vol': 'true',
        'include_24hr_change': 'true',
        'include_last_updated_at': 'true'
    }
    headers = {
        'accept': 'application/json'
    }

    response = requests.get(url, params=params, headers=headers)
    json_data = response.json()
    symbol = json_data.keys

    info = json_data[symbol]

    return symbol, info["usd"], info["usd_market_cap"], info["usd_24h_vol"], info["usd_24h_change"], info["last_updated_at"]
    
    
df = pd.DataFrame(columns=["symbol", "usd", "volume24H", "percentChange24H", "createdAt"])

symbol, usd, volume24, percent24, createat = get_realtime_price_gecko(id)

idx = len(df)
df.loc[idx, "symbol"] = symbol
df.loc[idx, "usd"] = usd
df.loc[idx, "volume24H"] = volume24
df.loc[idx, "percentChange24H"] = percent24
df.loc[idx, "createAt"] = createat

print(df)
df.to_sql("gecko_price", index=False, con=engine, if_exists="append")
