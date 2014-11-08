# SARB

A Twitter Search And Retweet Bot.

Becarefull before use: you must "nettoyer tes tuyaux" with this : http://www.youtube.com/watch?v=wdpXyI3_Qpk

[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/Cyrille37/SARB?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## Description

Le "bot" utilise un compte tweeter pour retweeter les tweets trouvés à partir d'une requête de recherche.
Il lui faut donc:
- un compte tweeter pour retweeter
- ce compte doit autauriser l'application SARB
- et aussi, de préférence, un ordinateur allumé en permanence et connecté à Internet ;-)

## Mode d'emploi

Pour comprendre le mode d'emploi il est préférable de connaitre le [protocole oauth2](https://dev.twitter.com/oauth/application-only) utilisé par Tweeter.

Par exemple à mettre en "cron" une ligne du genre:
```
*/15 * * * * /path/SARB.php -c /path/secrets.txt -s '#truc OR #bidule' -l fr
```

On peut simuler le fonctionnement, pour voir si des tweets sont trouvés ou bien simplement que ça fonctionne, avec l'option "-z", c'est à dire que les tweets ne seront pas retweetés.
```
./SARB.php -z -c secrets.txt -s '#truc OR #bidule' -l fr
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
il est possible de limiter la recherche à une langue (option -l)
```
./TwitterTools.php -c secrets.txt -a search -q '#enjoy' -l fr
```
