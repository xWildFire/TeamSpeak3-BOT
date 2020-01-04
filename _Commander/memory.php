<?php
class memory {
	public static function command($ts, $client, $args, $clients, $channels, $server, $instances){
		$ts->sendMessage(1, $client['clid'], "Used memory: ".self::memoryUsage());
	}
	
	private static function memoryUsage(){
		$unit = array('b','kb','mb','gb','tb','pb');
		$size = memory_get_usage();
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}
}
?>