<?php
class advertise {
	private static $xd;

	public static function load($ts){
		self::$xd = 0;
	}
	
	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['advertise'];
		$mess = $cfg['messages'][self::$xd];
		$ts->sendMessage(3, 1, "
.
ᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒ



» ".$mess."



ᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒᵒ
.");
		self::$xd = self::$xd+1;
		if(self::$xd >= count($cfg['messages']))
			self::$xd = 0;
	}
}
?>