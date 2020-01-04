<?php
class welcome {
	private static $joined;
	
	public static function load($ts){
		$clients = $ts->clientList('-away -voice -times -groups -ip -uid -info');
		self::$joined = (array)array_map(function($cl){return $cl['clid'];}, $clients);
	}

	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['welcome'];
		$msg = file_get_contents('config/messages/welcome.txt');
		$msg = strtr($msg, array(
			'{SERVER_NAME}' => $server['virtualserver_name'],
			'{ONLINE}'  => $server['virtualserver_clientsonline'],
			'{MAX}' => $server['virtualserver_maxclients'])
		);
		
	
		$new = array_map(function($cl){return $cl['clid'];}, $clients);
		$diff = array_diff($new, self::$joined);
		foreach($diff as $clid) {
			$client = $ts->clientInfo($clid);
			if(!array_key_exists('client_nickname', $client))
				continue;
			
			$cmsg = strtr($msg, array(
				'{NICK}' => $client['client_nickname'],
				'{CONNECTIONS}' => $client['client_totalconnections'],
				'{CLIENT_FIRST_CONNECTED}' => date("d/m/Y", $client['client_created']),
				'{CLIENT_LAST_CONNECTED}' => date("d/m/Y", $client['client_lastconnected'])
			));
			
			if($cfg['linebyline']){
				foreach(explode("\n", $cmsg) as $mm){
					$ts->sendMessage(1, $clid, $mm);
				}
			}else{
				$ts->sendMessage(1, $clid, $cmsg);
			}
		}
		self::$joined = $new;
	}
}
?>