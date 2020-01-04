<?php
class channel_group {
	
	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['channel_group'];
		foreach($clients as $client){
			if($client['cid'] == 7094){
				foreach(groupsIn($client, array_values($cfg['channels'])) as $gr)
					$ts->serverGroupDeleteClient($gr, $client['client_database_id']);
				$ts->clientKick($client['clid'], "channel");
				continue;
			}
			if(!array_key_exists($client['cid'], $cfg['channels']))
				continue;
			if(isIn($client, $group = $cfg['channels'][$client['cid']]))
				$ts->serverGroupDeleteClient($group, $client['client_database_id']);
			else
				$ts->serverGroupAddClient($group, $client['client_database_id']);
			$ts->clientKick($client['clid'], "channel");
		}

	}
}

?>