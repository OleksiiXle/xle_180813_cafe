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
