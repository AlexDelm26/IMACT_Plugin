<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../../../core/php/core.inc.php';

class ImactPlugin extends eqLogic
{
  public static function createVirtualLEDs($leds)
  {
    log::add('ImactPlugin', 'debug', '=== Début createVirtualLEDs ===');

    try {
      include_file('core', 'virtual', 'class', 'virtual');
      log::add('ImactPlugin', 'debug', 'virtual.class.php chargé');

      $ledCreated = 0;

      foreach ($leds as $led) {

        $eqLogic = eqLogic::byId($led['idEquipement']);
        if (!$eqLogic) {
          log::add('ImactPlugin', 'error', 'Equipement ID ' . $led['idEquipement'] . ' introuvable');
          continue;
        }
        $nomComplet = explode(' - ', $eqLogic->getName());
        log::add('ImactPlugin', 'debug', print_r($nomComplet, true));
        log::add('ImactPlugin', 'debug', 'Equipement source: ' . $eqLogic->getName());

        // Créer l'équipement virtuel
        $virtual = new virtual();
        $virtual->setEqType_name('virtual');
        $virtual->setName($nomComplet[1]);
        $virtual->setLogicalId('led_' . uniqid());
        $virtual->setObject_id(2);
        $virtual->setIsEnable(1);
        $virtual->setIsVisible(1);
        $virtual->save();
        log::add('ImactPlugin', 'debug', 'Virtuel créé: ' . $virtual->getName());

        // Créer les commandes
        log::add('ImactPlugin', 'debug', 'Création commande info...');
        $cmdInfo = self::createInfoCommand($virtual, $eqLogic);

        log::add('ImactPlugin', 'debug', 'Création commandes action...');
        log::add('ImactPlugin', 'debug', 'cmdInfo avant appel: ' . ($cmdInfo ? $cmdInfo->getId() : 'NULL'));
        log::add('ImactPlugin', 'debug', 'Type cmdInfo: ' . get_class($cmdInfo));

        try {
          self::createActionCommands($virtual, $eqLogic, $cmdInfo);
          log::add('ImactPlugin', 'debug', 'Retour de createActionCommands OK');
        } catch (Exception $e) {
          log::add('ImactPlugin', 'error', 'Exception dans createActionCommands: ' . $e->getMessage());
          log::add('ImactPlugin', 'error', 'Ligne: ' . $e->getLine());
          log::add('ImactPlugin', 'error', 'Fichier: ' . $e->getFile());
          throw $e;
        }

        $ledCreated++;
        log::add('ImactPlugin', 'debug', 'LED créée avec succès');
      }

      log::add('ImactPlugin', 'debug', 'Total LEDs créées: ' . $ledCreated);
      return $ledCreated;

    } catch (Exception $e) {
      log::add('ImactPlugin', 'error', 'Erreur dans createVirtualLEDs: ' . $e->getMessage());
      log::add('ImactPlugin', 'error', 'Ligne: ' . $e->getLine());
      throw $e;
    }
  }

  private static function createInfoCommand($virtual, $eqLogic)
  {
    log::add('ImactPlugin', 'debug', '=== Début createInfoCommand ===');

    $cmdSource = $eqLogic->getCmd('info', 'state');

    if (!$cmdSource) {
      throw new Exception('Commande state introuvable sur ' . $eqLogic->getName());
    }

    log::add('ImactPlugin', 'debug', 'Commande state trouvée: ' . $cmdSource->getHumanName());

    $cmdInfo = new cmd();
    $cmdInfo->setName('Etat');
    $cmdInfo->setEqLogic_id($virtual->getId());
    $cmdInfo->setLogicalId('etatLed');
    $cmdInfo->setType('info');
    $cmdInfo->setSubType('binary');
    $cmdInfo->setIsVisible(0);
    $cmdInfo->setIsHistorized(1);
    $cmdInfo->setConfiguration('calcul', '#' . $cmdSource->getId() . '#');
    $cmdInfo->save();

    log::add('ImactPlugin', 'debug', 'Commande Etat créée - ID: ' . $cmdInfo->getId());

    $valeurInitiale = $cmdSource->execCmd(); // Récupérer la valeur actuelle de l'équipement
    log::add('ImactPlugin', 'debug', 'Valeur initiale du ventilateur: ' . $valeurInitiale);

    $cmdInfo->event($valeurInitiale); // Mettre à jour avec la valeur initiale
    log::add('ImactPlugin', 'debug', 'Commande Etat initialisée avec: ' . $valeurInitiale);



    return $cmdInfo;
  }

