<?php
// https://www.fizord.ru/info/718

@set_time_limit(3600);
ini_set('memory_limit', '1024M');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
     

function get_string_between($album, $file, $save_dir, $string, $start, $end, $i = 0){

     if(strpos($string, $start) !== false){

          $url1 = explode($start, $string);
          $url2 = explode($end, $url1[1]);

          $url = trim($url2[0]);
          
          $string = str_replace($start.$url.$end, '', $string);
          
          if($url != ''){
               
               //file_put_contents($file.'.txt', $data_text."\n", FILE_APPEND);
               $fileTest = $save_dir.$album."_$i.jpg";
               
               if(!file_exists($fileTest)){
                    
                    //1 метод
                    $ch = curl_init($url);
                    $fp = fopen($fileTest, 'wb');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_exec($ch);
                    curl_close($ch);
                    fclose($fp);

                    //2 метод
                    //ест больше памяти
                    //file_put_contents($fileTest, file_get_contents($url));

                    sleep(1);
               }

               if(file_exists($fileTest)){
                    $filesize = filesize($fileTest);
                    if($filesize == 0){
                         
                         @unlink($fileTest);

                         sleep(1);
                         
                         //1 метод
                         $ch = curl_init($url);
                         $fp = fopen($fileTest, 'wb');
                         curl_setopt($ch, CURLOPT_FILE, $fp);
                         curl_setopt($ch, CURLOPT_HEADER, 0);
                         curl_exec($ch);
                         curl_close($ch);
                         fclose($fp);

                         //2 метод
                         //ест больше памяти
                         //file_put_contents($fileTest, file_get_contents($url));
                    }
               }

               if(file_exists($fileTest)){
                    $filesize = filesize($fileTest);
                    if($filesize == 0){
                         @unlink($fileTest);
                    }
               }

               $i++;
               return get_string_between($album, $file, $save_dir, $string, $start, $end, $i);
          }
     }
}

//создаём папки, даём разрешение на запись
// там где хранятся файлы .html альбомов
$dir_file = '/dir/file/';
// куда будем сохранять
$dir_image = '/dir/image/';

$scan_f = scandir($dir_file);


$start = '><img src="';
$end = '" alt="';

foreach ($scan_f as $file){
     if(strpos($file, '.html') !== false){

          //echo $file.'<br />';
          
          $dir_save_file = $dir_image.$file.'/';
          
          if(!(file_exists($dir_save_file))){ mkdir($dir_save_file, 0777, true); sleep(1); }

          $string = trim(file_get_contents($dir_file.$file));
          $string = preg_replace('/\s+/', ' ',$string);

          get_string_between($file, $dir_file.$file, $dir_save_file, $string, $start, $end);
     }
}


?>
