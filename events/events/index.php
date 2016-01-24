<?php

require('../loader/Loader.php');
$loader = new Loader();
$loader->registerNamespaces(array(
	'Events' => getcwd()//dirname(__FILE__)
))->register();

$manager = new Events\Manager();
$a = function(){echo "A";};
$manager->attach('test:hello', $a);
$b = function(Events\Event $event){
	echo "B";
};
$manager->attach('test:hello', $b);

var_dump($manager->fire('test:hello', null));

var_dump($manager->getListeners('test:hello'));