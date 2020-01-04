<?php
class default_channel {
	private static $joined;
	
	public static function load($ts){
		$clients = $ts->clientList('-away -voice -times -groups -ip -uid -info');
		self::$joined = (array)array_map(function($cl){return $cl['clid'];}, $clients);
	}

	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['default_channel'];
	
		
		$new = array_map(function($cl){return $cl['clid'];}, $clients);
		$diff = array_diff($new, self::$joined);
		foreach($diff as $clid) {
			$client = $ts->clientInfo($clid);
			if(!array_key_exists('client_nickname', $client) || $client['cid'] != $cfg['default_channel'])
				continue;

			$move = false;
			$in = groupsIn($client, array_keys($cfg['groups']));
            foreach ($in as $gr) {
				$ts->clientMove($clid, $cfg['groups'][$gr]);
				$move = true;
			}
			
			if(!$move){
				$test = $ts->channelGroupClientList(NULL, $client['client_database_id'], $cfg['aid']);	
				if(!empty($test)){
					$ts->clientMove($clid, $test[0]['cid']);
				}
			}
		}
		self::$joined = $new;
	}
}
?>