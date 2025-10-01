# Meteo (Symfony 7.2)

Application météo avec Symfony 7.2 qui permet :
- Recherche météo par nom de ville.
- Géolocalisation navigateur (fallback sur Nancy).
- Affichage du jour et des 7 prochains jours avec icônes.

Elle s’appuie sur :
- OpenStreetMap Nominatim (reverse geocoding) pour déduire la ville depuis des coordonnées GPS.
- RapidAPI (Meteosource) pour récupérer les prévisions.
- Cache Symfony pour limiter les appels API.

---

## Prérequis
- PHP >= 8.2
- Composer
- Serveur web pointant vers le dossier `public/` (Symfony CLI, WAMP/XAMPP, Nginx/Apache)
- Base de données (MySQL/MariaDB ou SQLite pour tests)

---

## Installation
1. Cloner le projet et se placer dans le répertoire :
   ```bash
   git clone <votre-repo> Meteo
   cd Meteo
   ```
2. Installer les dépendances :
   ```bash
   composer install
   ```
3. Configurer les variables d’environnement (voir sections ci-dessous). Au minimum :
   - `APP_SECRET`
   - `DATABASE_URL`
   - `RAPIDAPI_KEY`
   - `USERAGENT`

---

## Configuration des variables d’environnement
Les variables d’environnement se déclarent dans `.env` (par défaut), `.env.local` (non commité) ou via l’environnement système. Ne mettez jamais de secrets de production dans un fichier versionné.

Variables nécessaires :
- `APP_ENV` (ex : `dev`, `prod`)
- `APP_SECRET` (chaîne aléatoire)
- `DATABASE_URL` (connexion Doctrine)
- `RAPIDAPI_KEY` (clé RapidAPI pour Meteosource)
- `USERAGENT` (obligatoire pour Nominatim, ex : `YourApp/1.0 (mail@example.com)`)

Exemple prêt à l’emploi :
```dotenv
APP_ENV=dev
APP_SECRET=ChangeMe_1234567890abcdef

# MySQL/MariaDB (WAMP)
DATABASE_URL="mysql://user:password@127.0.0.1:3306/Meteo?serverVersion=10.11.2-MariaDB&charset=utf8mb4"

# RapidAPI (Meteosource)
RAPIDAPI_KEY=your-rapidapi-key

# Nominatim (obligatoire)
USERAGENT="YourAppName/1.0 (contact@example.com)"
```

> Note : Vous pouvez utiliser SQLite pour un démarrage rapide en dev/test :
> ```dotenv
> DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_%kernel.environment%.db"
> ```

---

## Lancement du serveur
- Avec Symfony CLI :
  ```bash
  symfony serve -d
  ```
  Application sur http://127.0.0.1:8000/

- Avec WAMP/XAMPP :
  - Pointez le DocumentRoot vers le dossier `public/`.
  - Accédez via http://localhost/ (ou http://localhost/Meteo/ selon votre config).

---

## Utilisation
- Page principale : `GET /`
  - Par ville : `/?ville=Nancy`
  - Par coordonnées : `/?lat=48.6921&lon=6.1844`

UI :
- Champ de ville + bouton Rechercher.
- Icône « 🎯 » pour géolocalisation navigateur.
- Fallback automatique vers `Nancy` en cas d’échec ou d’absence de paramètres.

---

## APIs utilisées
- Nominatim (OpenStreetMap)
  - Reverse : `https://nominatim.openstreetmap.org/reverse?lat={lat}&lon={lon}&format=json&accept-language=fr`
  - Header requis : `User-Agent: <USERAGENT>`
  - Politique : https://operations.osmfoundation.org/policies/nominatim/

- Meteosource via RapidAPI
  - Rechercher un lieu : `https://ai-weather-by-meteosource.p.rapidapi.com/find_places?text={city}&language=fr`
  - Données quotidiennes :
    - par `place_id` : `.../daily?place_id={id}&language=fr&units=metric`
    - par coordonnées : `.../daily?lat={lat}&lon={lon}&language=fr&units=metric`
  - Headers :
    - `x-rapidapi-host: ai-weather-by-meteosource.p.rapidapi.com`
    - `x-rapidapi-key: <RAPIDAPI_KEY>`

---

## Architecture du projet
- Contrôleur
  - `src/Controller/MeteoController.php` (route `/`)
- Services
  - `src/Service/InputValidationService.php` : validation ville/coordonnées.
  - `src/Service/GeocodingService.php` : Nominatim reverse (header `User-Agent`).
  - `src/Service/WeatherApiService.php` : appels RapidAPI + cache 12h.
  - `src/Service/WeatherRetrievalService.php`, `src/Service/WeatherFormattingService.php` : orchestration/formatage (référencés par le contrôleur).
- Données (si persistance)
  - `src/Entity/WeatherData.php`, `src/Repository/WeatherDataRepository.php`.
- Templates
  - `templates/meteo/index.html.twig`, `templates/base.html.twig`.
- Frontend
  - `public/js/geolocalisation.js` (géoloc auto + clic)
  - `public/js/meteo.js` (UI : messages, couleurs de températures)
  - `public/assets/icons/...` (icônes météo)

---

## Base de données
Si vous utilisez MySQL/MariaDB :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```
> Les migrations existantes : `migrations/*.php`.

Pour les tests ou le dev rapide, SQLite est possible (voir exemple `DATABASE_URL`).

---

## Cache
`src/Service/WeatherApiService.php` utilise `CacheInterface` (TTL : 12h) :
- Par ville : `weather_data_{ville}`
- Par coordonnées : `weather_coords_{lat}_{lon}`

Le cache réduit la charge et l’usage des quotas API.

---

## Déploiement (prod)
- Compiler les envs :
  ```bash
  composer dump-env prod
  ```
- S’assurer que le serveur pointe sur `public/`.
- Définir un `APP_SECRET` fort et des variables d’environnement réelles.

---

## Dépannage
- 403/429 avec Nominatim : vérifiez `USERAGENT` (doit être personnalisé et inclure un contact).
- 401/403 RapidAPI : vérifiez `RAPIDAPI_KEY` et les quotas.
- Icônes manquantes : assurez-vous que `public/assets/icons/...` existe et que le chemin dans les templates correspond.
- Redirections intempestives : vérifiez les paramètres `lat/lon` et la géolocalisation navigateur (`public/js/geolocalisation.js`).

---

## Licence
Moi
