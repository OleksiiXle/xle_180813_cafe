1. Настроить виртуальный хостинг по образцу
<VirtualHost *:80>
    ServerName cafe
    ServerAlias www.cafe

    DocumentRoot /var/www/xle/items/cafe/web
    <Directory /var/www/xle/items/cafe/web>
        AllowOverride All        
    </Directory>

    ErrorLog /var/log/apache2/project_error.log
    CustomLog /var/log/apache2/project_access.log combined
</VirtualHost>

2. Склонировать данный репозиторий

3. В зависимости от ОС и конфигурации решить проблему предоставления web-процессу возможности записывать данные в папку проекта .../var/
4. Composer update
5. Проверить настройки БД в файле app/config/parameters.yml
По умолчанию там - env(DATABASE_URL): 'mysql://root:111@127.0.0.1:3306/symfony_cafe'
Усли все устраивает:
6.php bin/console doctrine:database:create
  php bin/console doctrine:schema:create
  php bin/console doctrine:fixtures:load
  
  Будет создана БД, таблица cafe с несколькими тестовыми записями и таблица xle_user с двумя записями: администратор
  admin, пароль admin, и user, пароль admin
  
  ![Иллюстрация к проекту](https://github.com/OleksiiXle/xle_180813_cafe.git/web/build/images/map1.png)

