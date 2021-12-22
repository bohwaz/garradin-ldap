# Garradin LDAP server

Ce petit exemple démontre la mise en place d'un serveur LDAP basé sur Garradin (1.1.x).

C'est basé sur la librairie [FreeDSx LDAP](https://github.com/FreeDSx/LDAP).

Fun fact : c'est plus rapide/simple de coder un serveur LDAP avec cette librairie que de configurer OpenLDAP ;-)

Ceci permet d'utiliser Garradin pour authentifier des utilisateurs depuis d'autres applications.

Installation : 

```
composer install
```

Démarrer le serveur :

```
php index.php PORT_LDAP
```

Le port LDAP par défaut est 389, mais n'est accessible qu'en root.

Pour tester une fois le serveur démarré, utiliser le script `test.sh`.

Il est à noter que ceci est juste un exemple et qu'il faut probablement ajouter un peu de fonctionnalités pour récupérer les infos des membres dans d'autres applis, actuellement seul le nom est renvoyé.

N'hésitez pas à forker et améliorer.

## Installation comme serveur système avec systemd

Modifiez `config.php` pour indiquer le chemin d'accès à votre base de données Garradin (association.sqlite) puis en root :

```
composer install
mkdir -p /usr/local/share/garradin-ldap
cp -r * /usr/local/share/garradin-ldap
cp garradin-ldap.service /etc/systemd/system/
systemctl start garradin-ldap
```

Le serveur sera disponible sur le port 389.