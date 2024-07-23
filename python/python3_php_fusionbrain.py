#https://www.fizord.ru/info/720
#бесплатная генерация изображений через ИИ fusionbrai/Kandinsky
#fusionbrai.py

import json
import time

import requests

import sys
import base64
import os

class Text2ImageAPI:

    def __init__(self, url, api_key, secret_key):
        self.URL = url
        self.AUTH_HEADERS = {
            'X-Key': f'Key {api_key}',
            'X-Secret': f'Secret {secret_key}',
        }

    def get_model(self):
        response = requests.get(self.URL + 'key/api/v1/models', headers=self.AUTH_HEADERS)
        data = response.json()
        return data[0]['id']

    def generate(self, prompt, model, images=1, width=1024, height=1024):
        params = {
            "type": "GENERATE",
            "numImages": images,
            "width": width,
            "height": height,
            "generateParams": {
                "query": f"{prompt}"
            }
        }

        data = {
            'model_id': (None, model),
            'params': (None, json.dumps(params), 'application/json')
        }
        response = requests.post(self.URL + 'key/api/v1/text2image/run', headers=self.AUTH_HEADERS, files=data)
        data = response.json()
        return data['uuid']

    def check_generation(self, request_id, attempts=10, delay=10):
        while attempts > 0:
            response = requests.get(self.URL + 'key/api/v1/text2image/status/' + request_id, headers=self.AUTH_HEADERS)
            data = response.json()
            if data['status'] == 'DONE':
                return data['images']

            attempts -= 1
            time.sleep(delay)

try:
    if sys.argv[1] is None:
        print('error2')
    else:
        if __name__ == '__main__':
            
            if sys.argv[2] is None:
                text_return = 'Sun in sky';
            else:
                text_return = sys.argv[2]
        
            api = Text2ImageAPI('https://api-key.fusionbrain.ai/', 'YOUR_KEY', 'YOUR_SECRET')
            model_id = api.get_model()
            uuid = api.generate(text_return, model_id)
            images = api.check_generation(uuid)
            image_base64 = images[0]
            image_data = base64.b64decode(image_base64)
            
            with open(sys.argv[1], "wb") as file:
                file.write(image_data)
            
            if not os.path.exists(sys.argv[1]):
                print('error3')
            else:
                print('true')
except:
    print('error1')

#Не забудьте указать именно ваш YOUR_KEY и YOUR_SECRET.


#php:

#$file_gpt_name = 'gpt_'.time().'.png';
#$python =  exec("python3 /var/www/fusionbrai.py /var/www/image/$file_gpt_name 'нарисуй дом у озера'");
#echo $python;
#//если выводит true, то делаем ссылку и отдаём
