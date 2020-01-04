<?php
class channel_give {
	
	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['channel_give'];
		$aid = $cfg['aid'] ?: $server['virtualserver_default_channel_admin_group'];
		foreach($clients as $client){
			if($client['cid'] != $cfg['cid'])
				continue;
			$groups = $ts->channelGroupClientList(NULL, $client['client_database_id'], $aid);
			if(!empty($groups)){
				foreach ($groups as $group){
					$has = $channels[$group['cid']];
					if($has['pid'] != $cfg['pid'])
						continue;
					$ts->clientMove($client['clid'], $has['cid']);
					$ts->clientPoke($client['clid'], "Posiadasz juz kanał: [url=channelid://".$has['cid']."][b]".$has['channel_name']."[/b][/url]");
					continue 2;
				}
			}
			$nr = 0;
			$done = false;
			foreach($channels as $channel){
				if($channel['pid'] != $cfg['pid'])
					continue;
				$nr++;
				if($channel['channel_topic'] != '#FREE')
					continue;
				$done = true;
				$nr = (integer)$channel['channel_name'];
				
				$desc = file_get_contents("config/messages/channel_priv.txt");
				$desc = strtr($desc, array('{NR}' => $nr, '{USER}' => $client['client_nickname'], '{DATE}' => date('d.m.Y')));
				$ts->channelEdit($channel['cid'], array('channel_name' => "$nr. Kanal ".$client['client_nickname'], 'channel_topic' => date('d.m.Y'), 'channel_description' => $desc, 'channel_password' => '12345', 'channel_maxclients' => -1, 'channel_maxfamilyclients' => -1, 'channel_codec_quality' => 10, 'channel_flag_maxclients_unlimited' => 1, 'channel_flag_maxfamilyclients_unlimited' => 0, 'channel_flag_maxfamilyclients_inherited' => 1));
				
				$desc = file_get_contents("config/messages/channel_sub.txt");
				$order = 0;
				for($i = 1; $i <= $cfg['sub_channels']; $i++){
					$sub = $ts->channelCreate(array('channel_name' => "$i. Podkanal", 'channel_description' => $desc, 'channel_order' => $order, 'channel_codec_quality' => 10, 'cpid' => $channel['cid'], 'channel_flag_permanent' => 1));
					$order = $sub['cid'];
				}
				
				$ts->sendMessage(1, $client['clid'], "
							[i]Otrzymałeś kanał prywatny [b][color=blue]Nr. ".$nr."[/color][/b], wraz z 2 podkanałami.
							Jego hasło zostało ustawione na: [b][color=blue]12345[/color][/b][/i]");
				$ts->clientMove($client['clid'], $channel['cid']);
				$ts->setClientChannelGroup($aid, $channel['cid'], $client['client_database_id']);
				info("Channel(nr.$nr) was given to ".$client['client_nickname']."(".$client['client_unique_identifier'].")");
				break;
			}
			if(!$done){
				$nr++;
				$desc = file_get_contents("config/messages/channel_priv.txt");
				$desc = strtr($desc, array('{NR}' => $nr, '{USER}' => $client['client_nickname'], '{DATE}' => date('d.m.Y')));
				$x = $ts->channelCreate(array('channel_name' => "$nr. Kanal ".$client['client_nickname'], 'cpid' => $cfg['pid'], 'channel_topic' => date('d.m.Y'), 'channel_description' => $desc, 'channel_password' => '12345', 'channel_maxclients' => -1, 'channel_maxfamilyclients' => -1, 'channel_codec_quality' => 10, 'channel_flag_maxclients_unlimited' => 1, 'channel_flag_maxfamilyclients_unlimited' => 0, 'channel_flag_maxfamilyclients_inherited' => 1, 'channel_flag_permanent' => 1));
				
				$desc = file_get_contents("config/messages/channel_sub.txt");
				$order = 0;
				for($i = 1; $i <= $cfg['sub_channels']; $i++){
					$sub = $ts->channelCreate(array('channel_name' => "$i. Podkanal", 'channel_description' => $desc, 'channel_order' => $order, 'channel_codec_quality' => 10, 'cpid' => $x['cid'], 'channel_flag_permanent' => 1));
					$order = $sub['cid'];
				}
				
				$ts->sendMessage(1, $client['clid'], "
							[i]Otrzymałeś kanał prywatny [b][color=blue]Nr. ".$nr."[/color][/b], wraz z 2 podkanałami.
							Jego hasło zostało ustawione na: [b][color=blue]12345[/color][/b][/i]");
				$ts->clientMove($client['clid'], $x['cid']);
				$ts->setClientChannelGroup($aid, $x['cid'], $client['client_database_id']);
				info("Channel(nr.$nr) was given to ".$client['client_nickname']."(".$client['client_unique_identifier'].")");
			}
		}
	}
}
?>