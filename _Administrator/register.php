<?php
class register {
	
	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['register'];
			
		foreach($clients as $client){
			if($client['client_type'] != 0 || isIn($client, $cfg['ignored']))
				continue;
				
			$time = round(abs(time() - $client['client_created']) / 60,2);
			if($time > $cfg['time']){
				$ts->serverGroupAddClient($cfg['group'], $client['client_database_id']);
			}
		}
	}
}


?>