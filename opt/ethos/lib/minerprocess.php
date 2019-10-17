<?php

function delete_old_api_port() {
  $oldapiport = trim(`/usr/bin/sudo /sbin/iptables -t nat -L | grep DNAT | grep LOCAL |  grep -m 1 -Poi "(?<=to:127.0.0.1:)(.*)"`);

	if ($oldapiport != "") {
		`/usr/bin/sudo /sbin/iptables -t nat -D POSTROUTING -m addrtype --src-type LOCAL --dst-type UNICAST -j MASQUERADE`;
		`/usr/bin/sudo /sbin/iptables -t nat -D PREROUTING -p tcp -m tcp --dport 42000 -j DNAT --to-destination 127.0.0.1:$oldapiport`;
		`/usr/bin/sudo /sbin/iptables -t nat -D OUTPUT -p tcp -m addrtype --src-type LOCAL --dst-type LOCAL -m tcp --dport 42000 -j DNAT --to-destination 127.0.0.1:$oldapiport`;
	}
}

function select_api_port() {
	$apiport = rand(42001, 42200);
	if (!($socket = @fsockopen("127.0.0.1", $apiport, $errno, $errstr, 3))) {
		echo "Not in use, creating iptables rule..";
		`/usr/bin/sudo /sbin/sysctl -w net.ipv4.conf.all.route_localnet=1`;
		`/usr/bin/sudo /sbin/iptables -t nat -A POSTROUTING -m addrtype --src-type LOCAL --dst-type UNICAST -j MASQUERADE`;
		`/usr/bin/sudo iptables -t nat -A OUTPUT -m addrtype --src-type LOCAL --dst-type LOCAL -p tcp --dport 42000 -j DNAT --to-destination 127.0.0.1:$apiport`;
		
		$externalapi = trim(`/opt/ethos/sbin/ethos-readconf externalapi`);

		if ($externalapi == "enabled") {
			`/usr/bin/sudo sysctl -w net.ipv4.conf.eth0.route_localnet=1`;
			`/usr/bin/sudo iptables -t nat -A PREROUTING -p tcp --dport 42000 -j DNAT --to-destination 127.0.0.1:$apiport`;
		}
	} else {
	  echo "In use, finding new port.";
	  fclose($socket);
	  select_api_port();
  }

  return $apiport;
}

function select_gpus() {
	$devices = array();
	$selectedgpus = trim(`/opt/ethos/sbin/ethos-readconf selectedgpus`);

	if($selectedgpus != "") {
		$devices = explode(" ", $selectedgpus);
	}

	if(count($devices) == 0) {
		//mining functionality is dependent on gpucount.file always being available
		$gpus = trim(file_get_contents("/var/run/ethos/gpucount.file"));

		for($i = 0; $i < $gpus; $i++){
				$devices[] = $i;
		}
	}

	return $devices;
}

// fglrx check apu function
function check_apu()
{
	$checkapu = trim(`/opt/miners/ethminer/ethminer -G --list-devices | tee /var/run/ethos/checkigp.file`);

	if (preg_match('#\b(Kaveri|Beavercreek|Sumo|Wrestler|Kabini|Mullins|Temash|Trinity|Richland|Carrizo)\b#', $checkapu, $baddevices)) {
		echo "non-mining device found, excluding from mining gpus.\n";

		$validdevices = `grep ']' /var/run/ethos/checkigp.file | grep -v FORMAT | grep -v OPENCL | egrep -iv 'Beavercreek|Sumo|Wrestler|Kabini|Mullins|Temash|Trinity|Richland|Carrizo' | sed 's/\[//g' | sed 's/\]//g' | awk '{print \$1}' | xargs`;

		$extraflags = trim("$validdevices");

		return $extraflags;
	}
}

