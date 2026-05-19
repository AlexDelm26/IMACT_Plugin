# ImactPlugin — Plugin Jeedom

Plugin Jeedom de création d'éclairages, thermostats, volets et automates, développé dans le cadre d'un projet IMACT.

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
│   ├── ajax/
│   │    └──ImactPlugin.ajax.php    # Pont Back-Front
│   └── class/
│        └── ImactPlugin.class.php   # Classe principale, logique métier
│   
├── desktop/
│   ├── js/
│   │   └── ImactPlugin.js          # Interface frontend
│   ├── php/
│   │   └── ImactPlugin.php         # Vue principale
│   └── modal/
│   │   └── addLED.php              # Vue des fonctions
├── plugin_info/
│       └── info.json                   # Métadonnées du plugin
└── README.md
```

---

## Fonctions principales

### `createVirtualLEDs(array $leds)`
Crée et configure un ou plusieurs virtuels Jeedom pour piloter l'éclairage à partir d'un tableau de paramètres.

### `createThermostat(array $thermostats)`
Crée et configure un ou plusieurs thermostats Jeedom à partir d'un tableau de paramètres.

**Modes créés automatiquement :** Boost, Confort, Absent, Eco, Hors-Gel


### `createVolet(array $volets)`
Crée et configure un ou plusieurs volets Jeedom à partir d'un tableau de paramètres.

### `convertAutomate(array $automates)`
Copie des commandes dans un virtuel avec ou sans filtre
