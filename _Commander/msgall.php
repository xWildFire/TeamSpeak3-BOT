<?php
class msgall {
	public static function command($ts, $client, $args, $clients, $channels, $server, $instances){
		if(count($args) < 1){
			$ts->sendMessage(1, $client['clid'], "Usage: !msgall <message>");
			return;
		}
		$msg = implode(" ", $args);

		$c = 0;
		foreach($clients as $tclient){
			$ts->sendMessage(1, $tclient['clid'], $msg);
			++$c;
		}
		$ts->sendMessage(1, $client['clid'], "Message sent to ".$c." users.");
	}
}
?>