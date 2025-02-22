# Bill Meter PWA

Application de gestion de relevés de compteurs avec OCR et capacités hors ligne.

## Fonctionnalités

- 📱 Progressive Web App (PWA)
- 📷 Capture de relevés avec photo
- 🔍 OCR automatique des compteurs
- 📊 Apprentissage et amélioration OCR
- 💼 Gestion des clients
- 📄 Génération de factures
- 📨 Système de relances
- 🔄 Mode hors ligne

## Configuration Technique

### Prérequis

- PHP 8.2+
- Node.js 18+
- Composer
- SQLite / MySQL
- Cloudinary account

### Installation

1. Cloner le projet
```bash
git clone <repository>
cd bill_meter_pwa
```

2. Installer les dépendances
```bash
composer install
npm install
```

3. Configuration
```bash
cp .env.example .env
php artisan key:generate
```

4. Configurer les variables d'environnement
```env
CLOUDINARY_URL=cloudinary://...
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

5. Migrer la base de données
```bash
php artisan migrate --seed
```

6. Compiler les assets
```bash
npm run build
```

7. Démarrer le serveur
```bash
php artisan serve
```

## Comptes de Test

### Administrateur
- Email: admin@example.com
- Password: password

### Utilisateur Standard
- Email: user@example.com
- Password: password

## Fonctionnalités OCR

### Capture et Apprentissage

1. Capture de Photos
- Utilisation de l'appareil photo
- Support du mode hors ligne
- Stockage temporaire dans IndexedDB

2. Traitement OCR
- OCR automatique des photos
- Suggestions multiples
- Stockage des données d'entraînement

3. Interface Admin OCR
- Tableau de bord d'apprentissage
- Révision des résultats
- Statistiques de performance

### Commandes Utiles

Rapport de Performance OCR:
```bash
# Rapport des 7 derniers jours
php artisan ocr:report

# Rapport personnalisé
php artisan ocr:report --days=30 --email=admin@example.com
```

## Maintenance

### Tâches Planifiées
```bash
# Ajouter au crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Tâches configurées:
- Rapport OCR quotidien (8h00)
- Envoi des relances (9h00)

### Mise à Jour

1. Mettre à jour les dépendances
```bash
composer update
npm update
```

2. Migrer la base de données
```bash
php artisan migrate
```

3. Recompiler les assets
```bash
npm run build
```

## Guide de Développement

### Structure du Projet

```
app/
├── Console/Commands/          # Commandes Artisan
├── Http/
│   ├── Controllers/          # Contrôleurs
│   └── Middleware/           # Middleware
├── Models/                   # Modèles Eloquent
└── Notifications/           # Notifications

resources/
├── js/
│   ├── Components/          # Composants React
│   ├── Pages/              # Pages Inertia
│   └── lib/                # Bibliothèques JS
└── views/
    └── emails/             # Templates d'emails
```

### Technologies Principales

- Laravel 11
- React + TypeScript
- Inertia.js
- TailwindCSS
- Tesseract.js (OCR)
- Dexie.js (IndexedDB)
- Service Workers

## Sécurité

- Protection CSRF
- Validation des données
- Authentification multi-niveaux
- Middleware OCR Admin
- Gestion sécurisée des fichiers

## Support

Pour toute question ou problème:
1. Consulter la documentation
2. Ouvrir une issue
3. Contacter l'équipe support

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.
