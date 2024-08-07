# https://www.fizord.ru/info/719
import time
import os
#python -m pip install requests
import requests
import sys

sys.setrecursionlimit(1000000000)

def is_url_image(url, j = 0):
    #time.sleep(1)
    try:
        #image_formats = ("image/png", "image/jpeg", "image/jpg")
        #r = requests.head(url)
        #if r.headers["content-type"] in image_formats:
        #    return True
        #return False
        r = requests.get(url)
        if r.status_code == 200:
            return True
        return False
    except:
        if j < 3:
            j += 1
            is_url_image(url, j)
        else:
            return False


def fileSave(url, fileTest, j = 0):
    print(url + ' - ' + fileTest)
    if j < 3:
        if not os.path.exists(fileTest):
            if is_url_image(url):
                response = requests.get(url)
                with open(fileTest, 'wb') as file:
                    file.write(response.content)
                #time.sleep(1)
                if not os.path.exists(fileTest):
                    j += 1
                    fileSave(url, fileTest, j)


def fileDownload(album, file, save_dir, string, start, end, i):

    if start in string:
        if end in string:
        
            url1 = string.split(start, 1)
            url2 = url1[1].split(end, 1)
            url = url2[0]
            string = string.replace(start + url + end, '')

            if url != '':
                fileTest = save_dir + album + '_' + str(i) + '.jpg'
                fileSave(url, fileTest)
                i += 1
                fileDownload(album, file, save_dir, string, start, end, i)

# создаём папки, даём разрешение на запись
# там где хранятся файлы .html альбомов
dir_file = '/dir/file/';
# куда будем сохранять
dir_image = '/dir/image/';

start = '><img src="'
end = '" alt="'

scan_f = os.listdir(dir_file)
for file in scan_f:
    dir_save_file = dir_image + file + '/'
    if not os.path.exists(dir_save_file):
        os.mkdir(dir_save_file, 0o777)
        print('create' + dir_save_file)
        #time.sleep(1)
    f = open(dir_file+file, 'r')
    string = f.read()
    f.close
    fileDownload(file, dir_file+file, dir_save_file, string, start, end, 0)
