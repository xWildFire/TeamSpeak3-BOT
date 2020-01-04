<?php
class addchannels {
	 public static function command($ts, $client, $args, $clients, $channels, $server, $instances){
		$cfg = $ts->config['functions']['addchannels'];
		if(count($args) < 1 || !is_numeric($args[0])){
			$ts->sendMessage(1, $invokerid, "Usage: !addchannel <amount>");
			return;
		}else if($args[0] > $cfg['max']){
			$args[0] = $cfg['max'];
		}
		
		$nr = 0;
		$val = array();
		foreach($channels as $channel){		
			if($channel['pid'] == $cfg['pid']){
				$val['channel_order'] = $channel['cid'];
				++$nr;
			}  
		}
		
		$desc = file_get_contents("config/messages/channel_free.txt");
		
		$val['channel_topic'] = "#FREE";
		$val['channel_password'] = '';
		$val['channel_maxclients'] = 0;
		$val['channel_maxfamilyclients'] = 0;
		$val['channel_flag_maxclients_unlimited'] = 0;
		$val['channel_flag_maxfamilyclients_unlimited'] = 0; 
		$val['channel_flag_maxfamilyclients_inherited'] = 0;
		$val['channel_codec_quality'] = 10;
		$val['cpid'] = $cfg['pid'];
		$val['channel_flag_permanent'] = 1;

		for($i = 0; $i < $args[0]; ++$i){
			++$nr;
			
			$val['channel_name'] = "$nr. Prywatny Kanał - Wolny";
			$val['channel_description'] = str_replace("{NR}", $nr, $desc);
			
			
			$sub = $ts->channelCreate($val);
			$val['channel_order'] = (integer)$sub['cid'];
		}
		$ts->sendMessage(1, $client['clid'], "Added ".$args[0]." channels.");
	}
}
?>