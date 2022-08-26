import os
import psycopg2
from datetime import datetime, date, timezone, timedelta
import time
from linebot.models.responses import Content


def set_record():
    DATABASE_URL = os.environ["DATABASE_URL"]

    conn = psycopg2.connect(DATABASE_URL, sslmode='require')
    cursor = conn.cursor()

    cursor.close()
    conn.close()

    return seconds
