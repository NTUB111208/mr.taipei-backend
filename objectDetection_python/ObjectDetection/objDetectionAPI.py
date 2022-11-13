# %%
from __future__ import print_function
from flask import Flask,jsonify,request
from turtle import color
import cv2
import urllib.request
import matplotlib.pyplot as plt
import numpy as np
from openvino.runtime import Core
import googletrans
from pprint import pprint
import cvzone
from cvzone.SelfiSegmentationModule import SelfiSegmentation
import binascii
import struct
from PIL import Image
import scipy
import scipy.misc
import scipy.cluster
import webcolors
from translate import Translator
NUM_CLUSTERS = 5

# Initial 
app = Flask(__name__)
app.config["DEBUG"] = True
app.config["JSON_AS_ASCII"] = False

def objectDetection(img_url):
    # imgUrl = 'https://i.imgur.com/0iS3f68.jpg'
    img_req = urllib.request.urlopen(img_url)

    ie = Core()
    segmentor = SelfiSegmentation()

    model = ie.read_model(model="model/v3-small_224_1.0_float.xml")
    compiled_model = ie.compile_model(model=model, device_name="CPU")

    output_layer = compiled_model.output(0)
    arr = np.asarray(bytearray(img_req.read()), dtype=np.uint8)
    img = cv2.imdecode(arr, -1) # Load it as it is
    input_image = cv2.resize(src=img, dsize=(224, 224))

    # The MobileNet model expects images in RGB format
    image = cv2.cvtColor(input_image, code=cv2.COLOR_BGR2RGB)

    # resize to MobileNet image shape
    img_Out = segmentor.removeBG(image, (255,255,255), threshold=0.99)

    # reshape to model input shape
    input_image = np.expand_dims(input_image, 0)

    result_infer = compiled_model([input_image])[output_layer]
    result_index = np.argmax(result_infer)
    return result_index, img_Out
    

def ConvertInferenceResult(result_index, img_Out):
    translator = googletrans.Translator()

    # Convert the inference result to a class name.
    imagenet_classes = open("utils/imagenet_2012.txt", encoding="utf-8").read().splitlines()

    # The model description states that for this model, class 0 is background,
    # so we add background at the beginning of imagenet_classes
    imagenet_classes = ['background'] + imagenet_classes

    imgResultsNum = imagenet_classes[result_index][:9]
    imgResults = '悠遊卡' if imgResultsNum == 'n04116512' else imagenet_classes[result_index][10:]
    ar = np.asarray(img_Out)
    shape = ar.shape
    ar = ar.reshape(np.product(shape[:2]), shape[2]).astype(float)

    # print('finding clusters')
    codes, dist = scipy.cluster.vq.kmeans(ar, NUM_CLUSTERS)
    # print('cluster centres:\n', codes)

    vecs, dist = scipy.cluster.vq.vq(ar, codes)         # assign codes
    counts, bins = np.histogram(vecs, len(codes))    # count occurrences
    # index_max = np.argmax(counts)                  # find most frequent
    try:
        index_max = np.argsort(counts, axis=0)[-2]
    except IndexError:
        index_max = 0
    peak = codes[index_max]
    colour = binascii.hexlify(bytearray(int(c) for c in peak)).decode('ascii')
    print('most frequent is %s (#%s)' % (peak, colour))

    #讀取 hexToColor 資料存2d arrays到list
    hexToColor_classes = open("utils/hexToColorName.txt", encoding="utf-8")
    colorDict = []
    for line in hexToColor_classes.read().splitlines():
        colorDict.append(line.split(' '))
    
    requested_colour = (peak[0], peak[1], peak[2])
    actual_name, closest_name = get_colour_name(requested_colour)

    # 找closest name 對應的 中文
    for i in colorDict:
        color_name = i[0].lower()
        if(color_name == closest_name):
            closest_name = i[1]
            break
    # print("Actual colour name:", actual_name, ", closest colour name:", closest_name) 
    results = translator.translate(imgResults, dest='zh-tw')
    print(imgResultsNum)
    print(results.text + '  ' +closest_name)
    data = {
        "item_name": results.text,
        "item_color": closest_name,
        "img_result_num": imgResultsNum,
        "most_frequent_color": colour
    }
    return jsonify(data)

def closest_colour(requested_colour):
    min_colours = {}
    for key, name in webcolors.CSS3_HEX_TO_NAMES.items():
        r_c, g_c, b_c = webcolors.hex_to_rgb(key)
        rd = (r_c - requested_colour[0]) ** 2
        gd = (g_c - requested_colour[1]) ** 2
        bd = (b_c - requested_colour[2]) ** 2
        min_colours[(rd + gd + bd)] = name
    return min_colours[min(min_colours.keys())]

def get_colour_name(requested_colour):
    try:
        closest_name = actual_name = webcolors.rgb_to_name(requested_colour)
    except ValueError:
        closest_name = closest_colour(requested_colour)
        actual_name = None
    return actual_name, closest_name

@app.route('/objectDetection', methods=['GET'])
def startPoint():
    if 'img_url' in request.args:
        img_url = request.args.get('img_url', None)
        result_index, img_Out = objectDetection(img_url)
        response = ConvertInferenceResult(result_index, img_Out)
        print(response)
        return response
    else:
        return "Error: No img url provided. Please specify another."


# app.run(debug=True, host='127.0.0.1', port=3001)

# plt.show()


# install 
# https://clay-atlas.com/blog/2020/05/05/python-cn-note-package-googletrans-google-translate/
# https://blog.csdn.net/xfyuanjun520/article/details/115465873
# https://www.delftstack.com/zh-tw/howto/python/opencv-background-subtraction/
# https://stackoverflow.com/questions/3241929/python-find-dominant-most-common-color-in-an-image