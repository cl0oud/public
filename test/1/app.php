<?php

/*

Тестовое задание PHP Backend Developer

Написать микросервис работы с гостями используя язык программирования на PHP,
можно пользоваться любыми opensource пакетами, также возможно реализовать с использованием фреймворков
или без них. БД также любая на выбор, использующая SQL в качестве языка запросов. 

Микросервис реализует API для CRUD операций над гостем.
То есть принимает данные для создания, изменения, получения,
удаления записей гостей хранящихся в выбранной базе данных.

Сущность "Гость" Имя, фамилия и телефон – обязательные поля. А поля телефон и email уникальны.
В итоге у гостя должны быть следующие атрибуты: идентификатор, имя, фамилия, email, телефон, страна.
Если страна не указана то доставать страну из номера телефона +7 - Россия и т.д. 

Правила валидации нужно придумать и реализовать самостоятельно. Микросервис должен запускаться в Docker. 

Результат опубликовать в Git репозитории, в него же положить README файл с описанием проекта.
Описание не регламентировано, исполнитель сам решает что нужно написать (техническое задание,
документация по коду, инструкция для запуска). Также должно быть описание API (как в него делать запросы,
какой формат запроса и ответа), можно в любом формате, в том числе в том же README файле.

Доп. обязательное условие для уровня Middle (по желанию для Junior): “В ответах сервера должны присутствовать
два заголовка X-Debug-Time и X-Debug-Memory, которые указывают сколько миллисекунд выполнялся запрос
и сколько Кб памяти потребовалось соответственно.”

*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// получение данных
$data_input = file_get_contents("php://input");

/*
//curl -s --header 'Content-Type: application/json' --request 'POST' --data "{\"method\":\"insert\",\"name\":\"name1\",\"surname\":\"surname1\",\"email\":\"email1\",\"number\":\"+79871111112\",\"country\":\"ru\"}" "http://localhost:9900/app.php"
// пример
// идентификатор, имя, фамилия, email, телефон, страна
// id name surname email number country
$data_input_array = array(
	'method'  => 'insert',        // CRUD: insert update delete get
	'name'    => 'name1',         // обязательный
	'surname' => 'surname1',      // обязательный
	'email'   => 'email1',        // уникальный
	'number'   => '+79871111112', // обязательный уникальный
	'country' => '',              // берём из номера телефона, если не указана, если указана (проверяем по номеру)
);
$data_input = json_encode($data_input_array);
*/

$error = array();
$text  = array();


// проверка входный данных
if(isset($data_input) && $data_input != '' && json_decode($data_input)){
	$tmpdata = json_decode($data_input, true);
}

// определение страны по номеру
function country_code($number, $array, $j = 2, $code_last = 'zz', $dial_code_arr = ''){
	
	$code      = $code_last;
	$dial_code = substr($number, 0, $j);
	
	$j2         = $j - 1;
	$dial_code2 = substr($number, 0, $j2);
	
	$i = 0;
	foreach($array as $key=>$val){
		if(strripos($val['dial_code'], $dial_code) !== false){
			$code          = mb_strtolower($val['code']);
			$dial_code_arr = $val['dial_code'];
			$i++;
		}
		if($i > 1){ break; }
	}
	if($i >= 1){
		$j++;
		return country_code($number, $array, $j, $code, $dial_code_arr);
	} else {
		if($dial_code_arr != $dial_code2){
			$code = array('zz');
		} else {
			$code = array();
			foreach($array as $key=>$val){
				if($val['dial_code'] == $dial_code2){ array_push($code, mb_strtolower($val['code'])); }
			}
		}
		return $code;
	}
}

function get_email($get_email){ if($get_email == ''){ return null; } else { return $get_email; } }

//echo count($error);
//exit();

