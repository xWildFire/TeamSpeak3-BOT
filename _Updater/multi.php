<?php
class multi {
	private static $record;
	
	public static function load(){
		self::$record = file_exists("cache/record.txt") ? file_get_contents("cache/record.txt") : 0;
	}

	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['multi'];

		$online = $server['virtualserver_clientsonline'] - $server['virtualserver_queryclientsonline'];
		
		if($cfg['online']['enabled']){
			$max = $server['virtualserver_maxclients'];
			$name = str_replace('{ONLINE}', $online, $cfg['online']['name']);
			$name = str_replace('{MAX}', $max, $name);
			$ts->channelEdit($cfg['online']['cid'], array('channel_name' => $name));
		}
		
		if($cfg['hour']['enabled']){
			$name = str_replace('{TIME}', date('H:i'), $cfg['hour']['name']);
			$ts->channelEdit($cfg['hour']['cid'], array('channel_name' => $name));
		}
		
		if($cfg['free_channels']['enabled']){
			$name = str_replace('{COUNT}', count(array_filter($channels, function($val)use($cfg){return $val['pid'] == $cfg['free_channels']['pid'] && $val['channel_topic'] == "#FREE";})), $cfg['free_channels']['name']);
			$ts->channelEdit($cfg['free_channels']['cid'], array('channel_name' => $name));
		}
			
		if($cfg['record']['enabled']){
			if($online > self::$record){
				self::$record = $online;
				file_put_contents("cache/record.txt", $online);
			}
			
			$name = str_replace('{RECORD}', self::$record, $cfg['record']['name']);
			$ts->channelEdit($cfg['record']['cid'], array('channel_name' => $name));
		}
	}
}

?>