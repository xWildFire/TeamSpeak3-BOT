<?php
class group_limit {
	
	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['group_limit'];

		foreach($clients as $client){
			foreach($cfg['groups'] as $groups => $max){
				if(count($in = groupsIn($client, explode(",", $groups))) <= $max)
					continue;

				$rem = count($in) - $max;
				foreach($in as $group){
					if(--$rem < 0)
						break;
					$ts->serverGroupDeleteClient($group, $client['client_database_id']);
				}
			}
		}
	}
}
?>