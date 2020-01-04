<?php
class bot {
    public static function command($ts, $client, $args, $clients, $channels, $server, $instances) {

        if (count($args) > 0) {
            if ($args[0] == "restart") {
                foreach ($instances as $instance) {
                    if (!method_exists($instance, 'getProcessId')) {
                        return;
                    }

                    if ($instance->getProcessId() !== 0) {
                        $instance->abort();
                    }

                }
                file_put_contents('cache/' . md5('RESTART'), '');
                clearstatcache();
                $ts->quit();
                posix_kill(posix_getpid(), SIGKILL);
                return;
            } else if ($args[0] == "stop") {
                $xx = null;
                foreach ($instances as $instance) {
                    if (!method_exists($instance, 'getProcessId')) {
                        break;
                    }

                    if ($instance->getProcessId() == 0) {
                        $xx = $instance;
                    } else {
                        $instance->abort();
                    }

                }
                exit();
                return;
            } else if ($args[0] == "logs") {
                if (count($args) > 1 && $args[1] == "clear") {
                    $output = "";
                    if (count($args) > 2 && is_numeric($args[2])) {
                        $lines = file("logs.log");
                        $nr = count($lines);
                        for ($i = $nr - $args[2]; $i < $nr; $i++) {
                            $output .= $lines[$i];
                        }
                    }
                    file_put_contents("logs.log", $output);
                    $ts->sendMessage(1, $client['clid'], "Logs cleared.");
                    return;
                }
                $lines = count($args) > 1 && is_numeric($args[1]) ? $args[1] : 10;
                $file = file("logs.log");
                for ($i = max(0, count($file) - $lines); $i < count($file); $i++) {
                    $ts->sendMessage(1, $client['clid'], $file[$i]);
                }
                return;
            }
        }
        $ts->sendMessage(1, $client['clid'], "Usage: !bot <restart/stop/logs>");
    }
}
?>