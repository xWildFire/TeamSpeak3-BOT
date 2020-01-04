<?php
class host {
	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['host'];
  
		$vars = array(
			'{CONNECTIONS}' => $server['virtualserver_client_connections'],
			'{NAME}' => $server['virtualserver_name'],
			'{MAX}' => $server['virtualserver_maxclients'],
			'{ONLINE}' => ($server['virtualserver_clientsonline'] - $server['virtualserver_queryclientsonline'])
		);
		$data = array('virtualserver_name' => strtr($cfg['name'], $vars));

		if($cfg['modal']){
			$data['virtualserver_hostmessage'] = strtr(file_get_contents('config/messages/host.txt', "r"), $vars);
			$data['virtualserver_hostmessage_mode'] = 2;
		}
		
		$ts->serverEdit($data);
	}
}
?>