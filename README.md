# SARB


A Twitter Search And Retweet Bot.

Becarefull before use: you must "nettoyer tes tuyaux" with this : http://www.youtube.com/watch?v=wdpXyI3_Qpk

## Description

Le "bot" retweeter les tweets à partir d'une requête de recherche.
Il lui faut donc:
- un compte tweeter pour retweeter
- ce compte doit autauriser l'application SARB

## Mode d'emploi

Par exemple à mettre en "cron" une ligne du genre:
```
*/15 * * * * /path/SARB.php -c /path/secrets.txt -s '#truc OR #bidule' -l fr
```

Il y a le TwitterTools.php qui permet d'obtenir le token d'autorisation du compte:
```
./TwitterTools.php -c secrets.txt -a authApp
```

Le fichier de configuration (option -c) doit au minimum contenir les 2 valeurs suivantes pour identifier l'application:
- OAUTH_CONSUMER_KEY
- OAUTH_CONSUMER_SECRET

Après l'obtention du token d'autorisation on ajoutera les 3 valeurs:
- USERID
- OAUTH_ACCESS_TOKEN
- OAUTH_ACCESS_SECRET

Pour tester le fichier de configuration lancer une recherche avec TwitterTools.php :
```
./TwitterTools.php -c secrets.txt -a search -q '#enjoy'
```
