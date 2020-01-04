<?php
class no_record {
	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['no_record'];
		foreach($clients as $client){
			if(!$client['client_is_recording'])
				continue;
			
			if(($cfg['global'] && !in_array($client['cid'], $cfg['channels']) && !isIn($client, $cfg['groups']))
				|| (!$cfg['global'] && (in_array($client['cid'], $cfg['channels']) || isIn($client, $cfg['groups'])))){
				$ts->clientKick($client['clid'], 'server', "Nagrywanie jest zabronione.");
			}
		}
	}
}
?>