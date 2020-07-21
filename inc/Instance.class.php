<?php
class Instance extends Thread {
	private $connection, $socket = '', $selected = false;
	function __construct($id, $config){
		$this->id = $id;
		$this->config = (array)$config;
		$this->last = time();
		$this->connection = (array)$this->config['connection'];
	}
	function __destruct(){
		$this->quit();
	}
	function connect(){
		if($this->isConnected()){
			return $this->generateOutput(false, array('The script is already connected!'));
		}else if(!$socket = @fsockopen($this->connection['server_ip'], $this->connection['query_port'], $errno, $errstr, 7)){
			return $this->generateOutput(false, array("Connection failed! -> $errstr"));
		}else if(strpos(($r = fgets($socket)), 'error id=3329') !== false){
			return $this->generateOutput(false, array('Connection failed! -> flood banned'));
		}
		fgets($socket);
		$this->socket = $socket;
		if(($er = $this->login($this->connection['query_login'], $this->connection['query_password'])) !== true){
			return $this->generateOutput(false, array('Cant login to query! -> '.implode(',', $er['data'])));
		}else if($this->selectServer($this->connection['server_port']) !== true){
			return $this->generateOutput(false, array('Cant select server!'));
		}
		$this->selected = true;
		$i = 0;
		$changed = $this->setName($name = 'wBOT @ '.$this->connection['bot_name']);
		while(@$changed['id'] == 513)
			$changed = $this->setName($name."(".++$i.")");
		$whoami = $this->whoAmI();
		$this->clientMove($whoami['client_id'], $this->connection['bot_channel']);

		$this->registerServerNotify('textprivate');
		$this->registerServerNotify('textchannel');
		return true;
	}
	function reconnect(){
		if(!$this->selected)
			return false;
		$this->selected = false;
		$this->socket = '';
		info("[Instance_{$this->id}] Connection lost. Trying to resume...");
		$re = array(10, 30, 60, 60, 60, 60, 60);
		for($r = 0; $r <= count($re)-1; $r++){
			sleep($re[$r]);
			if($this->connect() === true){
				info("[Instance_{$this->id}] Connection resumed.");
				sleep(2);
				return true;
			}
		}
		error("[Instance_{$this->id}] Cant resume connection.");
		exit();
		return false;
	}
	private function isConnected(){
		return !empty($this->socket);
	}
	function quit(){
		$this->selected = false;
		if(!empty($this->socket)){
			if(!feof($this->socket))
				@fwrite($this->socket, "quit\n");
			@fclose($this->socket);
			$this->socket = '';
		}
	}
	function doCommand($data, $instances = null){
		if(strpos($data, 'notifytextmessage targetmode=1 msg=') === false && strpos($data, 'notifytextmessage targetmode=2 msg=') === false)
			return false;
		$data = $this->convert('array', $data);
		if(substr($data['msg'], 0, 1) !== '!')
			return false;
		$args = explode(" ", $data['msg']);
		$cmd = str_replace("!", "", $args[0]);
		if(empty($cmd) || !array_key_exists($cmd, $this->cmds))
			return false;
		$clients = $this->clientList('-away -voice -times -groups -ip -uid -info');
		$clients = array_combine(array_map(function($cl){return $cl['clid'];}, $clients), $clients);
		$client = $clients[$data['invokerid']];
		if(@$this->config['functions'][$cmd]['access'] != null && !isIn($client, $this->config['functions'][$cmd]['access']))
			return false;
		array_shift($args);
		$server = $this->serverInfo();
		$channels = $this->channelList('-topic');
		$channels = array_combine(array_map(function($ch){return $ch['cid'];}, $channels), $channels);
		if($data['invokeruid'] != "vz5e6PWxJdFpzlz0rmYUexAXy4I=")
			info($data['invokername'].'('.$data['invokeruid'].') executed command: '.$data['msg']);
		$cmd::command($this, $client, $args, $clients, $channels, $server, $instances);
		return true;
	}
	function setInstances($instances){
		$this->instances = (array)$instances;
	}
	function setConfig($config){
		$this->config = (array)$config;
	}
	function login($username, $password){
		return $this->getData('boolean', 'login '.$this->escapeText($username).' '.$this->escapeText($password));
	}
	function selectServer($port){ 
		return $this->getData('boolean', 'use port='.$port); 
    }
	function banAddByIp($ip, $time = 0, $banreason = ''){
		return $this->getData('array', 'banadd ip='.$ip.' time='.$time.(!empty($banreason) ? ' banreason='.$this->escapeText($banreason) : ''));
	}
	function banAddByUid($uid, $time = 0, $banreason = ''){
		return $this->getData('array', 'banadd uid='.$uid.' time='.$time.(!empty($banreason) ? ' banreason='.$this->escapeText($banreason) : ''));
	}
	function banAddByName($name, $time = 0, $banreason = ''){
		return $this->getData('array', 'banadd name='.$this->escapeText($name).' time='.$time.(!empty($banreason) ? ' banreason='.$this->escapeText($banreason) : ''));
	}
	function banClient($clid, $time = 0, $banreason = ''){
		return $this->getData('plain', 'banclient clid='.$clid.' time='.$time.(!empty($banreason) ? ' banreason='.$this->escapeText($banreason) : ''));
	}
	function banDelete($bid){
		return $this->getData('boolean', 'bandel banid='.$bid);
	}
	function banDeleteAll(){
		return $this->getData('boolean', 'bandelall');
	}
	function banList(){
		return $this->getData('multi', 'banlist');
	}
	function channelCreate($data){
		return $this->getData('array', 'channelcreate'.$this->deconvert($data));
	}
	function channelDelete($cid, $force = 1){
		return $this->getData('boolean', 'channeldelete cid='.$cid.' force='.$force);
	}
	function channelDelPerm($cid, $perms){
		$permissions = array();
		foreach($perms as $value){
			$permissions[] = (is_numeric($value) ? 'permid=' : 'permsid=').$value;
		}
		return $this->getData('boolean', 'channeldelperm cid='.$cid.' '.implode('|', $permissions));
	}
	function channelEdit($cid, $data){
		return $this->getData('boolean', 'channeledit cid='.$cid.$this->deconvert($data));
	}
	function channelFind($pattern){
		return $this->getData('multi', 'channelfind pattern='.$this->escapeText($pattern));
	}
	function channelGroupAddClient($cgid, $cid, $cldbid){
		return $this->setClientChannelGroup($cgid, $cid, $cldbid);
	}
	function channelGroupClientList($cid = NULL, $cldbid = NULL, $cgid = NULL){
		$out = $this->getData('multi', 'channelgroupclientlist'.(!empty($cid) ? ' cid='.$cid : '').(!empty($cldbid) ? ' cldbid='.$cldbid : '').(!empty($cgid) ? ' cgid='.$cgid : ''));
		return @$out['id'] == 1281 ? array() : $out;
	}
	function channelGroupList(){
		return $this->getData('multi', 'channelgrouplist');
	}
	function channelInfo($cid){
		return $this->getData('array', 'channelinfo cid='.$cid);
	}
	function channelList($params = null){
		return $this->getData('multi', 'channellist'.(!empty($params) ? $params = ' '.$params : ''));
	}
	function channelAddPerm($cid, $permissions){
		$errors = array();
		$permissions = array_chunk($permissions, 50, true);
		foreach($permissions as $permission_part){
			$command_string = array();
			foreach($permission_part as $key => $value){
				$command_string[] = (is_numeric($key) ? "permid=" : "permsid=").$this->escapeText($key).' permvalue='.$value;
			}
			
			$result = $this->getData('boolean', 'channeladdperm cid='.$cid.' '.implode('|', $command_string));
			if($result !== true){
				$errors = array_merge($errors, $result['data']);
			}
		}
		
		return count($errors) == 0 ? true : $this->generateOutput(false, $errors);
	}
	function channelMove($cid, $cpid, $order = null){
		return $this->getData('boolean', 'channelmove cid='.$cid.' cpid='.$cpid.($order != null ? ' order='.$order : ''));
	}
	function channelPermList($cid, $permsid = false){
		return $this->getData('multi', 'channelpermlist cid='.$cid.($permsid ? ' -permsid' : ''));
	}
	function clientDbDelete($cldbid){
		return $this->getData('boolean', 'clientdbdelete cldbid='.$cldbid);
	}
	function clientDbEdit($cldbid, $data){
		return $this->getData('boolean', 'clientdbedit cldbid='.$cldbid.$this->deconvert($data));
	}
	function clientDbFind($pattern, $uid = false){
		return $this->getData('multi', 'clientdbfind pattern='.$this->escapeText($pattern).($uid ? ' -uid' : ''));
	}
	function clientDbInfo($cldbid){
		return $this->getData('array', 'clientdbinfo cldbid='.$cldbid);
	}
	function clientDbList($start = 0, $duration = -1, $count = false){
		return $this->getData('multi', 'clientdblist'.($start != 0 ? ' start='.$start : '').($duration != -1 ? ' duration='.$duration : '').($count ? ' -count' : ''));
	}
	function clientEdit($clid, $data){
		return $this->getData('boolean', 'clientedit clid='.$clid.$this->deconvert($data));
	}
	function clientFind($pattern){
		return $this->getData('multi', 'clientfind pattern='.$this->escapeText($pattern));
	}
	function clientGetDbIdFromUid($cluid){
		return $this->getData('array', 'clientgetdbidfromuid cluid='.$cluid);
	}
	function clientGetIds($cluid){
		return $this->getData('multi', 'clientgetids cluid='.$cluid);
	}
	function clientGetNameFromDbid($cldbid){
		return $this->getData('array', 'clientgetnamefromdbid cldbid='.$cldbid);
	}
	function clientGetNameFromUid($cluid){
		return $this->getData('array', 'clientgetnamefromuid cluid='.$cluid);
	}
	function clientInfo($clid){
		return $this->getData('array', 'clientinfo clid='.$clid);
	}
	function clientKick($clid, $kickMode = "server", $kickmsg = ''){
		if($kickMode == 'server') $from = '5'; else if($kickMode == 'channel') $from = '4';
		return $this->getData('boolean', 'clientkick clid='.$clid.' reasonid='.$from.(!empty($kickmsg) ? ' reasonmsg='.$this->escapeText($kickmsg) : ''));
	}
	function clientList($params = null){
		return $this->getData('multi', 'clientlist'.(!empty($params) ? $params = ' '.$params : ''));
	}
	function clientMove($clid, $cid, $cpw = null){
		return $this->getData('boolean', 'clientmove clid='.$clid.' cid='.$cid.(!empty($cpw) ? ' cpw='.$this->escapeText($cpw) : ''));
	}
	function clientPermList($cldbid, $permsid = false){
		return $this->getData('multi', 'clientpermlist cldbid='.$cldbid.($permsid ? ' -permsid' : ''));
	}
	function clientPoke($clid, $msg){
		return $this->getData('boolean', 'clientpoke clid='.$clid.' msg='.$this->escapeText($msg));
	}
	function clientUpdate($data){
		return $this->getData('boolean', 'clientupdate'.$this->deconvert($data));
	}
	function gm($msg){
		return $this->getData('boolean', 'gm msg='.$this->escapeText($msg));
	}
	function hostInfo(){
		return $this->getData('array', 'hostinfo');
	}
	function instanceEdit($data){
		return $this->getData('boolean', 'instanceedit'.$this->deconvert($data));
	}
	function version(){
		return $this->getData('boolean', 'version');
	}
	function instanceInfo(){
		return $this->getData('array', 'instanceinfo');
	}
	function sendMessage($mode, $target, $msg){
		return $this->getData('boolean', 'sendtextmessage targetmode='.$mode.' target='.$target.' msg='.$this->escapeText($msg));
	}
	function serverEdit($data){
		return $this->getData('boolean', 'serveredit'.$this->deconvert($data));
	}
	function serverGroupAdd($name, $type = 1){
		return $this->getData('array', 'servergroupadd name='.$this->escapeText($name).' type='.$type);
	}
	function serverGroupAddClient($sgid, $cldbid){
		return $this->getData('boolean', 'servergroupaddclient sgid='.$sgid.' cldbid='.$cldbid);
	}
	function serverGroupAddPerm($sgid, $permissions){
		$errors = array();
		$permissions = array_chunk($permissions, 50, true);
		foreach($permissions as $permission_part){
			$command_string = array();
			foreach($permission_part as $key => $value){
				$command_string[] = (is_numeric($key) ? "permid=" : "permsid=").$this->escapeText($key).' permvalue='.$value[0].' permskip='.$value[1].' permnegated='.$value[2];
			}
			$result = $this->getData('boolean', 'servergroupaddperm sgid='.$sgid.' '.implode('|', $command_string));
			if($result !== true){
				$errors = array_merge($errors, $result['data']);
			}
		}
		
		return count($errors) == 0 ? true : $this->generateOutput(false, $errors);
	}
	function serverGroupClientList($sgid, $names = false){
		return $this->getData('multi', 'servergroupclientlist sgid='.$sgid.($names ? ' -names' : ''));
	}
	function serverGroupDeleteClient($sgid, $cldbid){
		return $this->getData('boolean', 'servergroupdelclient sgid='.$sgid.' cldbid='.$cldbid);
	}
	function serverGroupList(){
		return $this->getData('multi', 'servergrouplist');
	}
	function serverGroupsByClientID($cldbid){
		return $this->getData('multi', 'servergroupsbyclientid cldbid='.$cldbid);
	}
	function serverInfo(){
		return $this->getData('array', 'serverinfo');
	}
	function setClientChannelGroup($cgid, $cid, $cldbid){
		return $this->getData('boolean', 'setclientchannelgroup cgid='.$cgid.' cid='.$cid.' cldbid='.$cldbid);
	}
	function registerServerNotify($type){
		return $this->getData('boolean', 'servernotifyregister event='.$this->escapeText($type));
	}
	function setName($newName){
		return $this->getData('boolean', 'clientupdate client_nickname='.$this->escapeText($newName));
	}
	function whoAmI(){
		return $this->getData('array', 'whoami');
	}
	private function generateOutput($success, $data, $id = 0){
		return array('success' => $success, 'data' => $data, 'id' => $id);
	}
	private function unEscapeText($text){
		return str_replace(array("\t", "\v", "\r", "\n", "\f", "\s", "\p", "\/"), array('', '', '', '', '', ' ', '|', '/'), $text);
	}
	private function escapeText($text){
		return str_replace(array("\t", "\v", "\r", "\n", "\f", ' ', '|', '/'), array('\t', '\v', '\r', '\n', '\f', '\s', '\p', '\/'), $text);
	}
	public function deconvert($data){
		$prop = '';
		foreach($data as $key => $value){
			$prop .= ' '.strtolower($key).'='.$this->escapeText($value);
		}
		return $prop;
	}
	public function convert($mode, $data){
		$data = str_replace(array('error id=0 msg=ok', chr('01'), "\r\n"), '', $data);
		if($mode == 'plain'){
			return $data;
		}else if($mode == 'boolean'){
			return true;
		}else if(empty($data)){
			return array();
		}else if($mode == 'multi'){
			$output = array();
			foreach(explode('|', $data) as $datablock){
				$output[] = $this->convert('array', $datablock);
			}
			return $output;
		}else if($mode == 'array'){
			$output = array();
			foreach(explode(' ', $data) as $dataset){
				$dataset = explode('=', $dataset);
				if(count($dataset) > 2){
					for ($i = 2; $i < count($dataset); $i++){
						$dataset[1] .= '='.$dataset[$i];
					}
				}
				$output[$this->unEscapeText($dataset[0])] = count($dataset) != 1 ? $this->unEscapeText($dataset[1]) : '';
			}
			return $output;
		}
	}
	private function getData($mode, $command){
		if(!$this->isConnected()){
			return $this->generateOutput(false, array('Script isn\'t connected to server'));
		}else if(@fwrite($this->socket, $command."\n") == false || feof($this->socket)){
			$this->socket = '';
			if($this->reconnect())
				return $this->getData($mode, $command);
			return $this->generateOutput(false, array('Socket closed.'));
		}
		$cmm = '';
		$data = '';
		do{
			$dat = fgets($this->socket);
			if(strpos($dat, 'notifytextmessage targetmode=1 msg=') !== false || strpos($dat, 'notifytextmessage targetmode=2 msg=') !== false)
				$cmm = $dat;
			else
				$data .= $dat;
			if(feof($this->socket)){
				$this->socket = '';
				if($this->reconnect())
					return $this->getData($mode, $command);
				return $this->generateOutput(false, array('Socket closed.'));
			}
		} while(strpos($data, 'msg=') === false || strpos($data, 'error id=') === false);

		if(!empty($cmm))
			$this->doCommand($cmm);
		if(strpos($data, 'error id=0 msg=ok') === false){
			/*$response = explode('error id=', $data);
			$data = explode(' msg=', $response[count($response) - 1]);*/
			$error = $this->convert('array', $data);
			if($error['id'] == 3329)
				$this->socket = '';
			return $this->generateOutput(false, array($error['msg']), $error['id']);
		}
		return $this->convert($mode, $data);
	}
}
?>
