<?php
class youtube {

	public static function execute($ts, $clients, $channels, $server){
		$cfg = $ts->config['functions']['youtube'];
		$youtubers = $cfg['youtubers'];

		
		foreach($youtubers as $yid => $cid){
			$ci = self::getFile("https://www.googleapis.com/youtube/v3/channels?part=statistics,snippet&id=$yid&key=KEY");
			$live = self::getFile("https://www.googleapis.com/youtube/v3/search?part=snippet&channelId=$yid&type=video&eventType=live&key=KEY");
			if($live['pageInfo']['totalResults'] == 0){
				$name = $cfg['offline'];
				$stat = "";
			}else{
				$stat = "[url=http://www.youtube.com/watch?v=".$live['items'][0]['id']['videoId']."][color=darkred]LIVE[/color][/url]";
				$name = str_replace("{VIEWERS}", file_get_contents("http://www.youtube.com/live_stats?v=".$live['items'][0]['id']['videoId']), $cfg['online']);
			}
			$desc = file_get_contents('config/messages/youtube.txt');
			$rep = array(
				"{NAME}" => $ci['items'][0]['snippet']['title'],
				"{ID}" => $yid,
				"{STATUS}" => $stat,
				"{SUBSCRIPTIONS}" => number_format($ci['items'][0]['statistics']['subscriberCount'], 0, '.', ' '),
				"{VIEWS}" => number_format($ci['items'][0]['statistics']['viewCount'], 0, '.', ' '),
				"{VIDEOS}" => number_format($ci['items'][0]['statistics']['videoCount'], 0, '.', ' ')
			);
			$ts->channelEdit($cid, array('channel_name' => str_replace("{NAME}", $ci['items'][0]['snippet']['title'], $name), 'channel_description' => strtr($desc, $rep)));
		}
	}

	private static function getFile($url){
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_URL => $url
		));
		$data = curl_exec($ch);
		curl_close($ch);
		return json_decode($data,true);
	}
}
?>
