# -*- coding: utf-8 -*-

# 載入LineBot所需要的套件
import re
import os

from database import *
from linebot.models import *
from linebot.models import TextSendMessage
from linebot import LineBotApi, WebhookHandler
from linebot.exceptions import InvalidSignatureError
from flask import Flask, request, abort, render_template
from datetime import datetime, date, timezone, timedelta
import time


app = Flask(__name__)


# 必須放上自己的Channel Access Token
Channel_Access_Token = ''
line_bot_api = LineBotApi(Channel_Access_Token)
# 必須放上自己的Channel Secret
Channel_Secret = ''
handler = WebhookHandler(Channel_Secret)

# 監聽所有來自 /callback 的 Post Request


@app.route("/callback", methods=['POST'])
def callback():
    # get X-Line-Signature header value
    signature = request.headers['X-Line-Signature']

    # get request body as text
    body = request.get_data(as_text=True)
    app.logger.info("Request body: " + body)

    # handle webhook body
    try:
        handler.handle(body, signature)
    except InvalidSignatureError:
        abort(400)

    return 'OK'

# 訊息傳遞區塊
##### 基本上程式編輯都在這個function #####


@handler.add(MessageEvent, message=TextMessage)
def handle_message(event):
    message = text = event.message.text


# 主程式
if __name__ == "__main__":
    port = int(os.environ.get('PORT', 5000))
    app.run(host='0.0.0.0', port=port)