  private static function createActionCommands($virtual, $eqLogic, $cmdInfo)
  {
    if (!$cmdInfo) {
      throw new Exception('cmdInfo est null !');
    }
    $cmdSourceOn = $eqLogic->getCmd('action', 'json::{"state":"ON"}');
    $cmdSourceOff = $eqLogic->getCmd('action', 'json::{"state":"OFF"}');

    if (!$cmdSourceOn || !$cmdSourceOff) {
      throw new Exception('Commandes ON/OFF introuvables');
    }

    // On
    $cmdOn = new virtualCmd();
    $cmdOn->setName('on');
    $cmdOn->setEqLogic_id($virtual->getId());
    $cmdOn->setType('action');
    $cmdOn->setSubType('other');
    $cmdOn->setValue($cmdInfo->getId());
    $cmdOn->setConfiguration('virtualAction', '1');
    $cmdOn->setConfiguration('infoName', '#' . $cmdSourceOn->getId() . '#');#61#
    $cmdOn->setDisplay('showNameOndashboard', '0');
    $cmdOn->setDisplay('showNameOnmobile', '0');
    $cmdOn->setConfiguration('updateCmdId', $cmdInfo->getId());
    $cmdOn->save();
    log::add('ImactPlugin', 'debug', 'Commande on créée - infoName: #' . $cmdSourceOn->getId() . '#');


    // Off
    $cmdOff = new virtualCmd();
    $cmdOff->setName('off');
    $cmdOff->setEqLogic_id($virtual->getId());
    $cmdOff->setType('action');
    $cmdOff->setSubType('other');
    $cmdOff->setValue($cmdInfo->getId());
    $cmdOff->setConfiguration('virtualAction', '0');
    $cmdOff->setConfiguration('infoName', '#' . $cmdSourceOff->getId() . '#');#60#
    $cmdOff->setDisplay('showNameOndashboard', '0');
    $cmdOff->setDisplay('showNameOnmobile', '0');
    $cmdOff->setConfiguration('updateCmdId', $cmdInfo->getId());
    $cmdOff->save();
    log::add('ImactPlugin', 'debug', 'Commande off créée - infoName: #' . $cmdSourceOff->getId() . '#');
  }

