# TheAlchemist_06_04_2024
 # Coin Price Crawler

This Python program is designed to crawl data for specific trading pairs from the Sui blockchain system according to a 1-hour trading frame. It fetches data such as pair symbol, price, total transactions in the last hour, trading volume in the last hour, and total value locked (TVL) from the Sui blockchain system's API. The fetched data is then stored in a PostgreSQL database table named `coin_price_1h`.

## Prerequisites

Before running this program, ensure you have the following installed:

- Python 3.x
- `requests` library: You can install it using `pip install requests`
- PostgreSQL: Ensure you have PostgreSQL installed and running on your localhost. You can download it from https://www.postgresql.org/download/

## Usage

1. Clone the repository or download the Python script (`coin_price_crawler.py`) to your local machine.
2. Install the required Python libraries using `pip install -r requirements.txt`.
3. Modify the database connection information in the script to match your PostgreSQL database credentials.
4. Run the Python script using `python coin_price_crawler.py`.
5. The program will fetch data for the specified trading pairs from the Sui blockchain system's API and store it in the PostgreSQL database table `coin_price_1h`.

## Customization

- You can customize the list of trading pairs (`trading_pairs`) in the script according to your requirements.
- Ensure that the Sui blockchain system provides an API endpoint for fetching trading pair data. If not, you may need to explore alternative methods for obtaining the data.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
