# ImactPlugin — Plugin Jeedom

Plugin Jeedom de gestion automatique des thermostats, développé dans le cadre d'un projet IMACT.

---

## Prérequis

### Template
> ⚠️ Ce plugin est conçu pour fonctionner **uniquement à partir du template Jeedom fourni**.  
> Les IDs internes (sondes, objets, commandes) sont basés sur ce template et ne doivent pas être modifiés.

---

## Architecture du projet

```
ImactPlugin/
├── core/
    ├── ajax/
    │    └──ImactPlugin.ajax.php    # Pont Back-Front
│   ├── class/
│   │   └── ImactPlugin.class.php   # Classe principale, logique métier
│   └── php/
│       └── ...                     # Fonctions utilitaires
├── desktop/
│   ├── js/
│   │   └── ImactPlugin.js          # Interface frontend
│   └── php/
│       └── ImactPlugin.php         # Vue principale
├── plugin_info/
│   └── info.json                   # Métadonnées du plugin
└── README.md
```

---

## Fonctions principales

### `createThermostat(array $thermostats)`
Crée et configure un ou plusieurs thermostats Jeedom à partir d'un tableau de paramètres.

**Modes créés automatiquement :** Boost, Confort, Absent, Eco, Hors-Gel

---

### `configureDisplayThermostat(int $id, mixed $commandePersonnelle)`
Configure l'affichage du thermostat après sa création.

---

## Logs

Les logs sont accessibles depuis **Analyse → Logs → ImactPlugin**.

Niveaux utilisés :
- `debug` : étapes de création
- `error` : erreurs avec message, ligne et fichier

---

## Limitations connues

- Les modes de consigne (Boost, Confort, etc.) sont fixes et définis à la création
- Le plugin ne gère pas la suppression ou la mise à jour d'un thermostat existant

---

## Développement futur

Pistes d'amélioration identifiées en fin de projet :
- Rendre les modes configurables depuis l'interface
- Ajouter une validation des paramètres en entrée de `createThermostat()`
- Factoriser la construction des modes (actuellement répétitive)