if(isset($tmpdata)){

	if(isset($tmpdata['method']) && $tmpdata['method'] != '' && isset($tmpdata['number']) && $tmpdata['number'] != '' && substr($tmpdata['number'], 0, 1) == '+' && is_numeric(substr($tmpdata['number'], 1, strlen($tmpdata['number'])))){
		
		// подключение к db
		$db_host     = 'db';
		$db_name     = 'db_sdfs';
		$db_tab      = 'db_sdfs_tab';
		$db_login    = 'db_sdfs_user';
		$db_password = 'db_sdfs_pass';

		//
		try {
			$conn = new PDO("mysql:host=$db_host", $db_login, $db_password);
			$sql0 = "CREATE DATABASE IF NOT EXISTS $db_name";
			$conn->exec($sql0);
		} catch (PDOException $e) {
			exit("ошибка create database: " . $e->getMessage());
		}

		try {
			$pdo_connect = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_login, $db_password);
			$sql1 = "create table IF NOT EXISTS $db_tab (
				id integer auto_increment primary key,
				name varchar(100),
				surname varchar(100),
				email varchar(256) unique,
				number varchar(100) unique,
				country varchar(10)
			);";
			$pdo_connect->exec($sql1);
		} catch (PDOException $e) {
			exit("ошибка create table: " . $e->getMessage());
		}
		
		// переменная number
		$get_number  = $tmpdata['number'];
		
		switch($tmpdata['method']){

			case 'insert':
			case 'update':


				$stmt = $pdo_connect->prepare("SELECT * FROM $db_tab WHERE number=?");
				$stmt->execute([$tmpdata['number']]); 
				$base = $stmt->fetch(PDO::FETCH_ASSOC);
				
				// если необходимо сохранить, но уникальный номер занят, то выводим ошибку
				if($tmpdata['method'] == 'insert' && isset($base['number']) && $base['number'] != ''){
					
					array_push($error, 'данный number занят');
				}
				
				if(empty($tmpdata['name']) || $tmpdata['name'] == ''){
					
					array_push($error, 'не указано имя');
				}
				
				
				if(empty($tmpdata['surname']) || $tmpdata['surname'] == ''){
					
					array_push($error, 'не указана фамилия');
				}
				
				if(count($error) < 1){

					$get_name    = mb_strtolower($tmpdata['name']);
					$get_surname = mb_strtolower($tmpdata['surname']);

					if(isset($tmpdata['email']))  { $get_email   = mb_strtolower($tmpdata['email']);   } else { $get_email = '';     }
					if(isset($tmpdata['country'])){ $get_country = mb_strtolower($tmpdata['country']); } else { $get_country = ''; }
					
					// включение массива стран
					$CountryCodes = json_decode(file_get_contents("CountryCodes.json"), true);
					
					$array_country_code = country_code($tmpdata['number'], $CountryCodes);
					
					$get_country_none = true;
					foreach($array_country_code as $key){ if($key == $get_country){ $get_country_none = false; break; } }
					if($get_country_none){ $get_country = $array_country_code[0]; }
				
					// проверка email
					if ($get_email != '' && !preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", $get_email)) {
						$get_email = '';
						array_push($error, 'данный email не прошёл валидацию, сохранение произвели с email = NULL');
					}
					if($get_email != ''){

						$stmt1 = $pdo_connect->prepare("SELECT * FROM $db_tab WHERE email=?");
						$stmt1->execute([$get_email]); 
						$base1 = $stmt1->fetch(PDO::FETCH_ASSOC);
						
						if(isset($base1['email']) && $base1['number'] != $get_number){

							$get_email = '';
							array_push($error, 'данный email используется другим номером, сохранение произвели с email = NULL');
						}
					}
					
					switch($tmpdata['method']){
						
						//if($get_email == ''){ $get_email = null; }
						
						case 'insert':

							$sql = "INSERT INTO $db_tab (name, surname, email, number, country)VALUES (?,?,?,?,?);";	
							$stmt = $pdo_connect->prepare($sql);
							$stmt->execute(array($get_name, $get_surname, get_email($get_email), $get_number, $get_country));
							
							$text = array('result' => 'запись сохранена');

						break;
						case 'update':

							$sql = "UPDATE $db_tab SET name=?, surname=?, email=?, country=? WHERE (number=?)";
							$stmt= $pdo_connect->prepare($sql);
							$stmt->execute([$get_name, $get_surname, get_email($get_email), $get_country, $get_number]);
							
							$text = array('result' => 'запись изменена');

						break;
					}
					
				}
				
			break;
			case 'delete':
			case 'get':

				$stmt = $pdo_connect->prepare("SELECT * FROM $db_tab WHERE number=?");
				$stmt->execute([$get_number]); 
				$base = $stmt->fetch(PDO::FETCH_ASSOC);
				
				if(isset($base['number']) && $base['number'] == $get_number){

					if($tmpdata['method'] == 'delete'){

						$sql = "DELETE FROM $db_tab WHERE number='$get_number'";
						$result = $pdo_connect->query($sql);
						
						$text = array('result' => 'запись удалена');
					
					} else { $text = array('result' => $base); }

				} else { array_push($error, 'данный number отсутствует в БД'); }

			break;
			default:
				array_push($error, 'отсутвие выбора метода операции');
		}
		


	} else {

		if(empty($tmpdata['method']) || $tmpdata['method'] == ''){
			array_push($error, 'неправильный метод');
		}
		
		if(empty($tmpdata['number']) || $tmpdata['number'] == '' || !substr($tmpdata['number'], 0, 1) == '+' || !is_numeric(substr($tmpdata['number'], 1, count($tmpdata['number'])))){
			array_push($error, 'неправильный номер');
		}
	}
	
} else { array_push($error, 'отсутствие входных данных'); }


if(count($error) > 0){ array_unshift($text, $error); }

echo json_encode($text, JSON_UNESCAPED_UNICODE)."\n";

?>
