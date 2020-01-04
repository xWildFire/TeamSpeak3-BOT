<?php
$start = microtime(true);

set_time_limit(0);
ini_set('memory_limit', '-1');
ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'UTF-8');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Warsaw');

clearstatcache();
require('inc/functions.php');
require('inc/Thread.class.php');
require('inc/Instance.class.php');
update();

$config = loadConfig();
if(json_last_error() != 0){
	error('Config: '.json_last_error_msg());
	return;
}

$instances = array();
foreach($config as $id => $val){
	if(@$val['enabled'] === false)
		continue;
	$instance = new Instance($id, $val);
	if(($cc = $instance->connect()) !== true){
		foreach($cc['data'] as $error){
			if(strlen($error) < 2)
				continue;
			error(str_replace("\n", '', $error));
		}
	}else{
		$instances[$id] = $instance;
	}
}
foreach($instances as $id => $instance){
	$instance->setInstances($instances);
	$instance->start();
}
if(count($instances) < 1){
	error('No instance can connect to server. Stopping...');
	return;
}
clearstatcache();
if(file_exists($restart = 'cache/'.md5('RESTART'))) unlink($restart); else
info('Started in '.round((microtime(true)-$start)*1000).'ms. ('.count($instances).' instances)');
foreach($instances as $instance){
	$instance->join();
	$instance->quit();
}
unset($instances, $config);
clearstatcache();
if(file_exists($restart))
	@require('RESTART');
?>