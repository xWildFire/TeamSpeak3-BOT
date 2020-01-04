<?php
class move {
	public static function command($ts, $client, $args, $clients, $channels, $server, $instances){
		if(count($args) < 2 || !array_key_exists($args[0], $channels)){
			$ts->sendMessage(1, $client['clid'], "Usage: !move <cid> <groups>");
			return;
		}

		$groups = explode(',', $args[1]);
		foreach($clients as $mclient){
			if($mclient['client_type'] != 0 || !isIn($mclient, $groups))
				continue;
			$ts->clientMove($mclient['clid'], $args[0]);
		}
		
		$ts->sendMessage(1, $client['clid'], "Moved specified groups to channel: [b]".$channels[$args[0]]['channel_name']."[/b]");
	}
}

?>