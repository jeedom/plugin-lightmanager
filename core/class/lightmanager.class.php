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
    $lightmanager = self::byId($_options['lightmanager_id']);
    if(!is_object($lightmanager)){
      return;
    }
    $stateHandling = $lightmanager->getCmd(null, 'stateHandling');
    if($stateHandling->execCmd() == 0){
      return;
    }
    $lightState = $lightmanager->getLightState();
    if($lightState != $lightmanager->getCache('lastLightOrder',$lightState) && $lightmanager->getConfiguration('auto_walkout') == 1){
      $stateHandling->event(0);
      return;
    }
    if($lightState){
      $crons = cron::searchClassAndFunction('lightmanager', 'autoLightOff', '"lightmanager_id":"' . $lightmanager->getId());
      if (is_array($crons)) {
        foreach ($crons as $cron) {
          if ($cron->getState() != 'run') {
            $cron->remove();
          }
        }
      }
      $cron = new cron();
      $cron->setClass('lightmanager');
      $cron->setFunction('autoLightOff');
      $cron->setOption(array('lightmanager_id' => intval($lightmanager->getId())));
      $cron->setLastRun(date('Y-m-d H:i:s'));
      $cron->setOnce(1);
      $cron->setSchedule(cron::convertDateToCron(strtotime('now') + 60 * $lightmanager->getConfiguration('delay::max_on')));
      $cron->save();
    }
  }
  
  public static function mainMotionChange($_options){
    $lightmanager = self::byId($_options['lightmanager_id']);
    if(!is_object($lightmanager)){
      return;
    }
    $lightmanager->handleStateLight();
  }
  
  public static function autoMotionLightOff($_options){
    $lightmanager = self::byId($_options['lightmanager_id']);
    if(!is_object($lightmanager)){
      return;
    }
    $stateHandling = $lightmanager->getCmd(null, 'stateHandling');
    if($stateHandling->execCmd() == 0){
      return;
    }
    $lightmanager->lightOff();
  }
  
  public static function autoLightOff($_options){
    $lightmanager = self::byId($_options['lightmanager_id']);
    if(!is_object($lightmanager)){
      return;
    }
    $stateHandling = $lightmanager->getCmd(null, 'stateHandling');
    if($stateHandling->execCmd() == 0){
      return;
    }
    $lightmanager->lightOff();
  }
  
  public static function mainLuminosityChange($_options){
    $lightmanager = self::byId($_options['lightmanager_id']);
    if(!is_object($lightmanager)){
      return;
    }
  }
  
  public static function mainHandleChange($_options){
    $lightmanager = self::byId($_options['lightmanager_id']);
    if(!is_object($lightmanager)){
      return;
    }
    $crons = cron::searchClassAndFunction('lightmanager', 'autoLightOff', '"lightmanager_id":"' . $lightmanager->getId());
    if (is_array($crons)) {
      foreach ($crons as $cron) {
        if ($cron->getState() != 'run') {
          $cron->remove();
        }
      }
    }
    $stateHandling = $lightmanager->getCmd(null, 'stateHandling');
    if($stateHandling->execCmd() == 0 && $lightmanager->getConfiguration('delay::regain_control') > 0){
      $cron = new cron();
      $cron->setClass('lightmanager');
      $cron->setFunction('resumeHandling');
      $cron->setOption(array('lightmanager_id' => intval($lightmanager->getId())));
      $cron->setLastRun(date('Y-m-d H:i:s'));
      $cron->setOnce(1);
      $cron->setSchedule(cron::convertDateToCron(strtotime('now') + 60 * $lightmanager->getConfiguration('delay::regain_control')));
      $cron->save();
    }
  }
  
  public function resumeHandling($_options){
    $lightmanager = self::byId($_options['lightmanager_id']);
    if(!is_object($lightmanager)){
      return;
    }
    $lightmanager->handleStateLight();
  }
  
  
  /*     * *********************Méthodes d'instance************************* */
  
  public function handleStateLight(){
    $stateHandling = $this->getCmd(null, 'stateHandling');
    if($stateHandling->execCmd() == 0){
      return;
    }
    $lightState = $this->getLightState();
    if($lightState != $this->getCache('lastLightOrder',$lightState) && $this->getConfiguration('auto_walkout') == 1){
      $stateHandling->event(0);
      return;
    }
    if($stateHandling->execCmd() == 0){
      return;
    }
    $motionState = $this->getMotionState();
    if($motionState == $this->getCache('lastMotionOrder',$motionState)){
      return;
    }
    $this->setCache('lastMotionOrder',$motionState)
    $crons = cron::searchClassAndFunction('lightmanager', 'autoMotionLightOff', '"lightmanager_id":"' . $this->getId());
    if (is_array($crons)) {
      foreach ($crons as $cron) {
        if ($cron->getState() != 'run') {
          $cron->remove();
        }
      }
    }
    $luminosityState = $this->getLuminosityState();
    if($motionState){
      if(!$luminosityState){
        return;
      }
      $this->lightOn();
    }else{
      if($this->getConfiguration('delay::off_no_motion') <= 0){
        $this->lightOff();
      }else{
        $cron = new cron();
        $cron->setClass('lightmanager');
        $cron->setFunction('autoMotionLightOff');
        $cron->setOption(array('lightmanager_id' => intval($this->getId())));
        $cron->setLastRun(date('Y-m-d H:i:s'));
        $cron->setOnce(1);
        $cron->setSchedule(cron::convertDateToCron(strtotime('now') + 60 * $this->getConfiguration('delay::off_no_motion')));
        $cron->save();
      }
    }
  }
  
  public function lightOn(){
    $lights = $this->getConfiguration('lights','');
    if($lights != '' ){
      foreach ($lights as $light) {
        try {
          $cmd = cmd::byId($light['cmdOn']);
          if(!is_object($cmd)){
            continue;
          }
          $cmd->execCmd($light['options']);
        } catch (\Exception $e) {
          
        }
      }
    }
    $lightmanager->setCache('lastLightOrder',1);
  }
  
  public function lightOff(){
    $lights = $this->getConfiguration('lights','');
    if($lights != '' ){
      foreach ($lights as $light) {
        try {
          $cmd = cmd::byId($light['cmdOff']);
          if(!is_object($cmd)){
            continue;
          }
          $cmd->execCmd();
        } catch (\Exception $e) {
          
        }
      }
    }
    $lightmanager->setCache('lastLightOrder',0);
  }
  
  public function getMotionState(){
    $motions = $this->getConfiguration('motions','');
    if($motions != '' ){
      foreach ($motions as $motion) {
        if(cmd::cmdToValue($motion['cmdMotion']) == 1){
          return true;
        }
      }
    }
    return false;
  }
  
  public function getLuminosityState(){
    $luminositys = $this->getConfiguration('luminositys','');
    if($luminositys != '' ){
      foreach ($luminositys as $luminosity) {
        if(cmd::cmdToValue($luminosity['cmdLuminosity']) < $luminosity['threshold']){
          return true;
        }
      }
    }
    return false;
  }
  
  public function getLightState(){
    $lights = $this->getConfiguration('lights','');
    if($lights != '' ){
      foreach ($lights as $light) {
        if(cmd::cmdToValue($light['cmdState'])){
          return true;
        }
      }
    }
    return false;
  }
  
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
    
    $cmd = $this->getCmd(null, 'on');
    if (!is_object($cmd)) {
      $cmd = new lightmanagerCmd();
      $cmd->setLogicalId('on');
      $cmd->setName(__('On', __FILE__));
    }
    $cmd->setType('action');
    $cmd->setSubType('other');
    $cmd->setEqLogic_id($this->getId());
    $cmd->save();
    
    
    $cmd = $this->getCmd(null, 'off');
    if (!is_object($cmd)) {
      $cmd = new lightmanagerCmd();
      $cmd->setLogicalId('off');
      $cmd->setName(__('Off', __FILE__));
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
        preg_match_all("/#([0-9]*)#/", $light['cmdState'], $matches);
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
    $lightmanager = $this->getEqLogic();
    if($this->getLogicalId() == 'on'){
      $lightmanager->lightOn();
    }else if($this->getLogicalId() == 'off'){
      $lightmanager->lightOff();
    }else if($this->getLogicalId() == 'resumeHandling'){
      $lightmanager->getCmd(null, 'stateHandling')->event(1);
    }else if($this->getLogicalId() == 'suspendHandling'){
      $lightmanager->getCmd(null, 'stateHandling')->event(0);
    }
  }
  
  /*     * **********************Getteur Setteur*************************** */
}