function check_status()
{
	$miner = trim(`/opt/ethos/sbin/ethos-readconf miner`);
	$miner_pid = trim(`/opt/ethos/sbin/ethos-readconf pid`);
	$miner_uptime = 0 + trim(`/opt/ethos/sbin/ethos-readdata mineruptime`);
	$max_boots = trim(`/opt/ethos/sbin/ethos-readconf autoreboot`);
	
	$uptime = trim(`cut -d " " -f1 /proc/uptime | cut -d "." -f 1`);
	$hostname = trim(file_get_contents("/etc/hostname"));
	
	//boot value assignment

	$status['updating']['value'] = intval(trim(file_get_contents("/var/run/ethos/updating.file")));
	$status['adl_error']['value'] = intval(trim(file_get_contents("/var/run/ethos/adl_error.file")));
	$status['no_cables']['value'] = intval(trim(file_get_contents("/var/run/ethos/nvidia_error.file")));
	$status['nomine']['value'] = intval(trim(file_get_contents("/var/run/ethos/nomine.file")));
	$status['nowatchdog']['value'] = intval(trim(file_get_contents("/var/run/ethos/nowatchdog.file")));

	if (preg_match("/sgminer/",$miner)) {
		$status['sgminerconfigerror']['value']  = intval(trim(@shell_exec("/opt/ethos/bin/lintsgconf status")));
	}

	$status['allow']['value'] = intval(trim(file_get_contents("/opt/ethos/etc/allow.file")));
	$status['off']['value'] = intval(trim(`/opt/ethos/sbin/ethos-readconf off`));
	$status['autorebooted']['value'] = intval(trim(file_get_contents("/opt/ethos/etc/autorebooted.file")));
	$status['defunct']['value'] = intval(trim(`ps uax | grep "$miner " | grep defunct | grep -v grep | wc -l`));
	$status['netdown']['value'] = intval(trim(file_get_contents("/var/run/ethos/network-status.file")));
	$crashedgpus = strval(trim(file_get_contents("/var/run/ethos/crashed_gpus.file")));
	if (($crashedgpus === "0" ) || ($crashedgpus)) {
		$crashedgpus = count(explode(" ", $crashedgpus));
	}

	$status['gpucrashed']['value'] = $crashedgpus;
	$status['overheat']['value'] = intval(trim(file_get_contents("/var/run/ethos/overheat.file")));
	$status['starting']['value'] = intval(trim(`ps uax | grep "$miner " | grep -v defunct | grep -v grep | wc -l`));
	$status['invalid']['value'] = intval(trim(@file_get_contents("/var/run/ethos/invalid.file")));

	$status['hash']['value'] = floatval(trim(`tail -10 /var/run/ethos/miner_hashes.file | sort -V | tail -1 | tr ' ' '\n' | awk '{sum += \$1} END {print sum}'`));

	//boot message assignment

	$status['booting']['message'] = "starting ethos: finishing boot process";
	$status['updating']['updating'] = "do not reboot: system upgrade in progress";
	$status['updating']['updated'] = "reboot required: update complete, reboot system";
	$status['adl_error']['message'] = "hardware error: possible gpu/riser/power failure";
	$status['no_cables']['message'] = "power cable problem: PCI-E power cables not seated properly";
	$status['nomine']['message'] = "hardware error: graphics driver did not load";
	$status['nowatchdog']['message'] = "no overheat protection: overheat protection not running";
	$status['sgminerconfigerror']['message'] = "config error: sgminer configuration is not valid";
	$status['allow']['message'] = "miner disallowed: use 'allow' command";
	$status['off']['message'] = "miner off:  miner set to off in config";
	$status['autorebooted']['message'] = "too many autoreboots: autorebooted ".$status['autorebooted']['value']." times";
	$status['defunct']['message'] = "gpu crashed: reboot required";
	$status['netdown']['message'] = "network failure: network is unreachable";
	$crashedgpusclean = trim(`cat /var/run/ethos/crashed_gpus.file | sed -r 's/[^ ]+/gpu&/g'`);

	$status['gpucrashed']['message'] = "gpu clock problem: $crashedgpusclean clocks are affected";
	$status['overheat']['message'] = "overheat: one or more gpus overheated";
	$status['starting']['message'] = "miner started: miner commanded to start";
	$status['invalid']['message'] = "invalid miner specified: set valid miner";
	$status['hung']['message'] = "possible miner stall: check miner log";
	$status['hash']['message'] = sprintf("%.1f", $status['hash']['value']) . " hash: miner active";

	//boot value/message checks

	if ($status['booting']['value'] > 0) {
		file_put_contents("/var/run/ethos/status.file", $status['booting']['message'] . "\n");
		return false;
	}
	
	if ($status['updating']['value'] == 1) {
		file_put_contents("/var/run/ethos/status.file", $status['updating']['updating'] . "\n");
		return false;
	}
	
	if ($status['updating']['value'] == 2) {
		file_put_contents("/var/run/ethos/status.file", $status['updating']['updated'] . "\n");
		return false;
	}
	
	if ($status['adl_error']['value'] > 0) {
		file_put_contents("/var/run/ethos/status.file", $status['adl_error']['message'] . "\n");
		return false;
	}
	
	if ($status['no_cables']['value'] > 0) {
		file_put_contents("/var/run/ethos/status.file", $status['no_cables']['message'] . "\n");
		return false;
	}

	if ($status['nomine']['value'] > 0) {
		file_put_contents("/var/run/ethos/status.file", $status['nomine']['message'] . "\n");
		return false;
	}

	if ($status['nowatchdog']['value'] > 0) {
		file_put_contents("/var/run/ethos/status.file", $status['nowatchdog']['message'] . "\n");
		return false;
	}
	
	if ($status['sgminerconfigerror']['value'] >= 1 && preg_match("/sgminer/",$miner)) {
		file_put_contents("/var/run/ethos/status.file", $status['sgminerconfigerror']['message'] . "\n");
		return false;
	}
	
	if ($status['allow']['value'] == 0) {
		file_put_contents("/var/run/ethos/status.file", $status['allow']['message'] . "\n");
		return false;
	}

	if ($status['off']['value'] == 1) {
		file_put_contents("/var/run/ethos/status.file", $status['off']['message'] . "\n");
		return false;
	}
	
	if ($status['autorebooted']['value'] > $max_boots) {
		file_put_contents("/var/run/ethos/status.file", $status['autorebooted']['message'] . "\n");
		return false;
	}

	if ($status['defunct']['value'] > 0) {
		file_put_contents("/var/run/ethos/status.file", $status['defunct']['message'] . "\n");
		file_put_contents("/var/run/ethos/defunct.file", $status['defunct']['value']);
		return false;
	}
	if ($status['netdown']['value'] > 0) {
		file_put_contents("/var/run/ethos/status.file", $status['netdown']['message'] . "\n");
		return false;
	}
	if ($status['gpucrashed']['value'] > 0 ) {
		//only report clocks too low if the miner is running
		if ($miner_uptime > 600 && $miner_pid > 0) {
			file_put_contents("/var/run/ethos/status.file", $status['gpucrashed']['message'] . "\n");
			return false;
		}
	}

	if ($status['overheat']['value'] > 0) {
		file_put_contents("/var/run/ethos/status.file", $status['overheat']['message'] . "\n");
		return false;
	}

	if ($status['starting']['value'] == 0) {
		file_put_contents("/var/run/ethos/status.file", $status['starting']['message'] . "\n");
		return true;
	}

	if ($status['invalid']['value'] > 0){
		file_put_contents("/var/run/ethos/status.file", $status['invalid']['message'] . "\n");
		return false;
	}

	if (($miner_uptime > 600 ) && ($status['hash']['value'] == 0)){
		file_put_contents("/var/run/ethos/status.file", $status['hung']['message'] . "\n");
		return false;
	}

	if ($status['hash']['value'] > 0) {
		file_put_contents("/var/run/ethos/status.file", $status['hash']['message'] . "\n");
		return false;
	}
	
}

$pool_syntax = array(
    "ethminer"=>array(
    "ssl"=>"stratum+ssl://%s",
    "stratum+tcp"=>"stratum+tcp://%s",
    "http"=>"http://%s",
    ""=>"%s"
  ),
    "dstm-zcash"=>array(
    "ssl"=>"ssl://%s",
    "stratum+tcp"=>"%s",
    "http"=>"%s",
    ""=>"%s"
  ),
    "ewbf-zcash"=>array(
    "ssl"=>"%s",
    "stratum+tcp"=>"%s",
    "http"=>"%s",
    ""=>"%s"
  ),
  "optiminer-zcash"=>array(
    "ssl"=>"zstratum+ssl://%s",
    "stratum+tcp"=>"%s",
    "http"=>"%s",
    ""=>"%s"
  ),
  "progpowminer"=>array(
    "ssl"=>"%s",
    "stratum+tcp"=>"%s",
    "http"=>"%s",
    ""=>"%s"
  ),
  "default"=>array(
    "ssl"=>"ssl://%s",
    "stratum+tcp"=>"stratum+tcp://%s",
    "http"=>"http://%s",
    ""=>"stratum+tcp://%s"
  )
);

