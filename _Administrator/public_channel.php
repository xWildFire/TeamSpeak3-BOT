<?php
class public_channel {
	
	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['public_channel'];
		foreach($clients as $client){
			if(!array_key_exists($client['cid'], $cfg['zone']))
				continue;

			$list = [];
			foreach($channels as $channel){
				if($channel['pid'] != $client['cid'])
					continue;
				$list[str_replace("Publiczny #", "", $channel['channel_name'])] = $channel['cid'];
			}
			for($i=1;$i<=count($list)+1;$i++){
				if(!array_key_exists($i, $list)){
					$nr = $i;
					break;
				}
			}
			$data = array('channel_name' => "Publiczny #$nr", 'cpid' => $client['cid'], 'channel_codec_quality' => 10, 'channel_flag_permanent' => 1);
			if(array_key_exists($nr-1, $list))
				$data['channel_order'] = $list[$nr-1];
			if($cfg['zone'][$client['cid']] > 0){
				$data['channel_flag_maxclients_unlimited'] = 0;
				$data['channel_maxclients'] = $cfg['zone'][$client['cid']];
				$name = "MAX ".$cfg['zone'][$client['cid']];
			}else{
				$name = "NO LIMIT";
			}
			$x = $ts->channelCreate($data);
			$ts->clientMove($client['clid'], $x['cid']);
			$ts->channelEdit($x['cid'], array('channel_flag_permanent' => 0));
			$ts->clientPoke($client['clid'], "Otrzymales kanał [b]Publiczny ($name) #$nr");
		}
	}
}
?>