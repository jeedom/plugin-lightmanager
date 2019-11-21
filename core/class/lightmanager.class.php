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
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class lightmanager extends eqLogic {
  /*     * *************************Attributs****************************** */
  
  
  /*     * ***********************Methode static*************************** */
  
  public static function mainLightChange($_options){
    
  }
  
  public static function mainMotionChange($_options){
    
  }
  
  public static function mainLuminosityChange($_options){
    
  }
  
  
  /*     * *********************Méthodes d'instance************************* */
  
  
  public function postSave() {
    $cmd = $this->getCmd(null, 'stateHandling');
    if (!is_object($cmd)) {
      $cmd = new lightmanagerCmd();
      $cmd->setLogicalId('stateHandling');
      $cmd->setName(__('Etat gestion', __FILE__));
    }
    $cmd->setType('info');
    $cmd->setSubType('binary');
    $cmd->setEqLogic_id($this->getId());
    $cmd->save();
    
    $cmd = $this->getCmd(null, 'suspendHandling');
    if (!is_object($cmd)) {
      $cmd = new lightmanagerCmd();
      $cmd->setLogicalId('suspendHandling');
      $cmd->setName(__('Suspendre', __FILE__));
    }
    $cmd->setType('action');
    $cmd->setSubType('other');
    $cmd->setEqLogic_id($this->getId());
    $cmd->save();
    
    $cmd = $this->getCmd(null, 'resumeHandling');
    if (!is_object($cmd)) {
      $cmd = new lightmanagerCmd();
      $cmd->setLogicalId('resumeHandling');
      $cmd->setName(__('Reprendre', __FILE__));
    }
    $cmd->setType('action');
    $cmd->setSubType('other');
    $cmd->setEqLogic_id($this->getId());
    $cmd->save();
    
    $cmd = $this->getCmd(null, 'refresh');
    if (!is_object($cmd)) {
      $cmd = new lightmanagerCmd();
      $cmd->setLogicalId('refresh');
      $cmd->setName(__('Rafraichir', __FILE__));
    }
    $cmd->setType('action');
    $cmd->setSubType('other');
    $cmd->setEqLogic_id($this->getId());
    $cmd->save();
    
    $lights = $this->getConfiguration('lights','');
    if($lights != '' ){
      $listener = listener::byClassAndFunction('lightmanager', 'mainLightChange', array('lightmanager_id' => intval($this->getId())));
      if (!is_object($listener)) {
        $listener = new listener();
      }
      $listener->setClass('lightmanager');
      $listener->setFunction('mainLightChange');
      $listener->setOption(array('lightmanager_id' => intval($this->getId())));
      $listener->emptyEvent();
      $nblistener = 0;
      foreach ($lights as $light) {
        preg_match_all("/#([0-9]*)#/", $light['cmdOn'], $matches);
        foreach ($matches[1] as $cmd_id) {
          $nblistener += 1;
          $listener->addEvent($cmd_id);
        }
      }
      if ($nblistener > 0) {
        $listener->save();
      }
    }else {
      $listener = listener::byClassAndFunction('lightmanager', 'mainLightChange', array('lightmanager_id' => intval($this->getId())));
      if (is_object($listener)) {
        $listener->remove();
      }
    }
    
    $motions = $this->getConfiguration('motions','');
    if($motions != '' ){
      $listener = listener::byClassAndFunction('lightmanager', 'mainMotionChange', array('lightmanager_id' => intval($this->getId())));
      if (!is_object($listener)) {
        $listener = new listener();
      }
      $listener->setClass('lightmanager');
      $listener->setFunction('mainMotionChange');
      $listener->setOption(array('lightmanager_id' => intval($this->getId())));
      $listener->emptyEvent();
      $nblistener = 0;
      foreach ($motions as $motion) {
        preg_match_all("/#([0-9]*)#/", $motion['cmdMotion'], $matches);
        foreach ($matches[1] as $cmd_id) {
          $nblistener += 1;
          $listener->addEvent($cmd_id);
        }
      }
      if ($nblistener > 0) {
        $listener->save();
      }
    }else {
      $listener = listener::byClassAndFunction('lightmanager', 'mainMotionChange', array('lightmanager_id' => intval($this->getId())));
      if (is_object($listener)) {
        $listener->remove();
      }
    }
    
    $luminositys = $this->getConfiguration('luminositys','');
    if($luminositys != '' ){
      $listener = listener::byClassAndFunction('lightmanager', 'mainLuminosityChange', array('lightmanager_id' => intval($this->getId())));
      if (!is_object($listener)) {
        $listener = new listener();
      }
      $listener->setClass('lightmanager');
      $listener->setFunction('mainLuminosityChange');
      $listener->setOption(array('lightmanager_id' => intval($this->getId())));
      $listener->emptyEvent();
      $nblistener = 0;
      foreach ($luminositys as $luminosity) {
        preg_match_all("/#([0-9]*)#/", $luminosity['cmdLuminosity'], $matches);
        foreach ($matches[1] as $cmd_id) {
          $nblistener += 1;
          $listener->addEvent($cmd_id);
        }
      }
      if ($nblistener > 0) {
        $listener->save();
      }
    }else {
      $listener = listener::byClassAndFunction('lightmanager', 'mainLuminosityChange', array('lightmanager_id' => intval($this->getId())));
      if (is_object($listener)) {
        $listener->remove();
      }
    }
  }
  
  /*     * **********************Getteur Setteur*************************** */
}

class lightmanagerCmd extends cmd {
  /*     * *************************Attributs****************************** */
  
  
  /*     * ***********************Methode static*************************** */
  
  
  /*     * *********************Methode d'instance************************* */
  
  public function execute($_options = array()) {
    
  }
  
  /*     * **********************Getteur Setteur*************************** */
}
