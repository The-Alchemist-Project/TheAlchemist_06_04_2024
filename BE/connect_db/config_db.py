import MySQLdb as mysql


config = {
  "user": "dioxtfeq_db",
  "password": "dioxtfeq_db@@123",
  "host": "localhost",
  "database": "dioxtfeq_db"
}


def config_db():
    return mysql.connect(**config)
