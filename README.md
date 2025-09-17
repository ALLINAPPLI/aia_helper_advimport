# aia_helper_advimport
Extension permettant d'ajouter un process d'import pour l'extension de la communauté civicrm ADVimport. Cette extension permet de faire un import spécifique selon une matrice csv pour les adhésions / contributions

This is an [extension for CiviCRM](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/), licensed under [AGPL-3.0](LICENSE.txt).

## Documentation

À l'activation de cette extension, celle-ci ajoute dans le paramétrage de l'import une nouvelle option dans le select : _Ajout de contribution sur des adhésions (existantes ou pas)_

Pour le bon fonctionnement de cette extension, il est nécessaire de respecter le mapping présent dans le code pour les labels dans le fichier csv. Le mapping se trouve dans la classe `AddContributionToMembership.php` méthode `getMapping(&$form)`

Le label du tableau doit correspondre parfaitement au label des colonnes du fichier csv. En faisant ça, le mapping se fait de suite sur l'écran de paramétrage de ADVimport.

### Fonctionnement

Cette extension utilise l'[order API](https://docs.civicrm.org/dev/en/latest/financial/orderAPI/) de civicrm. Cette api permet de créer une contribution et une adhésion.

On utilise trois dates lors du traitement : 

- `trxn_date` pour la date de transaction pour le paiement de la contribution
- `receive_date` pour la date de reçu de paiement pour la contribution
- `end_date` pour le calcul de la date de fin de l'adhésion

La date `trxn_date` est utilisée pour la date de paiement dans l'entité `Payment` et aussi pour le `join_date` dans l'entité `Membership`
si celle ci est vide alors on utilise la date du jour du traitement d'import pour renseigner ces deux valeurs.

On retrouve la même logique pour la `receive_date` pour la contribution. Si celle ci est vide on renseigne la date du jour dans l'entité.

### Formatage de date

Une fonction est mise à disposition pour le formatage de date de la matrice csv : `transformDateFormatCivicrm`. Cette fonction formate la date au format optimisé pour une insertion en base de données de civicrm. Ce format est `Y-m-d`

Une vérification est faîtes sur ce format présent dans la matrice csv. On contrôle si le format est français `d/m/Y` ou anglais `Y-m-d`. Dans tous les cas on formatte toujours la date au format anglais `Y-m-d`

## Requis

Extension [ADVimport](https://lab.civicrm.org/extensions/advimport)

