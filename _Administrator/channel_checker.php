<?php
class channel_checker {

    public static function execute($ts, $clients, $channels, $server) {
        $cfg = $ts->config['functions']['channel_checker'];
        $date = date('d.m.Y');
        $time = time();
        $priv = array();
        $nr = 0;
        foreach ($channels as $ch) {
            if (array_key_exists($ch['pid'], $priv)) {
                $priv[$ch['pid']]['sub'][] = $ch;
                $priv[$ch['pid']]['online'] += $ch['total_clients'];
            } else if ($ch['pid'] == $cfg['pid']) {
                $priv[$ch['cid']] = $ch;
                $priv[$ch['cid']]['sub'] = array();
                $priv[$ch['cid']]['online'] = $ch['total_clients'];
                $priv[$ch['cid']]['nr'] = ++$nr;
            }
		}
		$throw = [];
        $desc = file_get_contents("config/messages/channel_free.txt");
        foreach ($priv as $cid => $ch) {
            $data = array();

            if (startsWith($ch['channel_name'], $ch['nr'] . ".") == false) {
                $data['channel_name'] = $ch['nr'] . ". " . (preg_replace("/([0-9]?[0-9]?[0-9])[.]\s?/", "", $ch['channel_name']));
            }

            if ($ch['channel_topic'] != '#FREE') {
                if ($ch['online'] > 0 && $ch['channel_topic'] != $date) {
                    $data['channel_topic'] = $date;
                } else if ((strtotime($ch['channel_topic']) + 604800) < $time) {
                    if ($ch['channel_name'] != ($ch['nr'] . ". Prywatny Kanał - Wolny")) {
                        $data['channel_name'] = $ch['nr'] . ". Prywatny Kanał - Wolny";
                    }

                    $data['channel_topic'] = "#FREE";
                    $data['channel_password'] = '';
                    $data['channel_maxclients'] = 0;
                    $data['channel_maxfamilyclients'] = 0;
                    $data['channel_flag_maxclients_unlimited'] = 0;
                    $data['channel_flag_maxfamilyclients_unlimited'] = 0;
                    $data['channel_flag_maxfamilyclients_inherited'] = 0;
                    $data['channel_description'] = str_replace("{NR}", $ch['nr'], $desc);

                    $groups = $ts->channelGroupClientList($cid);
                    if (!empty($groups)) {
                        foreach ($groups as $gr) {
                            $ts->setClientChannelGroup($server['virtualserver_default_channel_group'], $cid, $gr['cldbid']);
                        }
                    }
                    foreach ($ch['sub'] as $sub) {
                        $ts->channelDelete($sub['cid']);
                    }
					$throw[] = $cid;
                }
            }

            if (!empty($data)) {
                $ts->channelEdit($cid, $data);
            }
		}
		$ch = end($priv);
		if ($ch['channel_topic'] == '#FREE' || in_array($ch['cid'], $throw)) {
			$ts->channelDelete($ch['cid']);
		}
    }
}
?>