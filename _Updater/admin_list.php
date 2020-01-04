<?php
class admin_list {
	public static function execute($ts, $clients, $channels, $server){
		$online = $server['virtualserver_clientsonline'] - $server['virtualserver_queryclientsonline'];
		if($online < 10)
			return;
		$cfg = $ts->config['functions']['admin_list'];
		$groups = $ts->serverGroupList();
		$groups = array_combine(array_map(function($gr){return $gr['sgid'];}, $groups), $groups);
		$clients = array_combine(array_map(function($gr){return $gr['client_database_id'];}, $clients), $clients);
		
		$desc = file_get_contents('config/messages/admin_list.txt');
		preg_match('/<online>(.*?)<\/online>/s', $desc, $online);
		preg_match('/<offline>(.*?)<\/offline>/s', $desc, $offline);
		preg_match('/<group>(.*?)<\/group>/s', $desc, $group);
		
		$add = '';
		foreach(explode(',', $cfg['groups']) as $gr){
			$gclients = $ts->serverGroupClientList($gr);
			if(!array_key_exists($gr, $groups))
				continue;
			$name = $groups[$gr]['name'];
			
			$users = '';
			foreach($gclients as $client){
				$admin = ($on = array_key_exists($client['cldbid'], $clients)) ? $clients[$client['cldbid']] : $ts->clientDbInfo($client['cldbid']);
				
				$min = time()-$admin['client_lastconnected'];
				$min = sprintf('%2d dni, %2d godzin, %2d minut', ($days = ($min / 86400)), ($hours = (($min - (floor($days) * 86400)) / 3600)), ($min / 60 % 60));
				$min = str_replace(' 0 dni, ', '', $min);
				$min = str_replace(' 0 godzin, ', '', $min);
				
				$vars = array("{NAME}" => $admin['client_nickname'], "{UID}" => $admin['client_unique_identifier'], "{TIME}" => $min);
				if($on){
					$vars["{CID}"] = $admin['cid'];
					$vars["{CNAME}"] = $channels[$admin['cid']]['channel_name'];
					$users .= strtr($online[1], $vars)."\n";
				}else{
					$users .= strtr($offline[1], $vars)."\n";
				}
			}
			$add .= strtr($group[1], array("{NAME}" => $name, "{COUNT}" => count($gclients), "{USERS}" => $users))."\n";
		}
		$desc = preg_replace('/<online>(.*?)<\/online>/s', '', $desc);
		$desc = preg_replace('/<offline>(.*?)<\/offline>/s', '', $desc);
		$desc = preg_replace('/<group>(.*?)<\/group>/s', $add, $desc);
		$ts->channelEdit($cfg['cid'], array('channel_description' => $desc));
	}
}
?>