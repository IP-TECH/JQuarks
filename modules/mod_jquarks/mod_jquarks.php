<?php

defined('_JEXEC') or die('Restricted Access');


require_once(dirname(__FILE__) . DS . 'helper.php');

$publicQuizzes = ModJQuarksHelper::getPublicQuizzes($params); // get the publicQuizzes
$userQuizzes = ModJQuarksHelper::getUserQuizzes($params);  // get the privateQuizzes		 

// include the template for display
require(JModuleHelper::getLayoutPath('mod_jquarks'));
