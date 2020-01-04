<?php
class pokeall {
	public static function command($ts, $client, $args, $clients, $channels, $server, $instances){
		if(count($args) < 1){
			$ts->sendMessage(1, $client['clid'], "Usage: !pokeall <message>");
			return;
		}
		$msg = implode(" ", $args);
		
		$c = 0;
		foreach($clients as $tclient){
			if($tclient['client_type'] == 1)
				continue;
			$ts->clientPoke($tclient['clid'], $msg);
			++$c;
		}
		$ts->sendMessage(1, $client['clid'], "Poke sent to ".$c." users.");
	}
}
?>