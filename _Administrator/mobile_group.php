<?php
class mobile_group {
	
	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['mobile_group'];
		foreach($clients as $client){
			if(strpos($client['client_platform'], "Android") !== false){
				$ts->addClientServerGroup($client['client_database_id'], $cfg['android']);
			}else if(strpos($client['client_platform'], "iOS") !== false){
				$ts->addClientServerGroup($client['client_database_id'], $cfg['ios']);
			}
		}
	}
}
?>