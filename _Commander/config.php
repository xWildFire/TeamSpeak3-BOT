<?php
class config {
	public static function command($ts, $client, $args, $clients, $channels, $server, $instances){
		$config = loadConfig();
		if(count($args) > 0 && $args[0] == "reload"){
			foreach($instances as $id => $instance){
				$instance->setConfig($config[$id]);
			}
			$ts->sendMessage(1, $client['clid'], '[b]Reloaded config.[/b]');
			return;
		}else if(count($args) >= 3){
			if($args[0] == "get"){
				$val = $config[$args[1]];
				for($i = 2; $i < count($args); $i++){
					if(!array_key_exists($args[$i], $val)){
						$ts->sendMessage(1, $client['clid'], "Nie znaleziono klucza.");
						return;
					}
					$val = $val[$args[$i]];
				}
				$ts->sendMessage(1, $client['clid'], "[b]Wartość: ".json_encode($val, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."[/b]");
				return;
			}else if($args[0] == "set"){
				$val = '$config[$args[1]]';
				for($i = 2; $i < count($args)-1; $i++){
					$val .= '[$args['.$i.']]';
				}
				$x = $args[count($args)-1];
				if(strpos($x, '@') != false){
					$x = str_replace('=', "'=>'", $x);
					$x = str_replace('@', "','", $x);
					eval($val." = array('$x');");
				}else{
					$x = str_replace('_', ' ', $x);
					eval($val." = '$x';");
				}
				saveConfig($config);

				$ts->setConfig($config[$ts->id]);
				foreach($config as $id => $cfg){
					$instances[$id]->setConfig($config[$id]);
				}
				$ts->sendMessage(1, $client['clid'], "[b]Ustawiono [/b]");
				return;
			}else if($args[0] == "add"){
				$val = '$config[$args[1]]';
				for($i = 2; $i < count($args)-1; $i++){
					$val .= '[$args['.$i.']]';
				}
				@eval($val."[] = '".$args[count($args)-1]."';");
				foreach($config as $id => $cfg){
					$instances[$id]->setConfig($config[$id]);
				}
				saveConfig($config);
				$ts->sendMessage(1, $client['clid'], "[b]Ustawiono [/b]");
				return;
			}else if($args[0] == "remkey"){
				$val = '$config[$args[1]]';
				for($i = 2; $i < count($args); $i++){
					$val .= '[$args['.$i.']]';
				}
				@eval("unset(".$val.");");
				foreach($config as $id => $cfg){
					$instances[$id]->setConfig($config[$id]);
				}
				saveConfig($config);
				$ts->sendMessage(1, $client['clid'], "[b]Ustawiono [/b]");
				return;
			}else if($args[0] == "remval"){
				$val = '$config[$args[1]]';
				for($i = 2; $i < count($args)-1; $i++){
					$val .= '[$args['.$i.']]';
				}
				@eval("unset(".$val.'[array_search($args['.(count($args)-1).'],'.$val.')]);');
				foreach($config as $id => $cfg){
					$instances[$id]->setConfig($config[$id]);
				}
				saveConfig($config);
				$ts->sendMessage(1, $client['clid'], "[b]Ustawiono [/b]");
				return;
			}else if($args[0] == "setmsg"){
				$name = str_replace('.txt', '', $args[1]);
				$args = array_slice($args, 2);
				$msg = implode(" ", $args);
				$msg = str_replace('\n', "\n", $msg);
				file_put_contents("config/messages/$name.txt", $msg);
				$ts->sendMessage(1, $client['clid'], "[b]Ustawiono nowa wiadomosc.[/b]");
				return;
			}else{
				$ts->sendMessage(1, $client['clid'], "Usage: !config <get/set/add/remval/remkey/setmsg> <key>");
				return;
			}
		}
		$ts->sendMessage(1, $client['clid'], "Usage: !config <reload/get/set/setmsg/add/remval/remkey>");
	}
}
?>