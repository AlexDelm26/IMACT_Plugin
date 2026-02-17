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

try {
  require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
  include_file('core', 'authentification', 'php');

  if (!isConnect('admin')) {
    throw new Exception(__('401 - Accès non autorisé', __FILE__));
  }


  /* Fonction permettant l'envoi de l'entête 'Content-Type: application/json'
    En V3 : indiquer l'argument 'true' pour contrôler le token d'accès Jeedom
    En V4 : autoriser l'exécution d'une méthode 'action' en GET en indiquant le(s) nom(s) de(s) action(s) dans un tableau en argument
  */
  ajax::init();

  log::add('ImactPlugin', 'debug', '=== AJAX appelé ===');
  log::add('ImactPlugin', 'debug', 'Action: ' . init('action'));
  if (init('action') == 'addLEDS') {
    // Vérifier les données brutes
    log::add('ImactPlugin', 'debug', 'LEDs brutes: ' . init('leds'));

    $leds = json_decode(init('leds'), true);

    // Vérifier si le décodage a fonctionné
    if ($leds === null) {
      log::add('ImactPlugin', 'error', 'Erreur JSON decode: ' . json_last_error_msg());
      throw new Exception('Données JSON invalides');
    }

    log::add('ImactPlugin', 'debug', 'LEDs décodées: ' . print_r($leds, true));

    // Vérifier que la classe existe
    if (!class_exists('ImactPlugin')) {
      log::add('ImactPlugin', 'error', 'Classe ImactPlugin introuvable');
      throw new Exception('Classe ImactPlugin non chargée');
    }

    log::add('ImactPlugin', 'debug', 'Appel de createVirtualLEDs...');
    $ledCreated = ImactPlugin::createVirtualLEDs($leds);

    log::add('ImactPlugin', 'debug', 'LEDs créées: ' . $ledCreated);

    ajax::success($ledCreated . ' objet(s) créé(s) avec succès');
  }

  throw new Exception(__('Aucune méthode correspondante à', __FILE__) . ' : ' . init('action'));
  /*     * *********Catch exeption*************** */
} catch (Exception $e) {
  ajax::error(displayException($e), $e->getCode());
}
