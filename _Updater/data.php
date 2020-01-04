<?php
class data {

	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['data'];
		
		$cache = array("server" => $server, "clients" => array(), "admins" => 0);
		foreach($clients as $client){
			if($client['client_type'] == 1)
				continue;
			else if(isIn($client, $cfg['admins']))
				$cache['admins'] += 1;
			$cache['clients'][$client['connection_client_ip']][] = $client;
		}
	
		@file_put_contents($cfg['cache_file'], json_encode($cache, JSON_HEX_AMP));
		@file_put_contents($cfg['data_file'], ($server['virtualserver_clientsonline'] - $server['virtualserver_queryclientsonline']));
	}
}
?>