# Guide de Déploiement

## Prérequis Serveur

- PHP 8.2+
- Node.js 18+
- Composer
- Nginx/Apache
- MySQL/PostgreSQL
- Redis (recommandé pour les files d'attente)

## Installation Production

1. **Préparation du Serveur**
```bash
# Installation des dépendances système
apt-get update && apt-get install -y \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-gd \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-xml \
    nginx \
    mysql-server \
    redis-server
```

2. **Configuration du Projet**
```bash
# Clone et installation
git clone <repository> /var/www/bill_meter_pwa
cd /var/www/bill_meter_pwa
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

3. **Configuration Environnement**
```bash
cp .env.example .env
php artisan key:generate
```

Configurer le fichier `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bill_meter
DB_USERNAME=user
DB_PASSWORD=password

CLOUDINARY_URL=cloudinary://key:secret@cloud_name
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

4. **Configuration Base de Données**
```bash
mysql -u root -p
CREATE DATABASE bill_meter;
CREATE USER 'bill_meter'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON bill_meter.* TO 'bill_meter'@'localhost';
FLUSH PRIVILEGES;
```

5. **Migration et Données**
```bash
php artisan migrate --force
php artisan db:seed --force
```

6. **Configuration Nginx**
```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    root /var/www/bill_meter_pwa/public;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

7. **Configuration SSL (Let's Encrypt)**
```bash
apt-get install certbot python3-certbot-nginx
certbot --nginx -d votre-domaine.com
```

8. **Configuration Supervisor pour les Files d'Attente**
```ini
[program:bill-meter-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/bill_meter_pwa/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/bill_meter_pwa/storage/logs/worker.log
stopwaitsecs=3600
```

9. **Tâches Planifiées**
Ajouter au crontab:
```bash
* * * * * cd /var/www/bill_meter_pwa && php artisan schedule:run >> /dev/null 2>&1
```

10. **Vérification Post-Déploiement**
```bash
# Vérifier l'état de l'application
php artisan about

# Tester les files d'attente
php artisan queue:monitor

# Vérifier les permissions
php artisan cache:clear
php artisan config:clear
php artisan route:cache
php artisan view:cache
```

## Surveillance

1. **Mise en Place de la Surveillance**
```bash
# Installation de outils de surveillance
composer require facade/ignition
```

2. **Configuration des Alertes**
- Configurer les notifications Slack/Email pour les erreurs
- Mettre en place la surveillance des files d'attente
- Configurer la surveillance des performances

## Maintenance

1. **Mises à Jour**
```bash
# Mettre l'application en maintenance
php artisan down

# Mettre à jour
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize
php artisan queue:restart

# Remettre l'application en ligne
php artisan up
```

2. **Sauvegardes**
```bash
# Configuration des sauvegardes quotidiennes
0 2 * * * mysqldump -u user -p bill_meter | gzip > /backup/bill_meter_$(date +\%Y\%m\%d).sql.gz
```

## Sécurité

1. **Configuration du Pare-feu**
```bash
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

2. **Surveillance de la Sécurité**
- Installation de fail2ban
- Configuration des alertes de sécurité
- Mise en place des audits de sécurité

## Environnements

- Production: https://votre-domaine.com
- Staging: https://staging.votre-domaine.com
- Développement: Local

## Support

En cas de problème:
1. Consulter les logs: `/var/www/bill_meter_pwa/storage/logs/`
2. Vérifier le statut des services: `systemctl status nginx php8.2-fpm redis`
3. Contacter l'équipe support