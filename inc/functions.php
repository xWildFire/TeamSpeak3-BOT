<?php
/*************** CONFIG ***************/
function saveConfig($config){
	file_put_contents('config/config.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_HEX_AMP));
}
function loadConfig(){
	return json_decode(file_get_contents('config/config.json'), true);
}
/*************** CONFIG ***************/
/*************** BACKUP ***************/
function getFiles($directory = ''){
	$files = array();
	$scan = scandir(empty($directory) ? "./" : $directory);
	foreach($scan as $entry){
		if($entry == '.' || $entry == '..') continue;
		$path = $directory.$entry;
		if(is_dir($path)) {
			$files = array_merge($files, getFiles("$path/"));
		}else{
			$files[] = $path;
		}
	}
	return $files;
}
function backup(){
	$zip = new ZipArchive();
	if($zip->open("backup.zip", ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) == true){
		foreach(getFiles() as $path){
			$zip->addFile($path, $path);
		}
		$zip->close();
	}
	return file_exists("backup.zip") ?: "Error when creating backup backup.";
}
function backup_restore(){
	if(!file_exists('backup.zip')) return false;
	$zip = new ZipArchive();
	if($zip->open('backup.zip') != true) return false;
	$ex = $zip->extractTo("./");
	$zip->close();
	return $ex;
}
/*************** BACKUP ***************/
/*************** UPDATE ***************/
function update($instances = null, $ts = null){
	clearstatcache();
	$current = (float)file_get_contents('cache/version.txt');
	$versions = @file_get_contents("http://wildfire.tk/wBOT/version.php?v=$current");
	if(empty($versions) || empty($versions = array_filter(explode("\n", $versions), function($x)use($current){return $x > $current;})))
		return "You have current version.($current)";
	sort($versions);
	foreach($versions as $ver){
		if(!backup()){
			$error = "Error when creating backup.";
		}else if(!file_put_contents("update.zip", fopen("http://wildfire.tk/wBOT/update-$ver.zip", 'r'))){
			$error = "Error when downloading/saving update.";
		}else{
			$zip = new ZipArchive();
			$old = umask(0);
			if($zip->open("update.zip") != true){
				$error = "Error when opening zipped update.";
			}else if(!$zip->extractTo("./")){
				$error = "Error when extracting update.";
				if($restored = backup_restore())
					$error .= " Backup restored.";
			}else{
				if(file_exists('execute.php')){
					include('execute.php');
					unlink('execute.php');
				}
				file_put_contents('cache/version.txt', $ver);
				clearstatcache();
			}
			@$zip->close();
			umask($old);
		}
		if(!isset($restored) || $restored)
			@unlink('backup.zip');
		@unlink("update.zip");
		if(isset($error))
			return $error;
	}
	return "Successful updated to version $ver.";
}
/*************** UPDATE ***************/
/*************** LOGGER ***************/
function errorHandler($errno, $errstr, $errfile = null, $errline = null){
    if(($errno & error_reporting()) <= 0)
		return false;
	
	$dt = date('H:i:s');
	$type = 'UNKNOWN';
    switch($errno){
        case E_STRICT:
		case E_PARSE:
            $type = "CODE";
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
			$type = "INFO";
			break;
        case E_WARNING:
        case E_USER_WARNING:
            $type = "WARN";
            break;
        case E_ERROR:
        case E_USER_ERROR:
			$type = "ERROR";
			break;
    }
	$errmsg = "[$dt][$type] $errstr\n";
	if($errfile && $errline)
		$errmsg .= "     in $errfile on line $errline\n";

	echo $errmsg;
	error_log($errmsg, 3, 'logs.log');
	return true;
}
set_error_handler("errorHandler");
function error($message){ 
	errorHandler(E_USER_ERROR, $message);
}
function info($message){
	errorHandler(E_USER_NOTICE, $message);
}
$restart = 'cache/'.md5('RESTART');
register_shutdown_function(function(){
	$error = error_get_last();
	if(strpos($error['message'], 'RESTART') != false){
		pcntl_exec('/usr/bin/php', array('bot.php'));
	}else if(in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))){
		errorHandler($error['type'], "(SHUTDOWN)".$error['message'], $error['file'], $error['line']);
		update();
	}
});
/*************** LOGGER ***************/
function isIn($client, $req){
	return !empty(groupsIn($client, $req));
}
function groupsIn($client, $req){
	if(!is_array($req))
		$req = explode(',', $req);
	return array_intersect(explode(',', $client['client_servergroups']), $req);
}
function startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}
function secondsToTime($seconds) {
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime("@$seconds");
	return $dtF->diff($dtT)->format(' %a dni %h godz %i min');
}
?>