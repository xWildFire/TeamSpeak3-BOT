<?php
class guestinfo {
	private static $joined;
	
	public static function load($ts){
		$clients = $ts->clientList('-away -voice -times -groups -ip -uid -info');
		self::$joined = (array)array_map(function($cl){return $cl['clid'];}, $clients);
	}

	public static function execute($ts, $clients, $channels, $server){
		$msg = "
[i]Po [u][b]90 minutach[/b][/u] aktywności otrzymasz range [b][color=red]Użytkownik[/color][/b].
Następnie otrzymasz kanał prywatny, wchodząc na [b][color=blue]• Darmowy kanał prywatny[/color][/b].[/i]";
		$new = array_map(function($cl){return $cl['clid'];}, $clients);
		$diff = array_diff($new, self::$joined);
		foreach($diff as $clid) {
			if($clients[$clid]['client_servergroups'] == $server['virtualserver_default_server_group']){
				$ts->sendMessage(1, $clid, $msg);
			}
		}
		self::$joined = $new;
	}
}
?>