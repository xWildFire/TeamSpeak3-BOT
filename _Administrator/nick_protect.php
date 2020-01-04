<?php
class nick_protect {
	
	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['nick_protect'];
		$regex = "/\[(".$cfg['groups'].")\]|(".$cfg['words'].")/i";
		foreach($clients as $client){
			if($client['client_type'] != 0)
				continue;
			$name = strtolower(str_replace(" ", "", $client['client_nickname']));
			if(preg_match($regex, $client['client_nickname']) > 0){
				$ts->clientKick($client['clid'], 'server', 'Nick zawiera niedozwolone słowa.');
			}else if(preg_match("/[^a-zA-Z0-9żźćńśółąęŻŹĆŃŚÓŁ~@#$%^&*♥(){}\\\\|\=\-:\"<>?!`_+;'.\[\]\/, ]+/i", $client['client_nickname']) > 0){
				$ts->clientKick($client['clid'], 'server', 'Nick nie może zawierać znaków specjalnych.');
			}
		}
	}
}

?>