<?php
class afk_checker {
	private static $afk = array();
	
	public static function load($ts){
		if(file_exists("cache/afk.txt") && time()-filemtime("cache/afk.txt") < 60*60*4){
			self::$afk = file_get_contents("cache/afk.txt");
			self::$afk = (array)json_decode(self::$afk, true);
		}
	}

	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['afk_checker'];
		$time = $cfg['time']*60000;
		
		foreach(self::$afk as $clid => $cid){
			if(array_key_exists($clid, $clients)){
				$client = $clients[$clid];
				if($client['client_idle_time'] > $time || $client['client_away'])
					continue;
				else if($client['cid'] == $cfg['channel'])
					$ts->clientMove($clid, $cid);
			}
			$changed = true;
			unset(self::$afk[$clid]);
		}
		
		foreach ($clients as $client){
			if($client['client_type'] != 0 || array_key_exists($client['clid'], self::$afk) || isIn($client, $cfg['ignored']) 
				|| (($idle = $client['client_idle_time']) <= $time && (!$client['client_away'] || $idle <= 5000)))
				continue;
			$changed = true;
			self::$afk[$client['clid']] = $client['cid'];
			$ts->clientMove($client['clid'], $cfg['channel']);
		}
		if(isset($changed))
			file_put_contents("cache/afk.txt", json_encode(self::$afk));
	}
}
?>