<?php

// header("content-type:text/html; charset=utf-8");

// require('Manager.php');
// // require('Event.php');

$manager = new Manager();
// // $manager->enablePriorities(true);
// $as = function(Event $event){echo "Aasdasdas";$event->Stop();};
// $manager->attach('A:as', $as);
// $manager->attach('A:as', function(){echo "B";});
// var_dump($manager);
// var_dump($manager->_events['as']->count());

// $manager->detach('as', $as);
// $manager->detachAll('as');
// var_dump($manager);
// var_dump($manager->_events['as']->count());

// var_dump($manager->getListeners('as'));

// $manager->fireQueue($manager->_events['as']);
// var_dump($manager->fireQueue($manager->_events['as']));

// var_dump($manager->fire('A:as', null));

// class A{
// 	public function test(){
// 		echo "string";
// 	}
// }
// $asd = 'test';
// var_dump(call_user_func_array([new A, $asd], []));


