<?php
class clear {
	public static function command($ts, $client, $args, $clients, $channels, $server, $instances){
	 	if(count($args) < 1 || !array_key_exists($args[0], $channels)){
			$ts->sendMessage(1, $client['clid'], "Usage: !clear <cid>");
			return;
		}
		$chs = array($args[0]);
		foreach($channels as $channel){
			if(!in_array($channel['pid'], $chs))
				continue;
			$chs[] = $channel['cid'];
		}
		$i = 0;
		foreach($chs as $cid){
			$groups = $ts->channelGroupClientList($cid);	

			if(!empty($groups)){
				foreach($groups as $gr){
					$ts->setClientChannelGroup($server['virtualserver_default_channel_group'], $cid, $gr['cldbid']);
					++$i;
				}
			}
		}

		$ts->sendMessage(1, $client['clid'], "[b]Cleared $i channel groups.[/b]");
	}
}
?>