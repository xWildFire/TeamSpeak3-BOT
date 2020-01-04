<?php
class group_online {
    public static function execute($ts, $clients, $channels, $server) {
        $sgs = $ts->serverGroupList();
        $sgs = array_combine(array_map(function ($gr) {return $gr['sgid'];}, $sgs), $sgs);

        $cfg = $ts->config['functions']['group_online'];
        $check = array();
        foreach ($cfg['groups'] as $gr => $val) {
            foreach (explode(',', $gr) as $gg) {
                if (!array_key_exists($gg, $sgs)) {

                } else {
                    $check[$gg] = $gr;
                }
            }
        }
        $ind = 0;
        $groups = array();
        foreach ($clients as $client) {
            $in = groupsIn($client, array_keys($check));
            foreach ($in as $gr) {
                $groups[$check[$gr]][(array_search($gr, explode(',', $check[$gr])) * 1000) + $ind] = '[URL=client://0/' . $client['client_unique_identifier'] . '][' . $sgs[$gr]['name'] . '] ' . $client['client_nickname'] . '[/URL]';
                $ind++;
            }
        }
        $desc = file_get_contents("config/messages/group_online.txt");
        foreach ($groups as $group => $users) {
            ksort($users);
            $vars = array(
                "{ONLINE}" => count($users),
                "{USERS}" => implode("\n", $users),
            );
            if (strpos($group, ',') == false) {
                $vars["{NAME}"] = $sgs[$group]['name'];
            }
            $max = 0;
            foreach (explode(',', $group) as $gg) {
                $max += count($ts->serverGroupClientList($gg));
            }
            $vars["{MAX}"] = $max;

            $ts->channelEdit($cfg['groups'][$group]['cid'], array('channel_name' => strtr($cfg['groups'][$group]['name'], $vars), 'channel_description' => strtr($desc, $vars)));
        }

        $groups = array_diff(array_keys($cfg['groups']), array_keys($groups));
        foreach ($groups as $group) {
            $vars = array(
                "{ONLINE}" => 0,
                "{USERS}" => '-',
            );

            if (strpos($group, ',') == false) {
                $vars["{NAME}"] = $sgs[$group]['name'];
            }
            $max = 0;
            foreach (explode(',', $group) as $gg) {
                $max += count($ts->serverGroupClientList($gg));
            }
            $vars["{MAX}"] = $max;

            $ts->channelEdit($cfg['groups'][$group]['cid'], array('channel_name' => strtr($cfg['groups'][$group]['name'], $vars), 'channel_description' => strtr($desc, $vars)));
        }
    }
}
?>