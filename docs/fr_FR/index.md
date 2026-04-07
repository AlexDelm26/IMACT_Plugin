# ImactPlugin

Bienvenue sur la documentation du notre plugin **ImactPlugin** !

Vous trouverez ici l'utilisation de notre plugin.

## Description : 

Ce plugin permet d'accélérer le processus de déploiement côté Jeedom.  


## Fonctionnalités :   
* **Ajouter Éclairage** :  
*Créer un virtuel pour un éclairage avec les commandes state, on et off automatiquement*  
  Sélectionner un nombre d'éclairage à créer, puis valider le nombre. Et enfin, sélectionner votre équipement Zigbee.


* **Ajouter Thermostat** :  
*Créer un virtuel pour un thermostat avec les valeurs, la température extérieure, et les modes (Boost, Confort, Eco, Absent, Hors-Gel) automatiquement*  
Sélectionner un nombre de thermostat à créer, puis valider le nombre. Renseignez un nom pour votre thermostat, et une température intérieure.  
Les commandes actions et la commande personnelle sont facultatives.  
S'il y a plusieurs zones, sélectionner une zone, si vide : par défaut


* **Ajouter Volet**  
*Créer un volet virtuel ou proportionnel selon le retour d'état*  
Sélectionner un nombre de thermostat à créer, puis valider le nombre.  
Renseigner l'équipement que vous voulez créer.  
Décocher la case si il n'y a pas de retour d'état  
**__Attention au retour d'état :__**  
**Sans retour d'état :** Somfy RTS  
**Avec retour d'état :** Somfy IO, Zigbee

* **Ajouter Automate**  
*Copier mes commandes de l'automate vers un virtuel*  
Sélectionner un équipement source (l'automate) et un équipement cible (le virtuel)  
Si vous souhaitez copier toutes les commandes, cochez la case.  
Sinon complétez le filtre. **Commandes contenant uniquement** obligatoire

