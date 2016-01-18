<?php
# author: mengxuan
# time； 2016/1/17

echo '=============================================================<br/>';
echo '我是测试自动的加载的demo！<br/>';
echo '该模块主要是实现Phalcon的自动加载(事件管理除外)！<br/>';
echo '=============================================================<br/>';

require('Loader.php');

$loader = new Loader();
$loader->registerClasses(array(
	'Test' => 'Test.php',
	'Base\\Hello' => 'base/BaseHello.php'
));

$loader->registerNamespaces(array(
	'Ns\\Base' => 'base/'
));

$loader->registerPrefixes(array(
	'Pre_Base' => 'base/'
));

$loader->registerDirs(array(
	'base/'
));

$loader->register();

$test = new Test();
$hello = new Base\Hello();

$ns = new Ns\Base\Ns();
$sayhello = new Ns\Base\More\SayHello();

$pre = new Pre_Base_Prefix();
$sayPre = new Pre_Base_More_SayPrefix();// 文件路径不区分大小写

$dir = new HelloDir();
$sayDir = new More\SayDir();
$sayDirs = new More_SayDirs();