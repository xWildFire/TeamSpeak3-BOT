<?php
class swap {
	 public static function command($ts, $client, $args, $clients, $channels, $server, $instances){
		if(count($args) < 2 || !array_key_exists($args[0], $channels) || !array_key_exists($args[1], $channels)){
			$ts->sendMessage(1, $invokerid, "Usage: !swap <cid> <cid>");
			return;
		}
		
		$val1 = array();
		$val1['channel_order'] = $channels[$args[0]]['channel_order'];
		$val1['cpid'] = $channels[$args[0]]['pid'];
		$val1['channel_name'] = $channels[$args[1]]['channel_name'];
		$val1['channel_name'] = str_replace((integer)$channels[$args[1]]['channel_name'], (integer)$channels[$args[0]]['channel_name'], $val1['channel_name']);
		
		$val2 = array();
		$val2['channel_order'] = $channels[$args[1]]['channel_order'];
		$val2['cpid'] = $channels[$args[1]]['pid'];
		$val2['channel_name'] = $channels[$args[0]]['channel_name'];
		$val2['channel_name'] = str_replace((integer)$channels[$args[0]]['channel_name'], (integer)$channels[$args[1]]['channel_name'], $val2['channel_name']);

		$ts->channelEdit($args[0], $val2);
		$ts->channelEdit($args[1], $val1);
		
		$ts->sendMessage(1, $invokerid, "Channels swapped.");
	}
}

?>