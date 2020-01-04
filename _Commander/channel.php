<?php
class channel {
	 public static function command($ts, $client, $args, $clients, $channels, $server, $instances){
		if(count($args) < 1){
			$ts->sendMessage(1, $client['clid'], "Usage: !channel <clid> [nr]");
			return;
		}else if(!array_key_exists($args[0], $clients)){
			$ts->sendMessage(1, $client['clid'], "Cant find user with specified id.");
			return;
		}
		$cfg = $ts->config['functions']['channel'];
		$aid = $cfg['aid'] ?: $server['virtualserver_default_channel_admin_group'];
		$gclient = $clients[$args[0]];
		$groups = $ts->channelGroupClientList(NULL, $gclient['client_database_id'], $aid);

		$force = $args[count($args)-1] == "force";
		if(!empty($groups) && !$force){
			foreach ($groups as $group){
				$has = $channels[$group['cid']];
				if($has['pid'] != $cfg['pid'])
					continue;
				$ts->sendMessage(1, $client['clid'], "User [b]".$gclient['client_nickname']."[/b] has a channel [url=channelid://".$has['cid']."][b]".$has['channel_name']."[/b][/url]");
				$ts->sendMessage(1, $client['clid'], "Use !channel <clid> force");
				return;
			}
		}
		$want = NULL;
		if(($force && count($args) > 2) || (!$force && count($args) > 1))
			$want = ((integer)$args[1]);
		$want = ((integer)$args[count($args) > 2 ? 2 : 1]);
		foreach($channels as $channel){
			if($channel['pid'] != $cfg['pid'] || $channel['channel_topic'] != '#FREE')
				continue;
			$nr = (integer)$channel['channel_name'];
			if($want != NULL && $nr != $want)
				continue;
			
			$desc = file_get_contents("config/messages/channel_priv.txt");
			$desc = strtr($desc, array('{NR}' => $nr, '{USER}' => $gclient['client_nickname'], '{DATE}' => date('d.m.Y')));
			$ts->channelEdit($channel['cid'], array('channel_name' => "$nr. Kanal ".$gclient['client_nickname'], 'channel_topic' => date('d.m.Y'), 'channel_description' => $desc, 'channel_password' => '12345', 'channel_maxclients' => -1, 'channel_maxfamilyclients' => -1, 'channel_codec_quality' => 10, 'channel_flag_maxclients_unlimited' => 1, 'channel_flag_maxfamilyclients_unlimited' => 0, 'channel_flag_maxfamilyclients_inherited' => 1));
			
			$desc = file_get_contents("config/messages/channel_sub.txt");
			$order = 0;
			for($i = 1; $i <= $cfg['sub_channels']; $i++){
				$sub = $ts->channelCreate(array('channel_name' => "$i. Podkanal", 'channel_description' => $desc, 'channel_order' => $order, 'channel_codec_quality' => 10, 'cpid' => $channel['cid'], 'channel_flag_permanent' => 1));
				$order = $sub['cid'];
			}
			
			$ts->sendMessage(1, $gclient['clid'], "
						[i]Otrzymałeś kanał prywatny [b][color=blue]Nr. ".$nr."[/color][/b], wraz z 2 podkanałami.
						Jego hasło zostało ustawione na: [b][color=blue]12345[/color][/b][/i]");
			$ts->clientMove($gclient['clid'], $channel['cid']);
			$ts->setClientChannelGroup($aid, $channel['cid'], $gclient['client_database_id']);
			info("Channel(nr.$nr) was given to ".$gclient['client_nickname']."(".$gclient['client_unique_identifier'].")");
			$ts->sendMessage(1, $client['clid'], "You have given a channel to [b]".$gclient['client_nickname']."[/b]");
			break;
		}
	}
}

?>