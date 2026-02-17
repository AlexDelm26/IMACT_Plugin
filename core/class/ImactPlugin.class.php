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
<<<<<<< HEAD
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
}
=======
  /*     * *************************Attributs****************************** */

  /*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
  * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
  public static $_widgetPossibility = array();
  */

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration du plugin
  * Exemple : "param1" & "param2" seront cryptés mais pas "param3"
  public static $_encryptConfigKey = array('param1', 'param2');
  */

  /*     * ***********************Methode static*************************** */

  /*
  * Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly() {}
  */

  /*
  * Fonction exécutée automatiquement tous les jours par Jeedom
  public static function cronDaily() {}
  */

  /*
  * Permet de déclencher une action avant modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function preConfig_param3( $value ) {
    // do some checks or modify on $value
    return $value;
  }
  */

  /*
  * Permet de déclencher une action après modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function postConfig_param3($value) {
    // no return value
  }
  */

  /*
   * Permet d'indiquer des éléments supplémentaires à remonter dans les informations de configuration
   * lors de la création semi-automatique d'un post sur le forum community
   public static function getConfigForCommunity() {
      // Cette function doit retourner des infos complémentataires sous la forme d'un
      // string contenant les infos formatées en HTML.
      return "les infos essentiel de mon plugin";
   }
   */

  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert()
  {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert()
  {
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate()
  {
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate()
  {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave()
  {
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave()
  {
    
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove()
  {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove()
  {
  }

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration des équipements
  * Exemple avec le champ "Mot de passe" (password)
  public function decrypt() {
    $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
  }
  public function encrypt() {
    $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
  }
  */

  /*
  * Permet de modifier l'affichage du widget (également utilisable par les commandes)
  public function toHtml($_version = 'dashboard') {}
  */
  public function addLEDS($leds){
    
  }

  /*     * **********************Getteur Setteur*************************** */
}

class ImactPluginCmd extends cmd
{
  /*     * *************************Attributs****************************** */

  /*
  public static $_widgetPossibility = array();
  */

  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  /*
  * Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
  public function dontRemoveCmd() {
    return true;
  }
  */

  // Exécution d'une commande
  public function execute($_options = array())
  {
  }

  
  /*     * **********************Getteur Setteur*************************** */
}
>>>>>>> 0da36e3baf8fa9b0c5c75851c403a643fb08f0cd
