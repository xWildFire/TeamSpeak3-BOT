<?php
class channel_poke {
	private static $poked = array();
	
	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['channel_poke'];
		$time = time()-$cfg['cooldown'];
		self::$poked = array_filter(self::$poked, function($value)use($time){return $value >= $time;});
		
		foreach($clients as $client){
			if(!array_key_exists($client['cid'], $cfg['channels']) || array_key_exists($client['cid'], self::$poked) || $client['client_is_talker'] || !$client['client_input_hardware'])
				continue;
			$ccfg = $cfg['channels'][$client['cid']];
			if(isIn($client, $ccfg['groups']))
				continue;
			
			self::$poked[$client['cid']] = time();
			$msg = "Użytkownik [URL=client://0/".$client['client_unique_identifier']."]".substr($client['client_nickname'], 0, 14)."[/URL] wszedł na kanał pomocy.";
			
			foreach($clients as $gclient){
				if(!isIn($gclient, $ccfg['groups']))
					continue;
				if($client['clid'] == $gclient['clid'])
					continue 2;
				if($ccfg['poke']){
					$ts->clientPoke($gclient['clid'], substr($msg, 0, 100));
				}else{
					$ts->sendMessage(1, $gclient['clid'], $msg);
				}
			}
		}
	}
}
?>