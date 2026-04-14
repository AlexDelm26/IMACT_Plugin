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

      $ledCreated = 0;

      foreach ($leds as $led) {

        $eqLogic = eqLogic::byId($led['idEquipement']);
        if (!$eqLogic) {
          log::add('ImactPlugin', 'error', 'Equipement ID ' . $led['idEquipement'] . ' introuvable');
          continue;
        }

        $nomComplet = explode(' - ', $eqLogic->getName());
        $nom = $nomComplet[1] ?? $nomComplet[0];

        // Commandes source
        $cmdSource = $eqLogic->getCmd('info', 'state');
        $cmdSourceOn = $eqLogic->getCmd('action', 'json::{"state":"ON"}');
        $cmdSourceOff = $eqLogic->getCmd('action', 'json::{"state":"OFF"}');

        if (!$cmdSource || !$cmdSourceOn || !$cmdSourceOff) {
          throw new Exception('Commandes state/ON/OFF introuvables sur ' . $eqLogic->getName());
        }

        // Équipement virtuel
        $virtual = new virtual();
        $virtual->setEqType_name('virtual');
        $virtual->setName($nom);
        $virtual->setLogicalId('led_' . uniqid());
        $virtual->setObject_id(2);
        $virtual->setIsEnable(1);
        $virtual->setIsVisible(1);
        $virtual->save();

        // Commande info Etat
        // $cmdInfo = new virtualCmd();
        // $cmdInfo->setName('Etat');
        // $cmdInfo->setEqLogic_id($virtual->getId());
        // $cmdInfo->setLogicalId('etatLed');
        // $cmdInfo->setType('info');
        // $cmdInfo->setSubType('binary');
        // $cmdInfo->setIsVisible(0);
        // $cmdInfo->setIsHistorized(1);
        // $cmdInfo->setConfiguration('calcul', '#' . $cmdSource->getId() . '#');
        // $cmdInfo->save();
        $idEtat=self::createCommandLed('Etat',$virtual->getId(),'',$cmdSource,'info');
        self::createCommandLed('On',$virtual->getId(),$idEtat,$cmdSource,'action');
        self::createCommandLed('Off',$virtual->getId,$idEtat,$cmdSource,'action');

        // Commande action On
        // $cmdOn = new virtualCmd();
        // $cmdOn->setName('on');
        // $cmdOn->setEqLogic_id($virtual->getId());
        // $cmdOn->setType('action');
        // $cmdOn->setSubType('other');
        // $cmdOn->setValue($cmdInfo->getId());
        // $cmdOn->setConfiguration('virtualAction', '1');
        // $cmdOn->setConfiguration('infoName', '#' . $cmdSourceOn->getId() . '#');
        // $cmdOn->setTemplate('dashboard', 'custom::Lumière ON/OFF');
        // $cmdOn->setTemplate('mobile', 'custom::Lumière ON/OFF');
        // $cmdOn->setDisplay('showNameOndashboard', '0');
        // $cmdOn->setDisplay('showNameOnmobile', '0');
        // $cmdOn->save();

        // Commande action Off
        // $cmdOff = new virtualCmd();
        // $cmdOff->setName('off');
        // $cmdOff->setEqLogic_id($virtual->getId());
        // $cmdOff->setType('action');
        // $cmdOff->setSubType('other');
        // $cmdOff->setValue($cmdInfo->getId());
        // $cmdOff->setConfiguration('virtualAction', '1');
        // $cmdOff->setConfiguration('infoName', '#' . $cmdSourceOff->getId() . '#');
        // $cmdOff->setDisplay('showNameOndashboard', '0');
        // $cmdOff->setDisplay('showNameOnmobile', '0');
        // $cmdOff->setTemplate('dashboard', 'custom::Lumière ON/OFF');
        // $cmdOff->setTemplate('mobile', 'custom::Lumière ON/OFF');
        // $cmdOff->save();

        $ledCreated++;
        log::add('ImactPlugin', 'debug', 'LED créée: ' . $nom);
      }

      log::add('ImactPlugin', 'debug', 'Total LEDs créées: ' . $ledCreated);
      return $ledCreated;

    } catch (Exception $e) {
      log::add('ImactPlugin', 'error', 'Erreur createVirtualLEDs: ' . $e->getMessage() . ' ligne ' . $e->getLine());
      throw $e;
    }

  }
  public static function createCommandLed($name, $idVirtual, $value, $cmdSource, $typeCommande)
  {
    $cmd = new virtualCmd();
    $cmd->setName($name);
    $cmd->setEqLogic_id($idVirtual);
    $cmd->setType($typeCommande);
    $cmd->setSubType(($typeCommande === 'info') ? 'binary' : 'other');
    $cmd->setConfiguration(($typeCommande === 'info') ? 'calcul' : 'infoName', '#' . $cmdSource . '#');
    if ($typeCommande === 'info') {
      $cmd->setIsVisible(0);
      $cmd->setIsHistorized(1);
      $cmd->save();
      return $cmd->getId();
      } else {
      $cmd->setValue($value);
      $cmd->setConfiguration('virtualAction', '1');
      $cmd->setDisplay('showNameOndashboard', '0');
      $cmd->setDisplay('showNameOnmobile', '0');
      $cmd->setTemplate('dashboard', 'custom::Lumière ON/OFF');
      $cmd->setTemplate('mobile', 'custom::Lumière ON/OFF');
      $cmd->save();
    }
    
  }

  public static function createThermostat($thermostats)
  {
    log::add('ImactPlugin', 'debug', 'createThermostat appelé !');
    try {
      // include_file('core', 'thermostat', 'class', 'thermostat');
      // if (!class_exists('thermostat')) {
      //   log::add('ImactPlugin', 'debug', 'class thermostat introuvable');
      // }

      $idTemperature = 33430; // 104 sur la template | 33430 au bureau
      foreach ($thermostats as $thermostat) {
        $thermo = new thermostat();
        $thermo->setName($thermostat['nomThermostat']);
        $thermo->setEqType_name('thermostat');
        $thermo->setIsEnable(1);
        $thermo->setIsVisible(1);
        /* Fix */
        $thermo->setObject_id(2); // 22 sur la template
        $thermo->setConfiguration('order_min', 5);
        $thermo->setConfiguration('order_max', 28);
        $thermo->setConfiguration('engine', 'temporal');
        $thermo->setConfiguration('allow_mode', 'heat');
        $thermo->save();
        log::add('ImactPlugin', 'debug', $thermostat['nomThermostat'] . ' créé');
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
        log::add('ImactPlugin', 'debug', 'Commandes créés');
        $cmdMode = $thermo->getCmd('action', 'thermostat');        // boutons modes
        $cmd = $thermo->getCmd('info', 'mode');
        $cmd->setTemplate('dashboard', 'custom::Thermostat_statut_All');
        $cmd->save();
        $widgetPath = __DIR__ . '/../../data/customTemplates/dashboard/cmd.info.string.Thermostat_statut_All.html';
        $files = glob('/var/www/html/data/customTemplates/dashboard/cmd.info.string.*.html');
        foreach ($files as $file) {
          log::add('ImactPlugin', 'debug', basename($file));
        }
        log::add('ImactPlugin', 'debug', "Fichier widget existe : " . (file_exists($widgetPath) ? 'OUI' : 'NON') . "\n");
        $thermo->getCmd('info', 'mode')->setTemplate('dashboard', 'Thermostat_statut_All');
        $cmdOnOff = $thermo->getCmd('action', 'thermostat_mode');   // on/off
        $cmdOrder = $thermo->getCmd('action', 'order');             // consigne
        $cmdState = $thermo->getCmd('info', 'state');               // état chauffe
        $cmdPower = $thermo->getCmd('info', 'power');               // puissance
        $cmdTempIn = $thermo->getCmd('info', 'temperature');         // temp intérieure
        $cmdTempIn->setTemplate('dashboard', 'customtemp::thermometre');
        $cmdTempIn->save();
        $cmdTempOut = cmd::byId($idTemperature);
        $cmdTempOut->setTemplate('dashboard', 'customtemp::thermometre');
        $cmdTempOut->save();

        // $layout = [
        //   "backGraph::info" => "0",
        //   "parameters" => [],
        //   "height" => "464px",
        //   "width" => "534px",
        //   "backGraph::format" => "month",
        //   "backGraph::type" => "areaspline",
        //   "backGraph::color" => "#4572a7",
        //   "layout::dashboard" => "table",
        //   "layout::dashboard::table::nbLine" => "5",
        //   "layout::dashboard::table::nbColumn" => "2",
        //   "layout::dashboard::table::parameters" => [
        //     "center" => "1",
        //     "styletable" => "",
        //     "styletd" => "",
        //     "text::td::1::1" => "",
        //     "style::td::1::1" => "colspan=\"2\"",
        //     "text::td::1::2" => "",
        //     "style::td::1::2" => "display:none",
        //     "text::td::2::1" => "",
        //     "style::td::2::1" => "colspan=\"2\"",
        //     "text::td::2::2" => "",
        //     "style::td::2::2" => "display:none",
        //     "text::td::3::1" => "",
        //     "style::td::3::1" => "colspan=\"2\"",
        //     "text::td::3::2" => "",
        //     "style::td::3::2" => "display:none",
        //     "text::td::4::1" => "",
        //     "style::td::4::1" => "",
        //     "text::td::4::2" => "",
        //     "style::td::4::2" => "",
        //     "text::td::5::1" => "Température extérieure",
        //     "style::td::5::1" => "",
        //     "text::td::5::2" => "Température intérieure",
        //     "style::td::5::2" => "",
        //   ],
        // ];
        $thermo->setDisplay('layout::dashboard', 'table');
        $thermo->setDisplay('layout::dashboard::table::nbColumn', 2);
        $thermo->setDisplay('layout::dashboard::table::nbLine', 5);
        $thermo->setDisplay('layout::dashboard::table::parameters', [
          'style::td::1::1' => 'colspan="2"',
          'style::td::1::2' => 'display:none',
        ]);
        $thermo->save();
        if ($cmdMode) {
          $thermo->setDisplay("layout::dashboard::table::cmd::" . $cmdMode->getId() . "::line", 2);
          $thermo->setDisplay("layout::dashboard::table::cmd::" . $cmdMode->getId() . "::column", 1);
        }
        // if ($cmdOrder) {
        //   $layout["layout::dashboard::table::cmd::" . $cmdOrder->getId() . "::line"] = "3";
        //   $layout["layout::dashboard::table::cmd::" . $cmdOrder->getId() . "::column"] = "1";
        // }
        // if ($cmdState) {
        //   $layout["layout::dashboard::table::cmd::" . $cmdState->getId() . "::line"] = "4";
        //   $layout["layout::dashboard::table::cmd::" . $cmdState->getId() . "::column"] = "1";
        // }
        // if ($cmdPower) {
        //   $layout["layout::dashboard::table::cmd::" . $cmdPower->getId() . "::line"] = "4";
        //   $layout["layout::dashboard::table::cmd::" . $cmdPower->getId() . "::column"] = "2";
        // }
        // if ($cmdTempOut) {
        //   $layout["layout::dashboard::table::cmd::" . $cmdTempOut->getId() . "::line"] = "5";
        //   $layout["layout::dashboard::table::cmd::" . $cmdTempOut->getId() . "::column"] = "1";
        // }
        // if ($cmdTempIn) {

        //   $thermo->setDisplay('layout::dasgboard::table::cmd::' . $cmdTempIn->getId() . '::line', 5);
        //   $thermo->setDisplay('layout::dasgboard::table::cmd::' . $cmdTempIn->getId() . '::column', 1);

        //   $layout["layout::dashboard::table::cmd::" . $cmdTempIn->getId() . "::line"] = "5";
        //   $layout["layout::dashboard::table::cmd::" . $cmdTempIn->getId() . "::column"] = "2";
        // }

        // foreach ($layout as $key => $value) {
        //   $thermo->setDisplay($key, $value);
        // }
        $thermo->save();
        // log::add('ImactPlugin', 'debug', 'config après save : ' . json_encode($thermo->getConfiguration()));
      }

    } catch (\Throwable $th) {
      log::add('ImactPlugin', 'error', 'Erreur createThermostat : ' . $th->getMessage() . ' ligne ' . $th->getLine() . ' dans ' . $th->getFile());
      throw $th;
    }
    return 'ajout thermostat';
  }
  public static function verifyDuplicateName($thermostats): array
  {
    $duplicateName = [];
    $nomsCherches = array_map(fn($t) => trim($t['nomThermostat']), $thermostats);

    if (empty($nomsCherches)) {
      return [];
    }

    // Récupérer tous les thermostats existants en DB
    $existingThermostats = eqLogic::byType('thermostat');
    $nomsEnDB = array_map(fn($eq) => strtolower($eq->getName()), $existingThermostats);

    foreach ($thermostats as $thermostat) {
      if (in_array(strtolower(trim($thermostat['nomThermostat'])), $nomsEnDB)) {
        $duplicateName[] = [
          'numeroThermostat' => $thermostat['numeroThermostat'],
          'nomThermostat' => $thermostat['nomThermostat']
        ];
      }
    }

    return $duplicateName;
  }

  public static function verifyVoletPropExist()
  {
    $plugin = plugin::byId('voletProp');
    return $plugin->isActive();
  }
  public static function createVolet($volets)
  {
    log::add('ImactPlugin', 'debug', var_export($volets, true));
    $logicalIdPerPlugin = [
      'z2m' => [
        'action' => [
          'ouvrir' => 'json::{"state":"OPEN"}',
          'fermer' => 'json::{"state":"CLOSE"}',
          'stop' => 'json::{"state":"STOP"}',
          'slider' => 'json::{"position":"#slider#"}'
        ],
        'info' => [
          'position' => 'position',
        ],
      ],
      'rfxcom' => [
        'action' => [
          'ouvrir' => 'open',
          'fermer' => 'close',
          'stop' => 'stop',
          'slider' => 'setClosure'
        ],
        'info' => [
          'position' => 'core:ClosureState',
        ],
      ],
    ];

    try {
      foreach ($volets as $volet) {
        include_file('core', 'voletProp', 'class', 'voletProp');
        include_file('core', 'virtual', 'class', 'virtual');
        $eqLogic = eqLogic::byId($volet['idVolet']);
        $plugin = $eqLogic->getEqType_name();
        $nomComplet = explode(' - ', $eqLogic->getName());
        if ($plugin == 'virtual') {
          $plugin = $nomComplet[1];
        }
        $cmds = [];
        foreach ($logicalIdPerPlugin[$plugin] as $type => $logicalIds) {
          foreach ($logicalIds as $nom => $logicalId) {
            $cmds[$nom] = $eqLogic->getCmd($type, $logicalId);
            log::add('scenario', 'debug', "$nom : " . ($cmds[$nom] ? $cmds[$nom]->getId() : 'non trouvé'));
          }
        }

        if ($volet['etatRetour']) {
          $virtual = new virtual();
          $virtual->setName($nomComplet[1]);
          $virtual->setEqType_name('virtual');
          $virtual->setIsEnable(1);
          $virtual->setIsVisible(1);
          $virtual->setObject_id(($eqLogic->getEqType_name() == 'virtual') ? null : 10); // 10 sur la template
          $virtual->save();

          self::createCommandVolet('etat position', 'hauteur', $virtual->getId(), $cmds['position']->getId());

          self::createCommandVolet('ouvrir', 'up', $virtual->getId(), $cmds['ouvrir']->getId());

          self::createCommandVolet('fermer', 'down', $virtual->getId(), $cmds['fermer']->getId());

          self::createCommandVolet('stop', 'stop', $virtual->getId(), $cmds['stop']->getId());

          self::createCommandVolet('position', 'position', $virtual->getId(), $cmds['slider']->getId());

          self::createVisuelVolet($virtual, $volet['etatRetour']);

          // $voletUp = $virtual->getCmd('action', 'up');
          // $voletUp->setOrder(1);
          // $voletUp->setDisplay('forceReturnLineAfter', 1);
          // $voletUp->setDisplay('icon', '<i class="fa fa-arrow-up"></i>');
          // $voletUp->save();

          // $voletStop = $virtual->getCmd('action', 'stop');
          // $voletStop->setOrder(2);
          // $voletStop->setDisplay('forceReturnLineAfter', 1);
          // $voletStop->setDisplay('icon', '<i class="fa fa-stop"></i>');
          // $voletStop->save();

          // $voletDown = $virtual->getCmd('action', 'down');
          // $voletDown->setDisplay('forceReturnLineAfter', 1);
          // $voletDown->setDisplay('icon', '<i class="fa fa-arrow-down"></i>');
          // $voletDown->setOrder(3);
          // $voletDown->save();

          // $hauteur = $virtual->getCmd('info', 'hauteur');
          // $hauteur->setIsVisible(1);
          // $hauteur->setDisplay('showStatsOndashboard', 1);
          // $hauteur->setDisplay('showStatsOnmobile', 1);
          // $hauteur->setDisplay('showNameOndashboard', 0);
          // $hauteur->setTemplate('dashboard', 'custom::Imact - Volets');
          // $hauteur->save();


          // $voletPosition = $virtual->getCmd('action', 'position');
          // $voletPosition->setTemplate('dashboard', 'core::sliderVertical');
          // $voletPosition->save();

          // $virtual->setDisplay('layout::dashboard', 'table');
          // $virtual->setDisplay('layout::dashboard::table::nbColumn', 3);
          // $virtual->setDisplay('layout::dashboard::table::nbLine', 1);

          // $virtual->setDisplay('layout::dashboard::table::cmd::' . $hauteur->getId() . '::line', 1);
          // $virtual->setDisplay('layout::dashboard::table::cmd::' . $hauteur->getId() . '::column', 2);

          // $virtual->setDisplay('layout::dashboard::table::cmd::' . $voletPosition->getId() . '::line', 1);
          // $virtual->setDisplay('layout::dashboard::table::cmd::' . $voletPosition->getId() . '::column', 3);

          // $virtual->setDisplay('layout::dashboard::table::cmd::' . $voletDown->getId() . '::line', 1);
          // $virtual->setDisplay('layout::dashboard::table::cmd::' . $voletDown->getId() . '::column', 1);

          // $virtual->setDisplay('layout::dashboard::table::cmd::' . $voletStop->getId() . '::line', 1);
          // $virtual->setDisplay('layout::dashboard::table::cmd::' . $voletStop->getId() . '::column', 1);

          // $virtual->setDisplay('layout::dashboard::table::cmd::' . $voletUp->getId() . '::line', 1);
          // $virtual->setDisplay('layout::dashboard::table::cmd::' . $voletUp->getId() . '::column', 1);

          // $virtual->save();


        } else {
          $voletProp = new voletProp();
          $voletProp->setName($nomComplet[1]);
          $voletProp->setObject_id(($eqLogic->getEqType_name() == 'virtual') ? null : 10); // 10 sur la template | au bureau : null pour les tests
          $voletProp->setEqType_name('voletProp');
          $voletProp->setIsEnable(1);
          $voletProp->setIsVisible(1);
          $voletProp->setConfiguration('cmdUp', '#' . $volet['cmdOpen'] . '#');
          $voletProp->setConfiguration('cmdStop', '#' . $volet['cmdStop'] . '#');
          $voletProp->setConfiguration('cmdDown', '#' . $volet['cmdClose'] . '#');
          $voletProp->setConfiguration('jeedomState', 1);
          $voletProp->save();

          self::createVisuelVolet($voletProp, $volet['etatRetour']);

          // $voletProp->setDisplay('layout::dashboard', 'table');
          // $voletProp->setDisplay('layout::dashboard::table::nbColumn', 3);
          // $voletProp->setDisplay('layout::dashboard::table::nbLine', 1);

          // $voletDown = $voletProp->getCmd('action', 'down');
          // $voletDown->setOrder(3);
          // $voletDown->setDisplay('forceReturnLineAfter', 1);
          // $voletDown->setDisplay('showNameOnDashboard', 0);
          // $voletDown->save();

          // $voletStop = $voletProp->getCmd('action', 'stop');
          // $voletStop->setOrder(2);
          // $voletStop->setDisplay('forceReturnLineAfter', 1);
          // $voletStop->setDisplay('showNameOnDashboard', 0);
          // $voletStop->save();

          // $voletUp = $voletProp->getCmd('action', 'up');
          // $voletUp->setOrder(1);
          // $voletUp->setDisplay('forceReturnLineAfter', 1);
          // $voletUp->setDisplay('showNameOnDashboard', 0);
          // $voletUp->save();

          // $voletPosition = $voletProp->getCmd('action', 'position');
          // $voletPosition->setDisplay('showNameOndashboard', 0);
          // $voletPosition->setTemplate('dashboard', 'core::sliderVertical');
          // $voletPosition->save();

          // $hauteur = $voletProp->getCmd('info', 'hauteur');
          // $hauteur->setIsVisible(1);
          // $hauteur->setDisplay('showStatsOndashboard', 1);
          // $hauteur->setDisplay('showStatsOnmobile', 1);
          // $hauteur->setDisplay('showNameOndashboard', 0);
          // $hauteur->setTemplate('dashboard', 'custom::Imact - Volets');
          // $hauteur->save();

          // $voletProp->setDisplay('layout::dashboard::table::cmd::' . $hauteur->getId() . '::line', 1);
          // $voletProp->setDisplay('layout::dashboard::table::cmd::' . $hauteur->getId() . '::column', 2);

          // $voletProp->setDisplay('layout::dashboard::table::cmd::' . $voletPosition->getId() . '::line', 1);
          // $voletProp->setDisplay('layout::dashboard::table::cmd::' . $voletPosition->getId() . '::column', 3);

          // $voletProp->setDisplay('layout::dashboard::table::cmd::' . $voletDown->getId() . '::line', 1);
          // $voletProp->setDisplay('layout::dashboard::table::cmd::' . $voletDown->getId() . '::column', 1);

          // $voletProp->setDisplay('layout::dashboard::table::cmd::' . $voletStop->getId() . '::line', 1);
          // $voletProp->setDisplay('layout::dashboard::table::cmd::' . $voletStop->getId() . '::column', 1);

          // $voletProp->setDisplay('layout::dashboard::table::cmd::' . $voletUp->getId() . '::line', 1);
          // $voletProp->setDisplay('layout::dashboard::table::cmd::' . $voletUp->getId() . '::column', 1);

          // $voletProp->save();
          log::add('ImactPlugin', 'debug', 'display : ' . json_encode($voletProp->getDisplay()));

        }
      }
    } catch (\Throwable $th) {
      log::add('ImactPlugin', 'error', 'Erreur volet : ' . $th->getMessage() . ' ligne ' . $th->getLine() . ' dans ' . $th->getFile());
      throw $th;
    }

  }
  public static function createVisuelVolet($volet, $etatRetour)
  {
    $voletDown = $volet->getCmd('action', 'down');
    $voletStop = $volet->getCmd('action', 'stop');
    $voletUp = $volet->getCmd('action', 'up');
    $voletPosition = $volet->getCmd('action', 'position');
    $hauteur = $volet->getCmd('info', 'hauteur');

    $voletDown->setOrder(3);
    $voletDown->setDisplay('forceReturnLineAfter', 1);
    $voletDown->setDisplay('showNameOnDashboard', 0);
    if ($etatRetour) {
      $voletDown->setDisplay('icon', '<i class="fa fa-arrow-down"></i>');
    }
    $voletDown->save();

    $voletStop->setOrder(2);
    $voletStop->setDisplay('forceReturnLineAfter', 1);
    $voletStop->setDisplay('showNameOnDashboard', 0);
    if ($etatRetour) {
      $voletStop->setDisplay('icon', '<i class="fa fa-stop"></i>');
    }
    $voletStop->save();

    $voletUp->setOrder(1);
    $voletUp->setDisplay('forceReturnLineAfter', 1);
    $voletUp->setDisplay('showNameOnDashboard', 0);
    if ($etatRetour) {
      $voletUp->setDisplay('icon', '<i class="fa fa-arrow-up"></i>');
    }
    $voletUp->save();

    $voletPosition->setDisplay('showNameOndashboard', 0);
    $voletPosition->setTemplate('dashboard', 'core::sliderVertical');
    $voletPosition->save();

    $hauteur->setIsVisible(1);
    $hauteur->setDisplay('showStatsOndashboard', 1);
    $hauteur->setDisplay('showStatsOnmobile', 1);
    $hauteur->setDisplay('showNameOndashboard', 0);
    $hauteur->setTemplate('dashboard', 'custom::Imact - Volets');
    $hauteur->save();

    $volet->setDisplay('layout::dashboard', 'table');
    $volet->setDisplay('layout::dashboard::table::nbColumn', 3);
    $volet->setDisplay('layout::dashboard::table::nbLine', 1);
    $volet->setDisplay('layout::dashboard::table::cmd::' . $hauteur->getId() . '::line', 1);
    $volet->setDisplay('layout::dashboard::table::cmd::' . $hauteur->getId() . '::column', 2);

    $volet->setDisplay('layout::dashboard::table::cmd::' . $voletPosition->getId() . '::line', 1);
    $volet->setDisplay('layout::dashboard::table::cmd::' . $voletPosition->getId() . '::column', 3);

    $volet->setDisplay('layout::dashboard::table::cmd::' . $voletDown->getId() . '::line', 1);
    $volet->setDisplay('layout::dashboard::table::cmd::' . $voletDown->getId() . '::column', 1);

    $volet->setDisplay('layout::dashboard::table::cmd::' . $voletStop->getId() . '::line', 1);
    $volet->setDisplay('layout::dashboard::table::cmd::' . $voletStop->getId() . '::column', 1);

    $volet->setDisplay('layout::dashboard::table::cmd::' . $voletUp->getId() . '::line', 1);
    $volet->setDisplay('layout::dashboard::table::cmd::' . $voletUp->getId() . '::column', 1);

    $volet->save();

  }
  public static function createCommandVolet($libelle, $logicalID, $virtual, $commande)
  {
    $cmd = new virtualCmd();
    $cmd->setName(($libelle == 'etat position') ? ucwords($libelle) : ucfirst($libelle));
    $cmd->setEqLogic_id($virtual);
    $cmd->setLogicalId($logicalID);
    $cmd->setType(($libelle == 'etat position') ? 'info' : 'action');
    $cmd->setSubType(($libelle == 'etat position') ? 'numeric' : 'other');
    $cmd->setConfiguration(($libelle == 'etat position') ? 'calcul' : 'infoName', '#' . $commande . '#');
    if ($libelle == 'etat position') {
      $cmd->setUnite('%');
    } else {
      $cmd->setConfiguration('virtualAction', '1');
    }
    $cmd->setDisplay('showNameOndashboard', '0');
    $cmd->setDisplay('showNameOnmobile', '0');
    $cmd->save();
  }
  public static function verifyIsWithoutLogicalId($id)
  {
    $eqLogic = eqLogic::byId($id);
    $plugin = $eqLogic->getEqType_name();
    $nomComplet = explode(' - ', $eqLogic->getName());
    if ($plugin == 'virtual') {
      $plugin = $nomComplet[1];
    }
    return ($plugin == 'rfxcomm') ? true : false;
  }

  public static function copyAllCommands($cmds, $equipementCible, $commandeType, $exclureCommandes1, $exclureCommandes2, $exclureCommandes3, $includeCommandes)
  {
    $commandesCrees = 0;
    foreach ($cmds as $cmd) {
      $oldCmdAction = cmd::byId($cmd->getValue());
      if (!cmd::byEqLogicIdCmdName($equipementCible, $cmd->getName())) {
        if (empty($includeCommandes) || strpos($cmd->getName(), $includeCommandes) !== false) {
          if (
            (empty($exclureCommandes1) || strpos($cmd->getName(), $exclureCommandes1) === false) &&
            (empty($exclureCommandes2) || strpos($cmd->getName(), $exclureCommandes2) === false) &&
            (empty($exclureCommandes3) || strpos($cmd->getName(), $exclureCommandes3) === false)
          ) {

            $newCommande = clone cmd::byId($cmd->getId());
            $newCommande->setId('');
            if ($commandeType == 'calcul') {
              $newCommande->setConfiguration('calcul', '#' . $cmd->getId() . '#');
            } else {
              $newCommande->setConfiguration('infoName', '#' . $cmd->getId() . '#');
              $newCommande->setValue((cmd::byEqLogicIdCmdName($equipementCible, $oldCmdAction->getName()))->getId());

            }
            $newCommande->setEqLogic_id($equipementCible);
            $newCommande->save();
            $commandesCrees++;
          }
        }
      }
    }
    return $commandesCrees;
  }

  public static function convertAutomate($automate)
  {

    $equipementSource = eqLogic::byId($automate['equipementSource']);
    $commandesCrees = 0;

    $actionCmds = $equipementSource->getCmd('action', null);
    $infoCmds = $equipementSource->getCmd('info', null);

    if ($automate['copierAllCommandes']) {
      // Copie les commandes infos d'abord
      $commandesCrees = self::copyAllCommands($infoCmds, $automate['equipementCible'], 'calcul', '', '', '', '');

      // Copie les commandes actions
      $commandesCrees += self::copyAllCommands($actionCmds, $automate['equipementCible'], 'infoName', '', '', '', '');

    } else {
      // Copie les commandes infos d'abord
      $commandesCrees = self::copyAllCommands($infoCmds, $automate['equipementCible'], 'calcul', $automate['exclureCommandes1'], $automate['exclureCommandes2'], $automate['exclureCommandes3'], $automate['commandesContenant']);

      // Copie les commandes actions
      $commandesCrees += self::copyAllCommands($actionCmds, $automate['equipementCible'], 'infoName', $automate['exclureCommandes1'], $automate['exclureCommandes2'], $automate['exclureCommandes3'], $automate['commandesContenant']);

    }
    return $commandesCrees;
  }

}

