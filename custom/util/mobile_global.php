<?php
require_once(dirname(__FILE__).'/../util/global.php');
require_once(dirname(__FILE__).'/../../app/Mage.php');
Mage::app();
umask(0);
ob_start();
session_start();
Mage::getSingleton("core/session", array("name" => "frontend"));