# StockPro

# Pré-requisitos
Servidor com as seguintes dependências:

* MySQL/MariaDB >= 5.5
* Apache >= 2.4
* PHP >= 7.2
* Composer >= 1.9
* GIT >= 1.8

# Instalação
Clone o repositório do sistema na pasta desejada
```
cd /var/www/html
git clone https://github.com/RodrigoTolomeotti/StockproPro.git
cd StockPro
composer install
```

##  Configurar StockPro
Configure o arquivo .env
```
cp .env.example .env
vi .env
```

##  Instalar banco de dados
Rode as migrations e os seeders do banco de dados
```
php artisan migrate
php artisan db:seed
```

## Configurações de permisão e SELinux para o StockPro
Caso a instalação for em linux é necessário configurar as seguintes permissões
```
chown apache:apache -R /var/www/html/StockPro
chcon -t httpd_sys_content_t /var/www/html/StockPro -R
chcon -t httpd_sys_rw_content_t /var/www/html/StockPro/storage -R
chcon -t httpd_sys_rw_content_t /var/www/html/StockPro/public/template_images/ -R
chcon -t httpd_sys_rw_content_t /var/www/html/StockPro/public/users/ -R
setsebool -P httpd_can_network_connect_db 1
setsebool -P httpd_can_sendmail 1
```

# Configurações do apache
No arquivo httpd.conf defina a pasta public como root e adicione permissões
de rota como o exemplo abaixo
```
<VirtualHost *:80>
    DocumentRoot "/var/www/html/StockproPro/public"
    ServerName StockPro.com.br
    RewriteEngine on

    # Redireciona todas as requisições para utilizar SSL com HTTPS
    RewriteCond %{SERVER_NAME} =StockPro.com.br
    RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>

<Directory "/var/www/html/StockPro/public">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

# GUIA DO USUÁRIO
Acesse o link para visualizar o manual do usuário e um vídeo demo do sistema!
```
https://1drv.ms/f/s!Alcr87NDrJN5gb9gph2ULkYF6VMi7A?e=s9Y9DW
```
