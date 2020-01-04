<?php
class twitch {

	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['twitch'];
		$streamers = array_change_key_case($cfg['streamers'], CASE_LOWER);
	
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_HTTPHEADER => array('Client-ID: KEY'),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_URL => 'https://api.twitch.tv/kraken/streams?channel='.implode(',', array_keys($streamers))
		));
		$res = curl_exec($ch);
		curl_close($ch);
		if($res !== false){
			$online = @json_decode($res, true);
			if(!@array_key_exists('streams', $online))
				return;
			$online = $online['streams'];
			$desc = file_get_contents("config/messages/twitch_on.txt");
			foreach($online as $user){
				$rep = array(
					'{NAME}' => $user['channel']['display_name'],
					'{GAME}' => $user['game'],
					'{STATUS}' => $user['channel']['status'],
					'{VIEWERS}' => number_format($user['viewers'], 0, '.', ' '),
					'{FOLLOWERS}' => number_format($user['channel']['followers'], 0, '.', ' '),
					'{VIEWS}' => number_format($user['channel']['views'], 0, '.', ' ')
				);
				$cname = str_replace("{NAME}", $user['channel']['display_name'], $cfg['online']);
				$cname = str_replace("{VIEWERS}", $user['viewers'], $cname);
				$ts->channelEdit($streamers[$user['channel']['name']], array('channel_name' => $cname, 'channel_description' => strtr($desc, $rep)));
				unset($streamers[$user['channel']['name']]);
			}
		}

		$desc = file_get_contents("config/messages/twitch_off.txt");
		foreach($streamers as $name => $cid){
			$ts->channelEdit($cid, array('channel_name' => str_replace("{NAME}", $name, $cfg['offline']), 'channel_description' => str_replace('{NAME}', $name, $desc)));
		}
	}
}
?>