function setup_pools($miner)
{
	global $pool_syntax;

  $miner_syntax = $miner;

  switch($miner_syntax) {
  	case "lolminer":
	  case "xmr-stak":
	  case "xtl-stak":
	  case "ewbf-equihash":
      $miner_syntax = "ewbf-zcash";
      break;

    case "ethminer-single":
    case "progpowminer":
    case "progpowminer-single";	  
    	$miner_syntax = "ethminer";
    	break;
	}

  $profile = ((isset($pool_syntax[$miner_syntax])) ? $miner_syntax : "default");

	foreach (range(1,4) as $checkpool) {
		if (($pools[$checkpool] = trim(`/opt/ethos/sbin/ethos-readconf proxypool$checkpool`)) != "") {
			if (!preg_match("/^(ssl|http|stratum\+tcp)\:\/\/(.+)$/", $pools[$checkpool], $proxypoolsplit)) {
				$proxypoolsplit = array("", "", $pools[$checkpool]);
			}

			$pools[$checkpool] = sprintf($pool_syntax[$profile][$proxypoolsplit[1]], $proxypoolsplit[2]);
		}
	}
	return array_values($pools);
}

function check_miner()
{
	$miner = trim(`/opt/ethos/sbin/ethos-readconf miner`);
	$checks = array("proxypool1","proxypool2","proxypool3","proxypool4","proxywallet","miner");
	
	foreach($checks as $check){
			$poolinfo_string .= trim(`/opt/ethos/sbin/ethos-readconf $check`);
	}
	
	$poolinfo_md5_prior = trim(@file_get_contents("/var/run/ethos/poolinfo.md5"));
	$poolinfo_md5_current = md5($poolinfo_string);
	file_put_contents("/var/run/ethos/poolinfo.md5", $poolinfo_md5_current);
	if (trim($poolinfo_md5_prior) != "" && $poolinfo_md5_prior != $poolinfo_md5_current) {
		`/opt/ethos/bin/minestop`;
		`echo "" | tee /var/run/miner.output > /tmp/minercmd`;
		foreach (range(0,16) as $outnum) {
			`echo "" | tee /var/run/miner.output.$outnum`;
		}
		return false;
	}
}

