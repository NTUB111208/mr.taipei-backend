import os
import psycopg2
from datetime import datetime, date, timezone, timedelta
import time
from linebot.models.responses import Content


def set_record():
    DATABASE_URL = os.environ["DATABASE_URL"]

    conn = psycopg2.connect(DATABASE_URL, sslmode='require')
    cursor = conn.cursor()

    # 轉換時區 -> 東八區
    dt1 = datetime.utcnow().replace(tzinfo=timezone.utc)
    dt2 = dt1.astimezone(timezone(timedelta(hours=8)))

    # strftime 將時間格式化/格式化一段時間字串
    timenow = dt2.strftime("%Y-%m-%d %H:%M:%S")  # 將目前時間轉換為 string
    postgres_selectone_query = f"""SELECT time FROM usertime WHERE userid = '' """

    cursor.execute(postgres_selectone_query)
    rows = cursor.fetchone()

    # strptime 按照特定時間格式將字串轉換為時間類型。
    time_1_struct = datetime.strptime(timenow, "%Y-%m-%d %H:%M:%S")  # 現在時間
    time_2_struct = datetime.strptime(
        str(rows[0]), '%Y-%m-%d %H:%M:%S')  # 預計抵達時間
    print(type(time_1_struct))
    print(type(time_2_struct))

    seconds = (time_2_struct - time_1_struct).seconds
    print('現在時間:', time_1_struct)
    print('預計抵達時間:', time_2_struct)
    print('相差', seconds, '秒')


    cursor.close()
    conn.close()

    return seconds
