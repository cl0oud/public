https://zee3.fizord.ru/2024/09/26/_overall_2024_cloud/20240926-175226_TfxvAhcD6-R.zip

CRUD: insert update delete get


sql: id name surname email number country


обязательные значения:
	name surname number


пример:

	добавление
	curl -s --header 'Content-Type: application/json' --request 'POST' --data "{\"method\":\"insert\",\"name\":\"name1\",\"surname\":\"surname1\",\"email\":\"email1\",\"number\":\"+79871111112\",\"country\":\"ru\"}" "http://localhost:9900/app.php"

	получение
	curl -s --header 'Content-Type: application/json' --request 'POST' --data "{\"method\":\"get\",\"number\":\"+79871111112\"}" "http://localhost:9900/app.php"

	удаление
	curl -s --header 'Content-Type: application/json' --request 'POST' --data "{\"method\":\"delete\",\"number\":\"+79871111112\"}" "http://localhost:9900/app.php"

	изменение
	curl -s --header 'Content-Type: application/json' --request 'POST' --data "{\"method\":\"update\",\"name\":\"name1\",\"surname\":\"surname1\",\"email\":\"test@mail.ru\",\"number\":\"+79871111112\"}" "http://localhost:9900/app.php"
	
