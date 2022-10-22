import re
import os
from flask import Flask, request, abort
from linebot import (LineBotApi, WebhookHandler)
from linebot.exceptions import (InvalidSignatureError)
from linebot.models import *
import time

app = Flask(__name__)

# 必須放上自己的Channel Access Token
line_bot_api = LineBotApi('OL6gBrOu9QhnSsr0uQSDnxaeDlSNkx7XIIC2wdNrZyXpMkntulEjb57BOnFcWUZMddRu5T4YvdHu6j349dA/FlukLZD9c0Sem16hahns8Wl5irTlPEgOH9yuEMNGGeZUY32CCyGBr+IKjnYAukzYXgdB04t89/1O/w1cDnyilFU=')
# 必須放上自己的Channel Secret
handler = WebhookHandler('8b6970c622e7a38ee4621f1e64f6a356')


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

#訊息傳遞區塊
##### 基本上程式編輯都在這個function #####
@handler.add(MessageEvent, message=TextMessage)
def handle_message(event):
    msg = event.message.text
    if re.match('到站提醒',msg):
        time.sleep(300)
        line_bot_api.reply_message(event.reply_token,TextSendMessage('即將到站，請準備下車～'))
    else:
        line_bot_api.reply_message(event.reply_token, TextSendMessage(msg))

#主程式
import os
if __name__ == "__main__":
    port = int(os.environ.get('PORT', 5000))
    app.run(host='0.0.0.0', port=port)