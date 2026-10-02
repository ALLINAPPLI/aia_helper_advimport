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

### Crédit indirect

Dans le cas du crédit indirect, on récupère l'identifiant de l'adhésion pour ensuite assigner l'adhésion préalablement créée avec le contact id du filleul

On créé également un enregistrement dans l'entité `ContributionSoft`

### Adhésion existante

Si la colonne `membership_id` est remplie, sa valeur est passée à `line_items[0]['params']['id']`. L'Order API met alors à jour cette adhésion au lieu d'en créer une.

L'adhésion doit déjà exister dans la base cible, avec ce même identifiant, et appartenir au `contact_id` de la ligne. Le contact propriétaire ne doit pas être supprimé. Un identifiant issu d'un autre environnement (production vers sandbox, par exemple) ne correspond pas.

### Identifiants de tarif

La colonne CSV `price_field_value_id` est lue comme un identifiant de champ de prix (`price_field_id`), pas comme un identifiant d'option. `getTarif()` filtre avec `price_set_id` et `price_field_id`.

Les valeurs envoyées à l'Order API sont ensuite celles-ci :

- `price_field_id` reçoit `price_field_id.price_set_id` (l'ensemble de prix)
- `price_field_value_id` reçoit `price_field_id.id` (le champ de prix)

L'option de tarif réellement utilisée est `$tarif[0]['id']`. Le type d'adhésion vient de `$tarif[0]['membership_type_id.id']`.

### Fréquence

Après la création de la contribution par l'Order API, si la colonne `Fréquence` est remplie, sa valeur est écrite sur la contribution créée.

La mise à jour passe par l'API4 `Contribution.update`, sur le champ personnalisé `Frequence.Fr_quence_Don`. Elle ne s'exécute que si l'Order API a renvoyé un `contribution_id`.

La valeur du CSV est enregistrée telle quelle. Elle doit correspondre à une option valide de ce champ.

### Dépannage

- **Symptôme** : `Failed with order API: Expected one Membership but found 0`
  - **Cause probable** : `membership_id` est renseigné, et l'API Membership ne renvoie aucune ligne pour cet identifiant (absent de la base, autre contact, contact supprimé, ou adhésion de test).
  - **Contrôle** : `Membership.get` avec `id` égal à la valeur de la ligne, puis la même requête avec `contact_id`.

## Requis

Extension [ADVimport](https://lab.civicrm.org/extensions/advimport)

