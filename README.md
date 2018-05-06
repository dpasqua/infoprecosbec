# becprecos

### configs 
 
 - gerar arquivo .env a partir de .env.example
 - configurar BD e GOOGLE API em .env

### banco de dados

 - php artisan migrate

### processar carga inicial 
 
 - php bin/console.php processar:municipios
 - php bin/console.php processar:uges
 - php bin console.php processar:coordenadas

### processar crawler api

  - php bin/console.php api:ocs ddmmyyyy ddmmyyyy
  - php bin/console.php api:ocdetalhes

### processar produtos

 - php bin console.php processar:produtos