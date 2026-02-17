<?php
require_once __DIR__ . '/../../../../core/php/core.inc.php';

class ImactPluginCmd extends cmd {
    
    public function execute($_options = array()) {
        
        if ($this->getType() == 'action') {
            // Exécuter la commande request (la commande Zigbee)
            $requestId = $this->getConfiguration('request');
            if ($requestId) {
                $cmdRequest = cmd::byId($requestId);
                if ($cmdRequest) {
                    log::add('ImactPlugin', 'debug', 'Exécution commande Zigbee: ' . $cmdRequest->getName());
                    $cmdRequest->execCmd($_options);
                }
            }
            
            // Attendre que le Zigbee se mette à jour
            usleep(500000); // 0.5 seconde
            
            // Rafraîchir la commande info liée
            $cmdInfoId = $this->getValue();
            if ($cmdInfoId) {
                $cmdInfo = cmd::byId($cmdInfoId);
                if ($cmdInfo) {
                    log::add('ImactPlugin', 'debug', 'Rafraîchissement commande info: ' . $cmdInfo->getName());
                    $cmdInfo->execute(); // Force le recalcul
                    log::add('ImactPlugin', 'debug', 'Nouvelle valeur: ' . $cmdInfo->execCmd());
                }
            }
        }
        
        return true;
    }
}