function start_miner()
{
	$miner = trim(`/opt/ethos/sbin/ethos-readconf miner`);
	$mine_with = "";
	
	check_miner();
	$status = check_status();

	if (!$status) {
			return false;
	}
	/*******************************
	*  COMMON STARTUP VARS
	********************************/
	$driver = trim(`/opt/ethos/sbin/ethos-readconf driver`);
	$flags = trim(`/opt/ethos/sbin/ethos-readconf flags`);
	$extraflags = ""; // no extra flags by default
	$hostname = trim(`cat /etc/hostname`);
	$poolpass1 = trim(shell_exec("/opt/ethos/sbin/ethos-readconf poolpass1"));
	$poolpass2 = trim(shell_exec("/opt/ethos/sbin/ethos-readconf poolpass2"));
	$poolpass3 = trim(shell_exec("/opt/ethos/sbin/ethos-readconf poolpass3"));
	$poolpass4 = trim(shell_exec("/opt/ethos/sbin/ethos-readconf poolpass4"));
	$poolpass_arr = array($poolpass1, $poolpass2, $poolpass3, $poolpass4);
	$proxywallet = trim(`/opt/ethos/sbin/ethos-readconf proxywallet`);
	$poolarr = setup_pools($miner);
	list ($proxypool1, $proxypool2, $proxypool3, $proxypool4) = $poolarr;
	$poolemail = trim(`/opt/ethos/sbin/ethos-readconf poolemail`);
	$gpus = trim(file_get_contents("/var/run/ethos/gpucount.file"));
	$stratumtype = trim(`/opt/ethos/sbin/ethos-readconf stratumenabled`);
	
	if ($poolpass1 == "") {
		$poolpass1 = "x";
	}

	if ($poolpass2 == "") {
		$poolpass2 = "x";
	}

	//setup the worker name, and manage pool exceptions
	$worker = trim(`/opt/ethos/sbin/ethos-readconf worker`);
	$worker = trim(preg_replace("/[^a-zA-Z0-9]+/", "", $worker));
	$dworker = trim(`/opt/ethos/sbin/ethos-readconf worker`); // only used for claymore dualminer, as it may have eth on nanopool and dualminer pool on a different pool.
	$dworker = trim(preg_replace("/[^a-zA-Z0-9]+/", "", $dworker));
	$namedisabled = trim(`/opt/ethos/sbin/ethos-readconf namedisabled`);

	if (!preg_match("/(ethminer|progpowminer|claymore(\-legacy)?\z)/",$miner)) {
		$worker = "." . $worker;
	}

	if ($namedisabled == "disabled") {
		$worker = "";
	}

	if ($worker != "") {
		if (preg_match("/dwarfpool.com/",$proxypool1) || preg_match("/dwarfpool.com/",$proxypool2)){
			if($miner == "ccminer" || $miner == "sgminer-gm-xmr" || $miner == "claymore-xmr" ){
				$worker = trim(preg_replace("([a-zA-Z])", "1", $worker));
			}
		}
	
   		if (($poolemail != "") && (!preg_match("/ethminer/",$miner)) && (preg_match("/(ethosdistro.com|nanopool.org)/",$proxypool1) || preg_match("/(ethosdistro.com|nanopool.org)/",$proxypool2))) {
			$worker .= "/" . $poolemail;
   		}
  	}

	/*******************************
	*  DSTM-ZCASH
	********************************/
	if ($miner == "dstm-zcash") {
		
		$externalapi = trim(`/opt/ethos/sbin/ethos-readconf externalapi`);
		$api = "--telemetry 127.0.0.1:2222";

		if ($externalapi == "enabled" ){
			$api = "--telemetry 0.0.0.0:2222";
		}

		$devices = implode(",",select_gpus());
		if(trim(`/opt/ethos/sbin/ethos-readconf selectedgpus`) != "") {
			$mine_with = "-dev $devices";
		}

		preg_match("/(.*):(\d+)/", $proxypool1, $dstm_pool1);
		$pools = "--server " . $dstm_pool1['1'] . " --port " .  $dstm_pool1['2'] . " --user $proxywallet$worker --pass $poolpass1 ";

		if ($proxypool2 != "") {
			preg_match("/(.*):(\d+)/", $proxypool2, $dstm_pool2);
			$pools .= " --pool=" . $dstm_pool2['1'] . "," .  $dstm_pool2['2'] . "," . $proxywallet . $worker . "," . $poolpass2 . " ";
		}

		if ($proxypool3 != "") {
			preg_match("/(.*):(\d+)/", $proxypool3, $dstm_pool3);
			$pools .= " --pool=" . $dstm_pool3['1'] . "," .  $dstm_pool3['2'] . "," . $proxywallet . $worker . "," . $poolpass3 . " ";
		}

		if ($proxypool4 != "") {
			preg_match("/(.*):(\d+)/", $proxypool4, $dstm_pool4);
			$pools .= " --pool=" . $dstm_pool4['1'] . "," .  $dstm_pool4['2'] . "," . $proxywallet . $worker . "," . $poolpass4 . " ";
		}
	
	}

	/*******************************
	*  ETHMINER/ETHMINER-SINGLE/PROGPOWMINER/PROGPOWMINER-SINGLE
	********************************/
	if (preg_match("/(ethminer|ethminer-single|progpowminer|progpowminer-single)/",$miner)) {

		$gpumode = trim(`/opt/ethos/sbin/ethos-readconf gpumode`);
		$pool = trim(`/opt/ethos/sbin/ethos-readconf fullpool`);
		if ($flags == "") { 
			$flags = "--farm-recheck 200"; 
		}
		// accomodate CLI format change from 0.14.x to 0.16.x without breaking rigs
		if (preg_match("/dag-load-mode sequential/", $flags)) {
			$flags = str_replace("dag-load-mode sequential", "dag-load-mode 1", $flags);
		}
		// add --dag-load-mode 1 if its not in flags
		if (!preg_match("/dag-load-mode/", $flags)) {
			$flags .= " --dag-load-mode 1 ";
		}
		// api port needed for hash gather
		if (!preg_match("/api-port/", $flags) && (preg_match("/(ethminer-single|progpowminer-single)/",$miner))) {
			$flags .= " --api-port -3333 ";
		}
		
		if (!preg_match("/display-interval/", $flags)) {
			$flags .= " --display-interval 1 ";
		}
		
		if (!preg_match("/cl-global-work/", $flags) && ($driver == "amdgpu" || $driver == "fglrx" )) {
			$flags .= " --cl-global-work 8192 ";
		}
		
		if (!preg_match("/cuda-parallel-hash/", $flags) && $driver == "nvidia") {
			$flags .= " --cuda-parallel-hash 4 ";
		}
		
		if ($gpumode != "-G" || $gpumode != "-U") {
			if ($driver == "nvidia") {
				$gpumode = "-U";
			}

			if ($driver == "fglrx" || $driver == "amdgpu") {
				$gpumode = "-G";
			}
		}

		if ($driver == "nvidia" && $gpumode == "-U") {
			$selecteddevicetype = "--cuda-devices";
		} else {
			$selecteddevicetype = "--opencl-devices";
			// this function is not currently working, will re-enable when ready
			//$extraflags = check_apu();
		}
		if (preg_match("/(ethminer-single|progpowminer-single)/",$miner)){
			$devices = implode(",",select_gpus());
			if(trim(`/opt/ethos/sbin/ethos-readconf selectedgpus`) != "") {
				$mine_with = $selecteddevicetype . " " . $devices;
			}
		}

		switch ($stratumtype) {
		// parallel proxy
			/*case "enabled":
				if (preg_match("/(ethminer)/",$miner)){
					stratum_phoenix();
					$pools = " -P http://127.0.0.1:8080/$worker";
					break;
				}*/
				
			//coinotron - used with claymore
			case "coinotron":
				//if getwork pool is not empty, process as usual, otherwise fallback to stratum+tcp:// below
				if (trim($pool) != "") {
					$pools = " -P http://".str_replace("WORKER", $worker, $pool);
					break;
				}

			//disable proxy because of ethminer segfault bug in their getwork client
			case "enabled":
			// genoil proxy
			case "miner":
			case "nicehash":
				if ($worker != "") {
					$worker = "." . $worker;
				}

				$pools = "";

				foreach($poolarr as $p) {
					if ($p != "") {
						if (preg_match("/^(.+)\:\/\/(.+)$/", $p, $poolparts)) {
							$pool_string = $poolparts[1]."://".$proxywallet.$worker."@".$poolparts[2];
						}
						else {
							$pool_string = (($stratumtype == "nicehash") ? "stratum2" : "stratum" )."+tcp://".$proxywallet.$worker."@".$p;
						}

						if (($poolemail != "") && (preg_match("/(ethosdistro.com|nanopool.org)/",$p))) {
							$pool_string .= "/".$poolemail;
						}

						$pools .= " -P ".$pool_string;
					}
				}
				break;

			// getwork
			default:
				$pools = " -P http://".str_replace("WORKER", $worker, $pool);
		}

		//$extraflags .= $pool2 . " " . $pool3 . " " . $pool4;
		$config_string = $gpumode . " " . $pools . " " . $flags . " " . $extraflags . " ";
	}
	
	/*******************************
	*  CCMINER/NEVERMORE
	********************************/
	if (preg_match("/(ccminer|nevermore)/",$miner)) {
		
		$devices = implode(",",select_gpus());
		if(trim(`/opt/ethos/sbin/ethos-readconf selectedgpus`) != "") {
			$mine_with = "-d $devices";
		}

		if($miner == "ccminer" && !preg_match("/-a/",$flags)) {
			$flags .= " -a monero ";
		}

		if($miner == "nevermore" && !preg_match("/-a/",$flags)) {
			$flags .= " -a x16r ";

		}

		$pools="-o $proxypool1 -u $proxywallet$worker -p $poolpass1 ";
		if($proxypool2 != ""){
			$pools .= " -o $proxypool2 -u $proxywallet$worker -p $poolpass2 ";
		}
	}
	
	/*******************************
	*  AVERMORE
	********************************/
	if (preg_match("/avermore/",$miner)) {
		$devices = implode(",",select_gpus());
		if(trim(`/opt/ethos/sbin/ethos-readconf selectedgpus`) != ""){
			$mine_with = "-d $devices";
		}

		$maxtemp = trim(shell_exec("/opt/ethos/sbin/ethos-readconf maxtemp"));
		if ($maxtemp == "") {
			$maxtemp = "85";
		}
		
		//algorithm default to x16r
		if(!preg_match("/(-k|--algorithm|--kernel)/",$config_string)) {
			$config_string .= " -k x16r";
		}

		//worksize default to 256
		if(!preg_match("/(-w|--worksize)/",$config_string)) {
			$config_string .= " -w 256";
		}

		//intensity default to XI 256
		if(!preg_match("/(-I|--intensity|-X|--xintensity|--rawintensity)/",$config_string)) {
			$config_string .= " -X 256";
		}

		//gpu-threads default to 2
		if(!preg_match("/(-g|--gpu-threads)/",$config_string)) {
			$config_string .= " -g 2";
		}

		//api config
		if(!preg_match("/--api-listen/",$config_string)) {
				$api_config .= " --api-listen";
		}

		if(!preg_match("/--api-allow W\\:127.0.0.1/",$config_string)) {
				$api_config .= " --api-allow W:127.0.0.1";
		}

		if(!preg_match("/--api-port/",$config_string)) {
				$api_config .= " --api-port 4028";
		}
		else {
				$api_config = preg_replace("/--api-port \d+/", "--api-port $apiport", $api_config);
		}
		
		//pools config
		$pools = " -o $proxypool1 -u $proxywallet$worker -p $poolpass1 ";

		if($proxypool2 != "") {
			$pools .= " -o $proxypool2 -u $proxywallet$worker -p $poolpass2 ";
		}

		if($proxypool3 != "") {
			$pools .= " -o $proxypool3 -u $proxywallet$worker -p $poolpass3 ";
		}

		if($proxypool4 != "") {
			$pools .= " -o $proxypool4 -u $proxywallet$worker -p $poolpass4 ";
		}
		
		//overheat prevention
		if(!preg_match("/--temp-cutoff/",$config_string)) {
			$config_string .= " --temp-cutoff $maxtemp";
		}

		if(!preg_match("/--temp-overheat/",$config_string)) {
			$config_string .= " --temp-overheat $maxtemp";
		}
		
		$config_string = "$config_string $pools $api_config $mine_with";
	}
	
	/*******************************
	*  CGMINER-SKEIN/SGMINER-GM/SGMINER-GM-XMR
	********************************/
	if (preg_match("/(sgminer|cgminer)/",$miner)) {
		$devices = implode(",", select_gpus());
		if(trim(`/opt/ethos/sbin/ethos-readconf selectedgpus`) != "") {
			$mine_with = "-d $devices";
		}

		$maxtemp = trim(shell_exec("/opt/ethos/sbin/ethos-readconf maxtemp"));
		if ($maxtemp == "") {
			$maxtemp = "85";
		}

		if($miner == "sgminer-gm") {
			$config_string = file_get_contents("/home/ethos/sgminer.stub.conf");
		} else {
			$config_string = file_get_contents("/home/ethos/".$miner.".stub.conf");
		}

		if ($driver == "amdgpu") {
			$config_string = preg_replace("/ethash\"/", "ethash-new\"", $config_string);
		}

		$config_string = str_replace(".WORKER",$worker,$config_string);
		$config_string = str_replace("POOL1",$proxypool1,$config_string);
		$config_string = str_replace("POOL2",$proxypool2,$config_string);
		$config_string = str_replace("WALLET",$proxywallet,$config_string);
		$config_string = str_replace("PASSWORD1",$poolpass1,$config_string);
		$config_string = str_replace("PASSWORD2",$poolpass2,$config_string);
		$config_string = str_replace("MAXTEMP",$maxtemp,$config_string);
		file_put_contents("/var/run/ethos/sgminer.conf",$config_string);
	}
	
	/*******************************
	* CLAYMORE COMMON
	********************************/
	if (preg_match("/claymore/",$miner)){
		// import legacy stub -> flags configuration for remote conf users first.
		$stubprefix = trim(@file_get_contents("/home/ethos/$miner.flags"));
		$config_string = trim(`/opt/ethos/sbin/ethos-readconf flags`);
		
		$mining_devices = select_gpus();

		if(count($mining_devices) > 0) {
			foreach($mining_devices as $i) {
				$device_array[$i]= dechex($i);
			}
		}

		$devices = implode("", $device_array);

		if(trim(`/opt/ethos/sbin/ethos-readconf selectedgpus`) != ""){
			$mine_with = "-di $devices -altnum 2 ";
		}

		$maxtemp = trim(shell_exec("/opt/ethos/sbin/ethos-readconf maxtemp"));
		if ($maxtemp == "") {
			$maxtemp = "85";
		}
		
	}
	    
	/*******************************
	* CLAYMORE DUALMINER (ETH)
	********************************/
	if ($miner == "claymore" || $miner == "claymore-legacy") {
		$dualminer_status = (trim(`/opt/ethos/sbin/ethos-readconf dualminer`));

		if(!preg_match("/-esm/",$config_string)) {
			if ($stratumtype == "nicehash") {
				$config_string .= " -esm 3 ";
			} elseif ($stratumtype == "coinotron" ) {
				$config_string .= " -esm 2 ";
			} else {
				$config_string .= " -esm 0 ";
			}
		}

		if ($worker != "") {
			$config_string .= " -eworker " . $worker . " ";
		}

		if(!preg_match("/-dbg/",$config_string)) {
			$config_string .= " -dbg -1 ";
		}

		if(!preg_match("/-wd/",$config_string)) {
			$config_string .= " -wd 0 ";
		}

		if(!preg_match("/-colors/",$config_string)) {
			$config_string .= " -colors 0 ";
		}

		if(!preg_match("/-allcoins/",$config_string)) {
			$config_string .= " -allcoins 1 ";
		}

		if(!preg_match("/-allpools/",$config_string)) {
			$config_string .= " -allpools 1 ";
		}

		if(!preg_match("/-gser/",$config_string)) {
			$config_string .= " -gser 2 ";
		}

		// resume normal good stuff
		$pools = " -epool $proxypool1 -ewal $proxywallet -epsw $poolpass1 ";
		if($proxypool2 != "") {
			$pools .= " -epool $proxypool2 -ewal $proxywallet -epsw $poolpass2 ";
		}

		if($proxypool3 != "") {
			$pools .= " -epool $proxypool3 -ewal $proxywallet -epsw $poolpass3 ";
		}

		if($proxypool4 != ""){
			$pools .= " -epool $proxypool4 -ewal $proxywallet -epsw $poolpass4 ";
		}

		if ($dualminer_status == "enabled" ){
			$dualminerpool = (trim(`/opt/ethos/sbin/ethos-readconf dualminer-pool`));
			$dualminercoin = (trim(`/opt/ethos/sbin/ethos-readconf dualminer-coin`));
			$dualminerwallet = (trim(`/opt/ethos/sbin/ethos-readconf dualminer-wallet`));
			$dualminerpoolpass = (trim(`/opt/ethos/sbin/ethos-readconf dualminer-poolpass`));
			// workaround for the fact that dualminer uses -eworker for ethereum worker name, but not for dualminer worker.

			if ($namedisabled != "disabled"){
				$dualminerworker = "." . $dworker;
				if (($poolemail != "") && (preg_match("/(ethosdistro.com|nanopool.org)/",$dualminerpool))) {
					$dualminerworker = "." . $dworker . "/" . $poolemail;
				}
			}

			$config_string .= " -dcoin $dualminercoin -dwal $dualminerwallet$dualminerworker -dpool $dualminerpool ";
			
			if(!preg_match("/-dpsw/",$config_string)) {
				if ($dualminerpoolpass != "") {
					$config_string .= " -dpsw $dualminerpoolpass ";
				} else {
					$config_string .= " -dpsw x ";
				}
			}
		}

		$config_string = "$stubprefix $config_string $pools $mine_with";
	}

	/*******************************
	* CLAYMORE-XMR
	********************************/
	if ($miner == "claymore-xmr"){
		$flags .= " -dbg -1 -wd 0 ";

		$pools = " -xpool $proxypool1 -xwal $proxywallet$worker -xpsw $poolpass1 ";
		if($proxypool2 != "") {
			$pools .= " -xpool $proxypool2 -xwal $proxywallet$worker -xpsw $poolpass2 ";
		}

		if($proxypool3 != "") {
			$pools .= " -xpool $proxypool3 -xwal $proxywallet$worker -xpsw $poolpass3 ";
		}

		if($proxypool4 != "") {
			$pools .= " -xpool $proxypool4 -xwal $proxywallet$worker -xpsw $poolpass4 ";
		}
	}

	/*******************************
	* CLAYMORE-ZCASH
	********************************/
	if ($miner == "claymore-zcash") {
		if(!preg_match("/-dbg/",$config_string)){
			$config_string .= " -dbg -1 ";
		}

		if(!preg_match("/-wd/",$config_string)){
			$config_string .= " -wd 0 ";
		}

		if(!preg_match("/-colors/",$config_string)){
			$config_string .= " -colors 0 ";
		}

		if(!preg_match("/-allpools/",$config_string)){
			$config_string .= " -allpools 1 ";
		}

		$pools = " -zpool $proxypool1 -zwal $proxywallet$worker -zpsw $poolpass1 ";
		if($proxypool2 != "") {
			$pools .= " -zpool $proxypool2 -zwal $proxywallet$worker -zpsw $poolpass2 ";
		}
		if($proxypool3 != "") {
			$pools .= " -zpool $proxypool3 -zwal $proxywallet$worker -zpsw $poolpass3 ";
		}
		if($proxypool4 != "") {
			$pools .= " -zpool $proxypool4 -zwal $proxywallet$worker -zpsw $poolpass4 ";
		}
		$config_string = "$stubprefix $config_string $pools $mine_with";
	}

	/*******************************
	* EWBF-ZCASH/EWBF-EQUIHASH
	********************************/
	if (preg_match("/ewbf-(zcash|equihash)/", $miner)) {
		delete_old_api_port();
		$apiport = select_api_port();
		$devices = implode(" ",select_gpus());

		$config_string = file_get_contents("/opt/ethos/etc/".$miner.".conf");
		$config_string = str_replace("DEVICES", $devices, $config_string);
		$config_string = str_replace("APIPORT", $apiport, $config_string);

		if (($maxtemp = trim(shell_exec("/opt/ethos/sbin/ethos-readconf maxtemp"))) == "") {
			$maxtemp = "85";
		}

		$config_string = str_replace("MAXTEMP",$maxtemp,$config_string);

		//new flags with ewbf-equihash
		if ($miner == "ewbf-equihash") {
			//get algo
		  if(preg_match("/--algo (\S*).*/", $flags, $matches)) {
		  	$algo = $matches[1];
	  	} else {
		    $algo = "192_7";
	  	}

	  	//get equihash pow string
			if(preg_match("/--pers (\S*).*/", $flags, $matches)) {
		  	$pers = $matches[1];
	  	} else {
				switch ($algo) {
					case "aion":
						$pers = "AION0PoW";
						break;

					case "zhash":
						$pers = "BitcoinZ";
						break;

					default:
						$pers = "ZERO_PoW";
				}
	  	}

			$config_string = str_replace("ALGO_STRING", $algo, $config_string);
			$config_string = str_replace("PERS_STRING", $pers, $config_string);
		}

		for ($i = 1; $i <= 4; $i++){
			if(${'proxypool'.$i} != "") {
				preg_match("/(.*):(\d+)/", ${'proxypool'.$i}, $pool_split);
				$config_string = $config_string . "\n[server]\nserver " . $pool_split[1] . "\nport " . $pool_split[2] . "\nuser " . $proxywallet . $worker . "\npass " . ${'poolpass'.$i} . "\n";
			}
		}

		file_put_contents("/var/run/ethos/".$miner.".conf", $config_string);
	}

	/*******************************
	* OPTIMINER-ZCASH
	********************************/
	if ($miner == "optiminer-zcash") {
		$optiminerversion = trim(`grep -Poi "(?<=optiminer-zcash v)(.*)" /var/run/ethos/miner.versions`);

		$devices = implode(" -d ",select_gpus());
		$extraflags = trim(`/opt/ethos/sbin/ethos-readconf flags`);
		$mine_with = "-d $devices";
		
		if(($optiminerversion != "1.7.0") && (!preg_match("/-a/",$extraflags))){
			$extraflags .= " -a equihash200_9 ";
		}
	}

	/*******************************
	* XMR-STAK/XTL-STAK
	********************************/
	if (preg_match("/(xmr|xtl)-stak/",$miner)) {
		/*
		$devices = implode(",",select_gpus());
		if(trim(`/opt/ethos/sbin/ethos-readconf selectedgpus`)){
			$mine_with = "-d $devices";
		}*/

		if(!preg_match("/--currency/",$flags)) {
			$flags .= " --currency cryptonight_v8 ";
		}

		if(!preg_match("/--cpu/",$flags)) {
			$flags .= " --noCPU ";
		}

		$tworker = trim($worker, ". ");
		if ($tworker != "") {
			$tworker = "-r ".$tworker;
		}

		$pools="-o $proxypool1 -u $proxywallet $tworker -p $poolpass1 ";

		if($proxypool2 != "") {
			$pools .= " -o $proxypool2 -u $proxywallet $tworker -p $poolpass2 ";
		}

		//delete cache files and copy default configs
		shell_exec("rm /var/run/ethos/".$miner."*.txt");
		shell_exec("su - ethos -c \"cp /opt/ethos/etc/xmr-stak-config.txt /var/run/ethos/$miner-config.txt\"");
		shell_exec("su - ethos -c \"cp /opt/ethos/etc/xmr-stak-pools.txt /var/run/ethos/$miner-pools.txt\"");

		if ($driver == "nvidia") {
			$stakgpu = " --noAMD ";

		 	if (!preg_match("/--nvidia/",$flags)) {
   			$stakgpu .= "--nvidia /var/run/ethos/".$miner."-nvidia.txt ";
   		}
		}
	 	else {
	 		$stakgpu = " --noNVIDIA ";

	 		if(!preg_match("/--amd/",$flags)) {
	 			$stakgpu .= "--amd /var/run/ethos/".$miner."-amd.txt ";
	 		}
	 	}

		if (!preg_match("/(-C | --poolconf )/",$flags)){
			$stakpoolconf = " --poolconf /var/run/ethos/".$miner."-pools.txt";
		}

		if (!preg_match("/(-c | --config )/",$flags)){
			$stakconf = "--config /var/run/ethos/".$miner."-config.txt";
		}

		$config_string = $stakconf . $stakpoolconf . $stakgpu;

	}

	/*******************************
	* TEAMREDMINER
	********************************/
	if ($miner == "teamredminer") {
		$devices = implode(",",select_gpus());
		if(trim(`/opt/ethos/sbin/ethos-readconf selectedgpus`) != "") {
			$mine_with = "-d $devices";
		}

		if(!preg_match("/(-a[\s]|--algo)/",$flags)) {
			$flags .= " --algo cnr ";
		}

		$pools="-o $proxypool1 -u $proxywallet$worker -p $poolpass1 ";

		if($proxypool2 != "") {
			$pools .= " -o $proxypool2 -u $proxywallet$worker -p $poolpass2 ";
		}
		
		$extraflags = "--api_listen 4028 --bus_reorder";
	}

	/*******************************
	*  WOLF-XMR-CPU
	********************************/
	
	if ($miner == "wolf-xmr-cpu") {
		$threads = trim(`/opt/ethos/sbin/ethos-readconf flags`);
		if ($threads == "") {
			$threads = trim(`nproc`);
		}
	}
			    
	/*******************************
	* LOLMINER
	********************************/
	if ($miner == "lolminer") {
		$devices = select_gpus();
		$apiport = select_api_port();
		$lolconfig_path = "/var/run/ethos/lolminer_config.json";

		$lolconfig = array(
			"DEFAULTS"=>array(
				"DEVICES"=>(($driver == "nvidia") ? "NVIDIA" : "AMD" ),
				"APIPORT"=>$apiport
			),
			"ETHOS"=>array(
				"COIN"=>"ZER",
				"POOLS"=>array()
			)
		);

		if(trim(`/opt/ethos/sbin/ethos-readconf selectedgpus`) != "") {
			foreach($devices as $idx=>$dev) {
				$devices[$idx] = intval($dev);
			}

			$lolconfig["DEFAULTS"]["DEVICES"] = $devices;
		}

		//read coin from flags and remove option from flags
		if(preg_match("/(-coin=(\S+)).*/", $flags, $coin_matches)) {
			$lolconfig["ETHOS"]["COIN"] = $coin_matches[2];
			$flags = str_replace($coin_matches[1], "", $flags);
		}

		//set pool array
		foreach($poolarr as $poolidx=>$pool) {
			if ($pool != "") {
				$poolpass = $poolpass_arr[$poolidx];

				if(preg_match("/^(.+\:\/\/)?(.+)\:(\d+)$/", $pool, $pool_matches)) {
					$lolconfig["ETHOS"]["POOLS"][] = array(
						"POOL"=>$pool_matches[2],
						"PORT"=>$pool_matches[3],
						"USER"=>$proxywallet.$worker,
						"PASS"=>(($poolpass == "") ? "x" : $poolpass)
					);
				}
			}
		}

		//output json
		file_put_contents($lolconfig_path, json_encode($lolconfig, JSON_PRETTY_PRINT));

		//remove -profile option from flags and force our own
		if(preg_match("/(-profile=(\S+)).*/", $flags, $flag_matches)) {
			$flags = str_replace($flag_matches[1], "", $flags);
		}

		//remove -usercfg option from flags and force our own
		if(preg_match("/(-usercfg=(\S+)).*/", $flags, $flag_matches)) {
			$flags = str_replace($flag_matches[1], "", $flags);
		}

		$flags = "-profile=ETHOS -usercfg=".$lolconfig_path." ".$flags;

	}

	//begin miner commandline buildup

	$miner_path['avermore'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.avermore -dmS avermore /opt/miners/avermore/avermore";
	$miner_path['dstm-zcash'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.dstm-zcash -l -L -dmS dstm-zcash /opt/miners/dstm-zcash/dstm-zcash";
	$miner_path['ccminer'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.ccminer -l -L -dmS ccminer /opt/miners/ccminer/ccminer";
	$miner_path['cgminer-skein'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.cgminer-skein -dmS cgminer-skein /opt/miners/cgminer-skein/cgminer-skein";		
	$miner_path['claymore'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.claymore -l -L -dmS claymore /opt/miners/claymore/claymore";
	$miner_path['claymore-xmr'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.claymore-xmr -l -L -dmS claymore-xmr /opt/miners/claymore-xmr/claymore-xmr";
	$miner_path['claymore-zcash'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.claymore-zcash -l -L -dmS claymore-zcash /opt/miners/claymore-zcash/claymore-zcash";
	$miner_path['claymore-legacy'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.claymore-legacy -l -L -dmS claymore-legacy /opt/miners/claymore-legacy/claymore-legacy";
	$miner_path['ethminer'] = "/opt/miners/ethminer/ethminer";
	$miner_path['ethminer-single'] = "/opt/miners/ethminer/ethminer-single";
	$miner_path['ewbf-zcash'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.ewbf-zcash -l -L -dmS ewbf-zcash /opt/miners/ewbf-zcash/ewbf-zcash";
 	$miner_path['nevermore'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.nevermore -l -L -dmS nevermore /opt/miners/nevermore/nevermore";
	$miner_path['optiminer-zcash'] = "/bin/bash -c \" cd /opt/miners/optiminer-zcash && /usr/bin/screen -c /opt/ethos/etc/screenrc -dmS optiminer /opt/miners/optiminer-zcash/optiminer-zcash";
	$miner_path['progpowminer'] = "/opt/miners/progpowminer/progpowminer";
	$miner_path['progpowminer-single'] = "/opt/miners/progpowminer/progpowminer-single";
	$miner_path['sgminer-gm'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.sgminer-gm -dmS sgminer /opt/miners/sgminer-gm/sgminer-gm";
	$miner_path['sgminer-gm-xmr'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.sgminer-gm-xmr -dmS sgminer /opt/miners/sgminer-gm/sgminer-gm-xmr";
	$miner_path['wolf-xmr-cpu'] = "/opt/miners/wolf-xmr-cpu/wolf-xmr-cpu";
	$miner_path['xmr-stak'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.xmr-stak -l -L -dmS xmr-stak /opt/miners/xmr-stak/xmr-stak";
	$miner_path['xtl-stak'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.xtl-stak -l -L -dmS xtl-stak /opt/miners/xtl-stak/xtl-stak";
	$miner_path['teamredminer'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.teamredminer -l -L -dmS teamredminer /opt/miners/teamredminer/teamredminer";
	$miner_path['ewbf-equihash'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.ewbf-equihash -l -L -dmS ewbf-equihash /opt/miners/ewbf-equihash/ewbf-equihash";
	$miner_path['lolminer'] = "/usr/bin/screen -c /opt/ethos/etc/screenrc.lolminer -l -L -dmS lolminer /opt/miners/lolminer/lolMiner";
		
			
	$start_miners = select_gpus();

	foreach($start_miners as $start_miner) {
		//if user disallowed before we reach this point, exit
    if(intval(trim(file_get_contents("/opt/ethos/etc/allow.file"))) == 0) {
    	break;
    }

		$miner_params['avermore'] = "$config_string";
		$miner_params['dstm-zcash'] = $api ." ". $flags ." ". $pools;
		$miner_params['ccminer'] = $flags ." ". $pools;
		$miner_params['cgminer-skein'] = "-c /var/run/ethos/sgminer.conf";
		$miner_params['claymore'] = "$config_string";
		$miner_params['claymore-xmr'] = "-allpools 1 " . $flags . " " . $pools;
		$miner_params['claymore-zcash'] = "$config_string";
		$miner_params['claymore-legacy'] = "$config_string";
		$miner_params['ethminer'] = $config_string . " " . $selecteddevicetype . " " . $start_miner;
		$miner_params['ethminer-single'] = $config_string;
		$miner_params['ewbf-zcash'] = "--config /var/run/ethos/ewbf-zcash.conf";
		$miner_params['nevermore'] = $flags ." ". $pools;
		$miner_params['sgminer-gm'] = "-c /var/run/ethos/sgminer.conf";
		$miner_params['sgminer-gm-xmr'] = "-c /var/run/ethos/sgminer.conf";
		$miner_params['optiminer-zcash'] = "-s $proxypool1 -u $proxywallet$worker -p $poolpass1 --log-file /var/run/miner.output";
		$miner_params['progpowminer'] =  $config_string . " " . $selecteddevicetype . " " . $start_miner;
		$miner_params['progpowminer-single'] = $config_string;
		$miner_params['wolf-xmr-cpu'] = "-o $proxypool1 -p $poolpass1 -u $proxywallet$worker -t $threads";
		$miner_params['xmr-stak'] = $flags ." ". $config_string ." ". $pools;
		$miner_params['xtl-stak'] = $flags ." ". $config_string ." ". $pools;
		$miner_params['teamredminer'] = $flags ." ". $pools;
		$miner_params['ewbf-equihash'] = "--config /var/run/ethos/ewbf-equihash.conf";
		$miner_params['lolminer'] = $flags;

		$miner_suffix['avermore'] = " " . $mine_with . " " . $extraflags;
		$miner_suffix['dstm-zcash'] = " " . $mine_with . " " . $extraflags;
		$miner_suffix['ccminer'] = " " . $mine_with . " " . $extraflags;
		$miner_suffix['cgminer-skein'] = " " . $mine_with . " " . $extraflags;
		$miner_suffix['claymore'] = " " . $extraflags;
		$miner_suffix['claymore-xmr'] = " " . $mine_with . " " . $extraflags;
		$miner_suffix['claymore-zcash'] = " " . $extraflags;
		$miner_suffix['claymore-legacy'] = " " . $extraflags;
		$miner_suffix['ethminer'] = " 2>&1 | /usr/bin/tee -a /var/run/miner.output >> /var/run/miner.$start_miner.output &";
		$miner_suffix['ethminer-single'] = " >> /var/run/miner.output 2>&1 &";
		$miner_suffix['ewbf-equihash'] = "";
		$miner_suffix['ewbf-zcash'] = "";
		$miner_suffix['sgminer-gm'] = " " . $mine_with . " " . $extraflags;
		$miner_suffix['sgminer-gm-xmr'] = " " . $mine_with ." ". $extraflags;
		$miner_suffix['optiminer-zcash'] = " " . $mine_with ." " . $extraflags ." \\\"";
		$miner_suffix['progpowminer'] = " 2>&1 | /usr/bin/tee -a /var/run/miner.output >> /var/run/miner.$start_miner.output &";
		$miner_suffix['progpowminer-single'] = " >> /var/run/miner.output 2>&1 &";
		$miner_suffix['wolf-xmr-cpu'] = " 2>&1 | /usr/bin/tee -a /var/run/miner.output &";
		$miner_suffix['xmr-stak'] = " " . $extraflags;
		$miner_suffix['xtl-stak'] = " " . $extraflags;
		$miner_suffix['teamredminer'] = " " . $mine_with . " " . $extraflags;
		$miner_suffix['lolminer'] = "";
		
		$command = "su - ethos -c \"" . escapeshellcmd($miner_path[$miner] . " " . $miner_params[$miner]) . " $miner_suffix[$miner]\"";
		$command = str_replace('\#',"#",$command);
		$command = str_replace('\&',"&",$command);
		if ($miner == "optiminer-zcash") {
			file_put_contents("/tmp/minercmd", "#!/bin/bash \n");
			file_put_contents("/tmp/minercmd", $command . "\n", FILE_APPEND);
		} else {
			file_put_contents("/tmp/minercmd", $command . "\n");
		}
		chmod("/tmp/minercmd", 0755);
		chdir('/tmp');

		`/tmp/minercmd`;

		// if($debug){ file_put_contents("/home/ethos/debug.log",$date . " " . $command . "\n"); 
		if (!preg_match("/(ethminer$|progpowminer?$)/",$miner)) {
			break;
		}

		//wait 10 secs before starting next instance, but check for disallow every 2 second
    for($i=0;$i<5;++$i) {
	    //if user set disallow, exit
 	    if (intval(trim(file_get_contents("/opt/ethos/etc/allow.file"))) == 0) {
	      return true;
	    }

	    sleep(2);
    }
	}

	return true;
}

?>
