import requests
import psycopg2

# API URL
url = "https://api.mobula.io/market/query"

# Trading pairs to fetch data for
trading_pairs = ['wbtcusdt', 'wethusdt', 'wsolusdt', 'wavaxusdt', 'wetcusdt', 'wdotusdt']

# Database connection information
db_config = {
    "user": "dioxtfeq_haods",
    "password": "Syhaobn123@",
    "host": "localhost",
    "database": "dioxtfeq_db"
}

# Connect to the database
conn = psycopg2.connect(**db_config)
cursor = conn.cursor()

# Create table if not exists
create_table_query = """
CREATE TABLE IF NOT EXISTS coin_price_1h (
    id SERIAL PRIMARY KEY,
    pair_symbol VARCHAR(50),
    price NUMERIC,
    total_txHash_1hour INTEGER,
    trading_volume_1hour NUMERIC,
    tvl NUMERIC
);
"""
cursor.execute(create_table_query)
conn.commit()

# Fetch data from the API and insert into the database
for pair_symbol in trading_pairs:
    params = {
        "pair_symbol": pair_symbol
    }
    response = requests.get(url, params=params)
    if response.status_code == 200:
        data = response.json()
        price = data['price']
        total_txHash_1hour = data['total_txHash_1hour']
        trading_volume_1hour = data['trading_volume_1hour']
        tvl = data['tvl']
        
        # Insert data into the database
        insert_query = """
        INSERT INTO coin_price_1h (pair_symbol, price, total_txHash_1hour, trading_volume_1hour, tvl)
        VALUES (%s, %s, %s, %s, %s);
        """
        cursor.execute(insert_query, (pair_symbol, price, total_txHash_1hour, trading_volume_1hour, tvl))
        conn.commit()
    else:
        print(f"Failed to fetch data for {pair_symbol}")

# Close the database connection
cursor.close()
conn.close()
