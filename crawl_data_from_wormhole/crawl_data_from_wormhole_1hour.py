import requests
import psycopg2

# Function to retrieve data from the Wormhole API
def get_data_from_wormhole(pair_symbol):
    url = f"https://api.wormhole.com/wormhole/market/v1/trading_pairs/{pair_symbol}/metrics"
    headers = {
        'Content-Type': 'application/json'
        # Add any required headers, such as authentication headers, if applicable
    }
    
    try:
        response = requests.get(url, headers=headers)
        if response.status_code == 200:
            data = response.json()
            return data
        else:
            print(f"Failed to retrieve data for {pair_symbol}. Status code: {response.status_code}")
            return None
    except Exception as e:
        print(f"Error occurred: {e}")
        return None

# Function to insert data into the database
def insert_data_into_database(pair_symbol, data):
    # Establish database connection
    try:
        connection = psycopg2.connect(
            user="dioxtfeq_haods",
            password="Syhaobn123@",
            host="localhost",
            database="dioxtfeq_db"
        )

        cursor = connection.cursor()

        # Insert data into the database
        sql_query = """INSERT INTO coin_price_1h (pair_symbol, price, total_txHash_1hour, trading_volume_1hour, tvl)
                        VALUES (%s, %s, %s, %s, %s)"""
        cursor.execute(sql_query, (pair_symbol, data['price'], data['total_txHash_1hour'], data['trading_volume_1hour'], data['tvl']))
        connection.commit()
        print(f"Data for {pair_symbol} inserted successfully.")
    except (Exception, psycopg2.Error) as error:
        print(f"Error while connecting to PostgreSQL: {error}")
    finally:
        # Close database connection
        if connection:
            cursor.close()
            connection.close()

# List of trading pairs
trading_pairs = ['btcusdt', 'ethusdt', 'solusdt', 'avaxusdt', 'etcusdt', 'dotusdt']

# Loop through trading pairs, retrieve data from the Wormhole API, and insert into the database
for pair_symbol in trading_pairs:
    data = get_data_from_wormhole(pair_symbol)
    if data:
        insert_data_into_database(pair_symbol, data)
