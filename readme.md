# API Le Progrès

API retravaillée du journal en ligne [Le Progrès](https://www.leprogres.fr/).

L'API a été découverte grâce au reverse engineering de l'application
[Android](https://play.google.com/store/apps/details?id=com.leprogres_prod.presse&hl=fr)
grâce aux outils **[APKTool](https://ibotpeaches.github.io/Apktool/)** et 
**[dex2jar](https://github.com/pxb1988/dex2jar)**. 
J'ai retravaillé les retours de l'API officielle qui sont un peu indigestes.

## Utilisation

``` shell
git clone https://github.com/babeuloula/api-leprogres
composer install
php -S localhost:8000
```

Ou avec Docker :

``` shell
git clone https://github.com/babeuloula/api-leprogres
make install
```

## Routes

Liste des routes disponibles dans l'API.

\* paramètre optionnel

### GET /lastContents?page=&perPage=contentType=

- **page*** : pagination des articles (défaut 1)
- **perPage*** : nombre d'articles par pages (defaut 20, max 50) 
- **contentType*** : type de contenu à afficher
    - **All** : Tous les contenus
    - **Gallery** : Galerie d'images
    - **Video** : Contenu vidéo
    - **RichContent**  : Contenu mixte
    - **Audio** : Contenu audio
    - **Live** : Contenu en direct (non implémenté dans l'API)   
    

### GET /oneContent?cmsUrl=

Dans les retours de la route `GET /lastContents`, il y a une valeur nommée `cmsUrl`,
il faut utiliser cette valeur comme paramètre à la route suivante.

Ne me demandez pas pourquoi ils ne se sont pas servi de la valeur `key`. 

- **cmsUrl** : URL de l'article