  public static function createThermostat($thermostats)
  {
    log::add('ImactPlugin', 'debug', 'createThermostat appelé !');
    try {
      include_file('core', 'thermostat', 'class', 'thermostat');
      if (!class_exists('thermostat')) {
        log::add('ImactPlugin', 'debug', 'class thermostat introuvable');
      }

      $idTemperature = 33430; // 104 sur la template
      foreach ($thermostats as $thermostat) {
        $thermo = new thermostat();
        $thermo->setName($thermostat['nomThermostat']);
        $thermo->setEqType_name('thermostat');
        $thermo->setIsEnable(1);
        $thermo->setIsVisible(1);
        /* Fix */
        $thermo->setObject_id(null); // A changer plus tard
        $thermo->setConfiguration('order_min', 5);
        $thermo->setConfiguration('order_max', 28);
        $thermo->setConfiguration('engine', 'temporal');
        $thermo->setConfiguration('allow_mode', 'heat');
        $thermo->save();
        /* */
        // Action
        if ($thermostat['commandeChauffer']) {
          $thermo->setConfiguration('heating', [
            [
              'cmd' => '#' . $thermostat['commandeChauffer'] . '#',
              'options' => ['slider' => '']
            ]
          ]);
        }
        if ($thermostat['commandeArreter']) {
          $thermo->setConfiguration('stoping', [
            [
              'cmd' => '#' . $thermostat['commandeArreter'] . '#',
              'options' => ['slider' => '']
            ]
          ]);
        }
        if ($thermostat['commandeConsigne']) {
          $thermo->setConfiguration('orderChange', [
            [
              'cmd' => '#' . $thermostat['commandeConsigne'] . '#',
              'options' => ['slider' => '']
            ]
          ]);
        }

        if ($thermostat['temperatureInterieure']) {
          $thermo->setConfiguration('temperature_indoor', '#' . $thermostat['temperatureInterieure'] . '#');
        }
        $thermo->setConfiguration('temperature_indoor_min', 0);
        $thermo->setConfiguration('temperature_indoor_max', 40);
        $thermo->setConfiguration('temperature_outdoor', '#' . cmd::byId($idTemperature)->getId() . '#');
        if ($thermostat['commandePersonnelle']) {
          $thermo->setConfiguration('customCmd', '#' . $thermostat['commandePersonnelle'] . '#');
        }
        $thermo->setConfiguration('hideLockCmd', 1);

        $commandesZone = eqLogic::byId($thermostat['consigneZone']);
        $modesThermostat = $thermo->getCmd('action', 'thermostat');
        $thermo->setConfiguration('existingMode', [
          [
            'isVisible' => 1,
            'name' => 'Boost',
            'actions' => [
              [
                'cmd' => '#' . $modesThermostat->getId() . '#',
                'options' => ['slider' => '#' . $commandesZone->getCmd('info', 'ConsigneBoost')->getId() . '#'] // à mettre dynamiquement
              ]
            ]
          ],
          [
            'isVisible' => 1,
            'name' => 'Confort',
            'actions' => [
              [
                'cmd' => '#' . $modesThermostat->getId() . '#',
                'options' => ['slider' => '#' . $commandesZone->getCmd('info', 'ConsigneConfort')->getId() . '#'] // à mettre dynamiquement
              ]
            ]
          ],
          [
            'isVisible' => 1,
            'name' => 'Eco',
            'actions' => [
              [
                'cmd' => '#' . $modesThermostat->getId() . '#',
                'options' => ['slider' => '#' . $commandesZone->getCmd('info', 'ConsigneEco')->getId() . '#'] // à mettre dynamiquement
              ]
            ]
          ],
          [
            'isVisible' => 1,
            'name' => 'Absent',
            'actions' => [
              [
                'cmd' => '#' . $modesThermostat->getId() . '#',
                'options' => ['slider' => '#' . $commandesZone->getCmd('info', 'ConsigneAbsent')->getId() . '#'] // à mettre dynamiquement
              ]
            ]
          ],
          [
            'isVisible' => 1,
            'name' => 'Hors Gel',
            'actions' => [
              [
                'cmd' => '#' . $modesThermostat->getId() . '#',
                'options' => ['slider' => '#' . $commandesZone->getCmd('info', 'ConsigneHorsGel')->getId() . '#'] // à mettre dynamiquement
              ]
            ]
          ],
        ]);
        $thermo->save();
        // log::add('ImactPlugin', 'debug', 'config après save : ' . json_encode($thermo->getConfiguration()));
      }

    } catch (\Throwable $th) {
      log::add('ImactPlugin', 'error', 'Erreur createThermostat : ' . $th->getMessage() . ' ligne ' . $th->getLine() . ' dans ' . $th->getFile());
    }
    return 'ajout thermostat';
  }
  public static function verifyDuplicateName($nameChamps):array{
    $duplicateName=[];
    
    if(in_array($nameChamps,$nameDB)){
      array_push($duplicateName,$nameChamps); // Ajoute dans le tableau, les noms déjà existants
    }

    return $duplicateName;
  }
  public static function log()
  {
    $eqLogic = eqLogic::byId(440);
    log::add('ImactPlugin', 'debug', '=== Commandes de ' . $eqLogic->getName() . ' ===');
    // log::add('ImactPlugin', 'debug', $eqLogic->getCmd('action', 'thermostat')->getHumanName());
    // log::add('ImactPlugin', 'debug', print_r($eqLogic->getCmd(), true));
    $i = 0;
    foreach ($eqLogic->getCmd() as $cmd) {
      log::add('ImactPlugin', 'debug', 'Id: ' . $i . ' | ' . 'Nom: ' . $cmd->getName() . ' | LogicalId: ' . $cmd->getLogicalId() . ' | Type: ' . $cmd->getType());
      $i = $i + 1;
    }
    foreach ($eqLogic->getCmd() as $consigneZone) {
      // log::add('ImactPlugin', 'debug', print_r($consigneZone, true));
      log::add('ImactPlugin', 'debug', $consigneZone->getConfiguration('calcul'));
    }
  }
}

