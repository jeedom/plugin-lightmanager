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

  public static function mainMotionChange($_options) {
    $lightmanager = self::byId($_options['lightmanager_id']);
    if (!is_object($lightmanager) || !$lightmanager->getIsEnable()) {
      return;
    }
    if (isset($_options['seconds']) && $_options['seconds'] > 0) {
      sleep($_options['seconds']);
    }
    log::add('lightmanager', 'debug', $lightmanager->getHumanName() . '[autoMotionLightOff] ' . json_encode($_options));
    $lightmanager->handleStateLight();
  }

  public static function autoMotionLightOff($_options) {
    $lightmanager = self::byId($_options['lightmanager_id']);
    if (!is_object($lightmanager) || !$lightmanager->getIsEnable()) {
      return;
    }
    if (isset($_options['seconds']) && $_options['seconds'] > 0) {
      sleep($_options['seconds']);
    }
    log::add('lightmanager', 'debug', $lightmanager->getHumanName() . ' autoMotionLightOff => ' . json_encode($_options));
    $stateHandling = $lightmanager->getCmd(null, 'stateHandling');
    if ($stateHandling->execCmd() == 0) {
      log::add('lightmanager', 'debug', $lightmanager->getHumanName() . '[autoMotionLightOff] handling disable, do nothing');
      return;
    }
    if ($lightmanager->getMotionState()) {
      log::add('lightmanager', 'debug', $lightmanager->getHumanName() . '[autoMotionLightOff] motion in progess do nothing');
      return;
    }
    $lightmanager->lightOff();
  }

  public static function autoLightOff($_options) {
    $lightmanager = self::byId($_options['lightmanager_id']);
    if (!is_object($lightmanager) || !$lightmanager->getIsEnable()) {
      return;
    }
    if (isset($_options['seconds']) && $_options['seconds'] > 0) {
      sleep($_options['seconds']);
    }
    log::add('lightmanager', 'debug', $lightmanager->getHumanName() . '[autoLightOff] autoLightOff => ' . json_encode($_options));
    $stateHandling = $lightmanager->getCmd(null, 'stateHandling');
    if ($stateHandling->execCmd() == 0) {
      log::add('lightmanager', 'debug', $lightmanager->getHumanName() . '[autoLightOff] handling disable, do nothing');
      return;
    }
    if ($lightmanager->getMotionState() && $lightmanager->getConfiguration('delay::off_no_motion') > 0) {
      log::add('lightmanager', 'debug', $lightmanager->getHumanName() . '[autoLightOff] motion in progess do nothing');
      log::add('lightmanager', 'debug', $lightmanager->getHumanName() . '[autoLightOff] Plan off light');
      $cron = new cron();
      $cron->setClass('lightmanager');
      $cron->setFunction('autoMotionLightOff');
      $cron->setOption(array('lightmanager_id' => intval($lightmanager->getId()), 'seconds' => date('s')));
      $cron->setLastRun(date('Y-m-d H:i:s'));
      $cron->setOnce(1);
      $cron->setSchedule(cron::convertDateToCron(strtotime('now') + 60 * $lightmanager->getConfiguration('delay::off_no_motion')));
      $cron->save();
      return;
    }
    $lightmanager->lightOff();
  }

  public static function mainHandleChange($_options) {
    $lightmanager = self::byId($_options['lightmanager_id']);
    if (!is_object($lightmanager) || !$lightmanager->getIsEnable()) {
      return;
    }
    if (isset($_options['seconds']) && $_options['seconds'] > 0) {
      sleep($_options['seconds']);
    }
    log::add('lightmanager', 'debug', $lightmanager->getHumanName() . '[mainHandleChange] ' . json_encode($_options));
    $crons = cron::searchClassAndFunction('lightmanager', 'autoLightOff', '"lightmanager_id":"' . $lightmanager->getId());
    if (is_array($crons)) {
      log::add('lightmanager', 'debug', $lightmanager->getHumanName() . '[handleStateLight] I need to remove previous plan cron, count ' . count($crons));
      foreach ($crons as $cron) {
        log::add('lightmanager', 'debug', $lightmanager->getHumanName() . '[handleStateLight] Check if cron need to be remove : ' . $cron->getId() . ' state : ' . $cron->getState());
        if ($cron->getState() != 'run') {
          $cron->remove();
        }
      }
    }
    $stateHandling = $lightmanager->getCmd(null, 'stateHandling');
    if ($stateHandling->execCmd() == 0 && $lightmanager->getConfiguration('delay::regain_control') > 0) {
      log::add('lightmanager', 'debug', $lightmanager->getHumanName() . '[mainHandleChange] plan resume handling');
      $cron = new cron();
      $cron->setClass('lightmanager');
      $cron->setFunction('resumeHandling');
      $cron->setOption(array('lightmanager_id' => intval($lightmanager->getId()), 'seconds' => date('s')));
      $cron->setLastRun(date('Y-m-d H:i:s'));
      $cron->setOnce(1);
      $cron->setSchedule(cron::convertDateToCron(strtotime('now') + 60 * $lightmanager->getConfiguration('delay::regain_control')));
      $cron->save();
    }
  }

  public static function resumeHandling($_options) {
    $lightmanager = self::byId($_options['lightmanager_id']);
    if (!is_object($lightmanager) || !$lightmanager->getIsEnable()) {
      return;
    }
    if (isset($_options['seconds']) && $_options['seconds'] > 0) {
      sleep($_options['seconds']);
    }
    log::add('lightmanager', 'debug', $lightmanager->getHumanName() . '[resumeHandling] ' . json_encode($_options));
    $lightmanager->getCmd(null, 'resumeHandling')->execCmd();
  }

  public static function cronDaily() {
    foreach (self::byType('lightmanager', true) as $lightmanager) {
      if ($lightmanager->getConfiguration('delay::regain_control') > 0) {
        $lightmanager->getCmd(null, 'resumeHandling')->execCmd();
      }
    }
  }

  /*     * *********************Méthodes d'instance************************* */

  public function handleStateLight() {
    log::add('lightmanager', 'debug', $this->getHumanName() . ' handleStateLight');
    $stateHandling = $this->getCmd(null, 'stateHandling');
    if ($stateHandling->execCmd() == 0) {
      log::add('lightmanager', 'debug', $this->getHumanName() . '[handleStateLight] Handling disable, do nothing');
      return;
    }
    $lightState = $this->getLightState();
    if ($lightState != $this->getCache('lastLightOrder', $lightState) && $this->getConfiguration('auto_walkout') == 1) {
      log::add('lightmanager', 'debug', $this->getHumanName() . '[handleStateLight] Light state no same that last order, do nothing');
      $stateHandling->event(0);
      return;
    }
    $motionState = $this->getMotionState();
    $this->setCache('lastMotionOrder', $motionState);
    $crons = cron::searchClassAndFunction('lightmanager', 'autoMotionLightOff', '"lightmanager_id":"' . $this->getId());
    if (is_array($crons)) {
      log::add('lightmanager', 'debug', $this->getHumanName() . '[handleStateLight] I need to remove previous plan cron, count ' . count($crons));
      foreach ($crons as $cron) {
        log::add('lightmanager', 'debug', $this->getHumanName() . '[handleStateLight] Check if cron need to be remove : ' . $cron->getId() . ' state : ' . $cron->getState());
        if ($cron->getState() != 'run') {
          $cron->remove();
        }
      }
    }
    if ($motionState) {
      log::add('lightmanager', 'debug', $this->getHumanName() . '[handleStateLight] Motion detected, check luminosity');
      $luminosityState = $this->getLuminosityState();
      if (!$luminosityState) {
        log::add('lightmanager', 'debug', $this->getHumanName() . '[handleStateLight] Luminosity not ok, do nothing');
        return;
      }
      $this->lightOn();
    } else {
      log::add('lightmanager', 'debug', $this->getHumanName() . '[handleStateLight] No motion check off light');
      if ($this->getConfiguration('delay::off_no_motion') <= 0) {
        $this->lightOff();
      } else {
        if (!$lightState) {
          log::add('lightmanager', 'debug', $this->getHumanName() . '[handleStateLight] Light is off nothing to do');
          return;
        }
        log::add('lightmanager', 'debug', $this->getHumanName() . '[handleStateLight] Light is on, plan off light');
        $cron = new cron();
        $cron->setClass('lightmanager');
        $cron->setFunction('autoMotionLightOff');
        $cron->setOption(array('lightmanager_id' => intval($this->getId()), 'seconds' => date('s')));
        $cron->setLastRun(date('Y-m-d H:i:s'));
        $cron->setOnce(1);
        $cron->setSchedule(cron::convertDateToCron(strtotime('now') + 60 * $this->getConfiguration('delay::off_no_motion')));
        $cron->save();
      }
    }
  }

  public function lightOn() {
    log::add('lightmanager', 'debug', $this->getHumanName() . '[lightOn] Turn on light');
    $lights = $this->getConfiguration('lights', '');
    if ($lights != '') {
      foreach ($lights as &$light) {
        try {
          $cmd = cmd::byId(str_replace('#', '', $light['cmdOn']));
          if (!is_object($cmd)) {
            continue;
          }
          if (!isset($light['options'])) {
            $light['options'] = array();
          }
          $cmd->execCmd($light['options']);
        } catch (\Exception $e) {
        }
      }
    }
    $this->setCache('lastLightOrder', 1);
  }

  public function lightOff() {
    log::add('lightmanager', 'debug', $this->getHumanName() . '[lightOff] Turn off light');
    $lights = $this->getConfiguration('lights', '');
    if ($lights != '') {
      foreach ($lights as $light) {
        try {
          $cmd = cmd::byId(str_replace('#', '', $light['cmdOff']));
          if (!is_object($cmd)) {
            continue;
          }
          if (!isset($light['options'])) {
            $light['options'] = array();
          }
          $cmd->execCmd($light['options']);
        } catch (\Exception $e) {
        }
      }
    }
    $this->setCache('lastLightOrder', 0);
  }

  public function getMotionState() {
    $motions = $this->getConfiguration('motions', '');
    if ($motions != '') {
      foreach ($motions as $motion) {
        if ($motion['enable'] != 1) {
          continue;
        }
        $value = jeedom::evaluateExpression($motion['cmdMotion']);
        log::add('lightmanager', 'debug', $this->getHumanName() . ' ' . $motion['cmdMotion'] . ' result : ' . $value);
        if ($value == 1) {
          log::add('lightmanager', 'debug', $this->getHumanName() . '[getMotionState] Motion check => 1');
          return true;
        }
      }
    }
    log::add('lightmanager', 'debug', $this->getHumanName() . '[getMotionState] Motion check => 0');
    return false;
  }

  public function getLuminosityState() {
    $luminositys = $this->getConfiguration('luminositys', '');
    if ($luminositys != '') {
      foreach ($luminositys as $luminosity) {
        if ($luminosity['enable'] != 1) {
          continue;
        }
        if (!isset($luminosity['min_last_min']) || $luminosity['min_last_min'] == 0) {
          $value = cmd::cmdToValue($luminosity['cmdLuminosity']);
        } else {
          $cmd = cmd::byId(str_replace('#', '', $luminosity['cmdLuminosity']));
          if (!is_object($cmd)) {
            continue;
          }
          $stats = $cmd->getStatistique(date('Y-m-d H:i:s', strtotime('now -' . $luminosity['min_last_min'] . ' min')), date('Y-m-d H:i:s', strtotime('now')));
          $value = $stats['min'];
        }
        log::add('lightmanager', 'debug', $this->getHumanName() . '[getLuminosityState] Luminosity ' . $value . ' threshold : ' . $luminosity['threshold']);
        if ($value < $luminosity['threshold']) {
          log::add('lightmanager', 'debug', $this->getHumanName() . '[getLuminosityState] Luminosity check => 1');
          return true;
        }
      }
    }
    log::add('lightmanager', 'debug', $this->getHumanName() . '[getLuminosityState] Luminosity check => 0');
    return false;
  }

  public function getLightState() {
    $lights = $this->getConfiguration('lights', '');
    if ($lights != '') {
      foreach ($lights as $light) {
        if ($light['enable'] != 1) {
          continue;
        }
        if (cmd::cmdToValue($light['cmdState'])) {
          log::add('lightmanager', 'debug', $this->getHumanName() . '[getLightState] Light state check => 1');
          return true;
        }
      }
    }
    log::add('lightmanager', 'debug', $this->getHumanName() . '[getLightState] Light state check => 0');
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

    $motions = $this->getConfiguration('motions', '');
    if ($motions != '') {
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
    } else {
      $listener = listener::byClassAndFunction('lightmanager', 'mainMotionChange', array('lightmanager_id' => intval($this->getId())));
      if (is_object($listener)) {
        $listener->remove();
      }
    }

    $listener = listener::byClassAndFunction('lightmanager', 'mainHandleChange', array('lightmanager_id' => intval($this->getId())));
    if (!is_object($listener)) {
      $listener = new listener();
    }
    $listener->setClass('lightmanager');
    $listener->setFunction('mainHandleChange');
    $listener->setOption(array('lightmanager_id' => intval($this->getId())));
    $listener->emptyEvent();
    $listener->addEvent($this->getCmd(null, 'stateHandling')->getId());
    $listener->save();
  }

  public function preRemove() {
    $listener = listener::byClassAndFunction('lightmanager', 'mainMotionChange', array('lightmanager_id' => intval($this->getId())));
    if (is_object($listener)) {
      $listener->remove();
    }
    $listener = listener::byClassAndFunction('lightmanager', 'mainHandleChange', array('lightmanager_id' => intval($this->getId())));
    if (is_object($listener)) {
      $listener->remove();
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
    if ($this->getLogicalId() == 'on') {
      $lightmanager->lightOn();
    } else if ($this->getLogicalId() == 'off') {
      $lightmanager->lightOff();
    } else if ($this->getLogicalId() == 'resumeHandling') {
      $lightmanager->getCmd(null, 'stateHandling')->event(1);
      $lightmanager->setCache('lastLightOrder', $lightmanager->getLightState());
      $lightmanager->handleStateLight();
    } else if ($this->getLogicalId() == 'suspendHandling') {
      $lightmanager->getCmd(null, 'stateHandling')->event(0);
    }
  }

  /*     * **********************Getteur Setteur*************************** */
}
