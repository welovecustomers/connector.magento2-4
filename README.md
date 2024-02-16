# Module WeLoveCustomers pour Magento2

### Pré-requis
- [Le module WeLoveCustomers](version existante ou dernière version stable disponible sur https://www.welovecustomers.fr/ressources-we-love-customers/#plugins)
- Accès SSH

### Installation
Dézipper le module WeLoveCustomers dans le répertoire **./app/code**. Dans ce dossier, il doit y avoir les répertoire "Magento" et "WeLoveCustomers".
Ouvrez un terminal et executez les commandes suivantes depuis le répertoire d'installation de votre site **./** :
```
    php bin/magento module:enable WeLoveCustomers_Connector
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
```


### Renseigner les clés api
Connectez-vous à la plateforme [WeLoveCustomers](https://app.welovecustomers.fr/).
Allez dans le menu **Profil/[Votre prénom]**.
Dans l'onglet API, vous trouvez les clés nécessaires au bon fonctionnement du module.
Pour cela, connectez vous l'administration de votre site Magento2 puis allez dans le menu **Store/Configuration**. Renseignez les champs "Api Key" et "Api Glue"
