<?php
class Connection {
	//every query is a method in $connection which can be related to any DBMS
	private $con;
	
	public function __construct($type, $data) {		
		$this->con = new $type($data);
	}

	public function escape($value) {
		return $this->con->escape($value);
	}
	
	/*
		Queries USER
	*/
	public function addUser($user, $pw, $email) {
		$key = hash("sha256", time().$pw, false);
		$pw  = hash("sha256", $pw, false);
		
		$error = 100;
		
		if(strlen($user) > 8 && strlen($pw) > 8 && strlen($email) > 5) {
			if($this->getUserAmount($user, $email) == 0) {
				$this->insUser($user, $pw, $email, $key);
				$this->insDashboard($user);
				$id = $this->addBuilding($user, "Main Building", 1);
				$this->addRoom($user, "Main Room", 0, 1);
			}
			else
				$error = 103;
		}
		else
			$error = 102;
		
		return $error;
	}
	
	public function delUser($key) {
		$user = $this->getUser($key);
		
		$b1  = $this->con->deluser($key);
		$b2  = $this->con->delDevices($user);
		$b3  = $this->con->delDashboard($user);
		$b4  = $this->con->delRooms($user);
		$b5  = $this->con->delBuildings($user);
		$b6  = $this->con->delTariffs($user);
		$b7  = $this->con->delProduction($user);
		$b8  = $this->con->delOccupancyForUser($user);
		$b9  = $this->con->delNotifications($user);
		$b10 = $this->con->delDeviceModelForUser($user);
		$b11 = $this->con->delConsumptionEvents($user);
		$b12 = $this->con->delAdvicesForUser($user);
		
		$circuits = $this->getCircuits($key);
		$b13 = $this->con->delCircuits($user, $circuits);
		
		if($b1 && $b2 && $b3 && $b4 && $b5 && $b6 && $b7 && $b8 && $b9 && $b10 && $b11 && $b12 && $b13)
			return true;
		else
			return false;
	}
	
	public function getUser($key) {
		return $this->con->getUser($key);
	}
	
	public function getKey($user) {
		return $this->con->getKey($user);
	}
	
	public function getPw($key) {
		return $this->con->getPw($key);
	}
	
	public function existsUser($user) {
		return $this->con->existsUser($user);
	}
	
	public function getTheme($key) {
		return $this->con->getTheme($key);
	}
	
	public function updTheme($key, $theme) {
		return $this->con->updTheme($key, $theme);
	}
	
	public function getPrivileges($key) {
		return $this->con->getPrivileges($key);
	}
	
	public function getPubData($user) {
		return $this->con->getPubData($user);
	}
	
	public function getPubPage($key) {
		return $this->con->getPubPage($key);
	}
	
	public function getPubPageByUser($user) {
		return $this->con->getPubPageByUser($user);
	}
	
	public function setPubPage($key, $value) {
		return $this->con->setPubPage($key, $value);
	}
	
	public function getUserAmount($user, $email) {
		return $this->con->getUserAmount($user, $email);
	}
	
	public function getUserAmountByPassword($user, $pw) {
		$pw = hash("sha256", $pw);
		return $this->con->getUserAmountByPassword($user, $pw);
	}
	
	public function insUser($user, $pw, $mail, $key) {
		return $this->con->insUser($user, $pw, $mail, $key);
	}
	
	public function isValidated($user) {
		return $this->con->isValidated($user);
	}
	
	public function insDashboard($user, $page=0, $widget="") {
		return $this->con->insDashboard($user, $page, $widget);
	}
	
	public function getDashboardID() {
		return $this->con->getDashboardID();
	}
	
	public function insNotKey($user, $key) {
		return $this->con->insNotKey($user, $key);
	}
	
	public function getNotKey($user) {
		return $this->con->getNotKey($user);
	}
	
	public function delNotKey($key) {
		return $this->con->delNotKey($key);
	}
	
	/*
		Queries ROOMS
	*/
	
	public function setDeviceRoom0($user, $room) {
		return $this->con->setDeviceRoom0($user, $room);
	}
	
	public function delRoom($user, $room) {
		return $this->con->delRoom($user, $room);
	}
	
	public function modifyRoom($id, $name, $type, $building, $def) {
		$b1 = $this->con->updRoomName($id, $name);
		$b2 = $this->con->updRoomType($id, $type);
		$b3 = $this->con->addRoomToBuilding($id, $building);
		$b4 = true;
		
		if($def == 1)
			$b4 = $this->updRoomSetDefault($id);
		
		if($b1 && $b2 && $b3 && $b4)
			return true;
		else
			return false;
	}
	
	public function addRoom($user, $name, $type, $def) {
		$id = hash("md2", $name.time(), false);
		
		//get default building
		$buildings = $this->con->getBuildings($user);
		$bId = "";
		for($i=0; $i<count($buildings); $i++) {
			if($buildings[$i][2] == 1)
				$bId = $buildings[$i][0];
		}
		
		$b1 = $this->con->addRoom($user, $id, $name, $type, $bId);
		$b2 = true;
		
		if($def == 1 || $def == "1")
			$b2 = $this->updRoomSetDefault($id);
		
		if($b1 && $b2)
			return $id;
		else
			return 0;
	}
	
	public function updRoomSetDefault($id) {
		$room = $this->getRoom($id);
		
		return $this->con->updRoomSetDefault($id, $room['username']);
	}
	
	public function getRooms($user) {
		return $this->con->getRooms($user);
	}
	
	public function getRoom($id) {
		return $this->con->getRoom($id);
	}
	
	/*
		Queries CIRCUITS
	*/
	
	public function addCircuit($key, $name) {
		$id = hash("md2", $key.time(), false);
		
		return $this->con->addCircuit($id, $key, $name);
	}
	
	public function modifyCircuit($id, $t, $v) {
		return $this->con->modifyCircuit($id, $t, $v);
	}
	
	public function getCircuits($key) {
		return $this->con->getCircuits($key);
	}
	
	public function getCircuitPower($c) {
		return $this->con->getCircuitPower($c);
	}
	
	public function getPowAvrg($c, $t) {
		/*
			c ... circuit id
			t ... timestamp of interval
		*/
	
		$data = $this->con->getPowAvrg($c, $t);
		
		$sum = 0;
		
		if($data != 0) {
			for($i=0; $i<count($data); $i++) {
				$sum += $data[$i][1];
			}
			
			$sum = $sum/count($data);
			
			return $sum;
		}
		else
			return 0;
		
	}
	
	public function existsCircuit($id) {
		return $this->con->existsCircuit($id);
	}
	
	public function insCircuitPower($id, $time, $power) {
		
		$h   = date("H", $time);
		$s   = date("s", $time);
		$min = date("i", $time);
		
		if($s==0 && $min==0) {
			$this->con->updPowerMoAverage($id, $h);
		}
		
		return $this->con->insCircuitPower($id, $time, $power);
	}
	
	public function updPowerMoAverage($id, $h) {
		return $this->con->updPowerMoAverage($id, $h);
	}
	
	public function delCircuit($id) {
		$b1 = $this->con->delCircuitPower($id);
		$b2 = $this->con->delCircuit($id);
		
		if($b1 && $b2)
			return true;
		else
			return false;
	}
	
	public function getCircuitAvrgData($id) {
		return $this->con->getCircuitAvrgData($id);
	}
	
	/*
		queries BUILDINGS
	*/
	
	public function addBuilding($user, $name, $def) {
		$id = hash("md2", time().$name, false);	
		
		$b1 = $this->con->addBuilding($id, $user, $name);
		$b2 = true;
		
		if($def == "1" || $def == 1)
			$b2 = $this->con->updBuildingSetDefault($id, $user);
		
		if($b1 && $b2) {
			return $id;
		}
		else
			return 0;
	}
	
	public function delBuilding($id) {
		return $this->con->delBuilding($id);
	}
	
	public function addRoomToBuilding($rId, $bId) {
		return $this->con->addRoomToBuilding($rId, $bId);
	}
	
	public function getBuildings($user) {
		return $this->con->getBuildings($user);
	}
	
	public function getBuilding($id) {
		return $this->con->getBuilding($id);
	}
	
	public function updBuilding($id, $name, $def) {
		$building = $this->getBuilding($id);
		
		$this->con->updBuildingSetDefault($id, $building["username"]);
		
		return $this->con->updBuilding($id, $name);
	}
	
	public function getEventsAll() {
		return $this->con->getEventsAll();
	}
	
	public function getLastEvent($user) {
		return $this->con->getLastEvent($user);
	}
	
	public function getEventsByUserAndTime($user, $t, $get) {
		$data = $this->con->getEventsByUserAndTime($user, $t);
		
		switch($get) {
			case "sum":
				return $data[0];
				break;
			case "lastData":
				return $data[1];
				break;
			case "n":
				return $data[2];
				break;
			case "max":
				return $data[3];
				break;
			case "events":
				return $data[4];
				break;
		}
	}
	
	public function getEventsByOwner($user) {
		return $this->con->getEventsByOwner($user);
	}
	
	public function getEventRange($user) {
		return $this->con->getEventRange($user);
	}
	
	public function getAggData($user) {
		return $this->con->getAggData($user);
	}
	
	public function getLastAggData($user, $value) {
		$result = $this->con->getLastAggData($user);
		
		switch($value) {
			case "p":
				return $result[0];
				break;
			case "c":
				return $result[1];
				break;
			case "l":
				return $result[2];
				break;
		}
	}
	
	public function delOccupancy() {
		return $this->con->delOccupancy();
	}
	
	public function writeOccupancy($user, $dev, $p, $data) {
		return $this->con->writeOccupancy($user, $dev, $p, $data);
	}
	
	public function getOccupancy($user, $mode) {
		return $this->con->getOccupancy($user, $mode);
	}
	
	/*
		Queries DEVICES
	*/
	
	public function getDevice($dev) {
		return $this->con->getDevice($dev);
	}
	
	public function delDevice($dev) {
		return $this->con->delDevice($dev);
	}
	
	public function addDevice($owner, $dev, $name) {
		//get default room
		$rooms = $this->con->getRooms($owner);
		$def = "";
		
		for($i=0; $i<count($rooms); $i++) {
			if($rooms[$i][4] == 1)
				$def = $rooms[$i][0];
		}
		
		return $this->con->addDevice($owner, $dev, $name, $def);
	}
	
	public function getDevicesByCategory($cat) {
		return $this->con->getDevicesByCategory($cat);
	}
	
	public function getDevicesAll() {
		return $this->con->getDevicesAll();
	}
	
	public function getDevicesUserdriven() {
		return $this->con->getDevicesUserdriven();
	}
	
	public function getDevicesOwner() {
		return $this->con->getDevicesOwner();
	}
	
	public function getCreditByDevice($user, $device) {
		return $this->con->getCreditByDevice($user, $device);
	}
	
	public function updCredits($user, $dev, $c) {
		return $this->con->updCredits($user, $dev, $c);
	}
	
	public function modifyDevice($user, $dev, $name, $cat, $room) {	
		$b1 = $this->con->updDeviceName($user, $dev, $name);
		$b2 = $this->con->updDeviceCat($user, $dev, $cat);
		$b3 = $this->con->updDeviceRoom($user, $dev, $room);
		
		if($b1 && $b2 && $b3)
			return true;
		else
			return false;
	}
	
	public function renameDevice($user, $dev, $name) {
		return $this->con->updDeviceName($user, $dev, $name);
	}
	
	public function updDeviceCredits($user, $dev, $credits) {
		return $this->con->updDeviceCredits($user, $dev, $credits);
	}
	
	public function addCredits($user, $device, $c, $date) {
		return $this->con->addCredits($user, $device, $c, $date);
	}
	
	public function getCredits($user, $device) {
		return $this->con->getCredits($user, $device);
	}
	
	public function getDevices($user) {
		return $this->con->getDevices($user);
	}
	
	public function getDeviceName($dev) {
		return $this->con->getDeviceName($dev);
	}
	
	public function getCategories() {
		return $this->con->getCategories();
	}
	
	public function getCategorieTree() {
		return $this->con->getCategorieTree();
	}
	
	public function getEventsByDevice($dev) {
		return $this->con->getEventsByDevice($dev);
	}
	
	public function getEventsByDeviceOwner($dev, $user) {
		return $this->con->getEventsByDeviceOwner($dev, $user);
	}
	
	public function addEvent($user, $dev, $start, $dur, $con) {
	    $return_value = $this->con->addEvent($user, $dev, $start, $dur, $con);
	    return $return_value;
	}
	
	public function updAnalytics($cat, $n, $avrg) {
		$b1 = $this->con->updAnalyticsUsages($cat, $n);
		$b2 = $this->con->updAnalyticsAvrg($cat, $avrg);
	
		if($b1 && $b2)
			return true;
		else
			return false;
	}
	
	/*
		Queries TARIFFS
	*/
	
	public function getTariffData($user, $days) {
		$tariff = array();
		
		for($i=0;$i<count($days);$i++) {
			$day = $days[$i];
			$result = $this->con->getTariffData($user, $day);
			
			for($j=0;$j<count($result);$j++) {
				$k = count($tariff);
				
				$tariff[$k][0] = $result[$j][0];
				$tariff[$k][1] = $result[$j][1];
				$tariff[$k][2] = $result[$j][2];
			}
		}
		return $tariff;
	}
	
	public function getTariffDESC($user) {
		return $this->con->getTariffDESC($user);
	}
	
	public function getTariffGroup($user) {
		$data = $this->con->getTariffGroup($user);		
		return $data;
	}
	
	public function delTariffData($user) {
		return $this->con->delTariffData($user);
	}
	
	public function insTariffData($user, $from, $to, $fromtp, $totp, $tariff) {
		return $this->con->insTariffData($user, $from, $to, $fromtp, $totp, $tariff);
	}
	
	/*
		Queries WIDGETS
	*/
	
	public function getWidgets($user, $page) {
		return $this->con->getWidgets($user, $page);
	}
	
	public function getWidget($id) {
		return $this->con->getWidget($id);
	}
	
	public function delWidget($id) {
		return $this->con->delWidget($id);
	}
	
	public function updWidgetInput($id, $value) {
		return $this->con->updWidgetInput($id, $value);
	}
	
	public function isAggInstalled($user) {
		return $this->con->isAggInstalled($user);
	}
	
	public function addAggData($user, $timestamp, $consumption, $production) {
		return $this->con->insertAggData($user, $timestamp, $consumption, $production);
	}
	
	public function isDisInstalled($user) {
		return $this->con->isDisInstalled($user);
	}
	
	public function delOldEvents() {
		return $this->con->delOldEvents();
	}
	
	/*
		Queries DEVICE-MODEL
	*/
	
	public function delDeviceModel($dev) {
		return $this->con->delDeviceModel($dev);
	}
	
	public function insDeviceModel($user, $dev, $result) {
		return $this->con->insDeviceModel($user, $dev, $result);
	}
	
	public function getDeviceModel($user, $mode, $dev) {
		return $this->con->getDeviceModel($user, $mode, $dev);
	}
	
	/*
		Queries ADVICES
	*/
	
	public function getAdvices($user) {
		return $this->con->getAdvices($user);
	}
	
	public function updAdvice($user, $dev, $type, $data) {
		return $this->con->updAdvice($user, $dev, $type, $data);
	}
	
	public function delAdvices() {
		return $this->con->delAdvices();
	}
	
	public function voteAdvice($user, $type, $dev, $n) {
		return $this->con->voteAdvice($user, $type, $dev, $n);
	}
	
	/*
		Queries Advisor
	*/
	
	public function advisorQuery1($user) {
		return $this->con->advisorQuery1($user);
	}
	
	public function advisorQuery2($user) {		
		return $this->con->advisorQuery2($user);
	}
	
	public function advisorQuery3() {
		return $this->con->advisorQuery3();
	}
	
	/*
		Queries RT-PowerUsage
	*/
	
	public function rtPowerQuery1($user, $n) {
		$data = $this->con->rtPowerQuery1($user);
		
		switch($n) {
			case 1:
				return $data[0];
				break;
			case 2:
				return $data[1];
				break;
			case 3:
				return $data[2];
				break;
		}
	}
	
	/*
		Queries CAKE
	*/
	
	public function cakeQuery1($user, $t1, $t2) {
		return $this->con->cakeQuery1($user, $t1, $t2);
	}
	
	/*
		Queries WALLET
	*/
	
	public function getWallet($user, $t=0) {
		return $this->con->getWallet($user, $t);
	}
	
	public function insWallet($user, $device, $amount, $time) {
		return $this->con->insWallet($user, $device, $amount, $time);
	}
	
	/*
		Queries SWITCH
	*/
	
	public function switchQuery1($user, $day) {
		return $this->con->switchQuery1($user, $day);
	}
	
	/*
		Queries UploadEvent
	*/
	
	public function setMaxConsumption($user) {
		return $this->con->setMaxConsumption($user);
	}
	
	/*
		Queries TimeSeries
	*/
	
	public function delOldTimeSeriesData($t, $c) {
		return $this->con->delOldTimeSeriesData($t, $c);
	}
	
	/*
	 * Queries MeterData
	 */
	public function existsMeterData($authkey, $input) {
		return $this->con->existsMeterData($authkey, $input);
	}
	
	public function addMeterData($authkey, $input, $value, $timestamp) {
		return $this->con->addMeterData($authkey, $input, $value, $timestamp);
	}
	
	public function updMeterData($authkey, $input, $value, $timestamp) {
		return $this->con->updMeterData($authkey, $input, $value, $timestamp);
	}
	
	public function getMeterData($authkey, $output) {
		return $this->con->getMeterData($authkey, $output);
	}

    /*
        Connect-Functions of Challenge-Goal-Setting-Widget
        added by Wascher Manuel
    */

    /* old version */
    public function getTimesOfUnderraining($user) {
        return $this->con->getTimesOfUnderraining($user);
    }

    public function insertChallenge($user, $timestamp, $category, $challenge_begin, $challenge_end, $incentive, $challenge_decision, $max_devs_consumption, $cur_devs_consumption, $times_of_underraining, $challenge_finished) {
        return $this->con->insertChallenge($user, $timestamp, $category, $challenge_begin, $challenge_end, $incentive, $challenge_decision, $max_devs_consumption, $cur_devs_consumption, $times_of_underraining, $challenge_finished);
    }

    public function getNewIncentive($category) {
        return $this->con->getNewIncentive($category);
    }

    public function get_current_Incentive($user, $timestamp) {
        return $this->con->get_current_Incentive($user, $timestamp);
    }

    public function updateChDesFin($user, $timestamp, $column, $value){
        return $this->con->updateChDesFin($user, $timestamp, $column, $value);
    }

    public function isCTInside($user, $timestamp) {
        return $this->con->isCTInside($user, $timestamp);
    }

    public function getChTimestamp($user) {
        return $this->con->getChTimestamp($user);
    }

    public function getCurrentActiveDev($user, $ch_timestamp) {
        return $this->con->getCurrentActiveDev($user, $ch_timestamp);
    }

    public function getCurrentDevsCons($user, $ch_timestamp) {
        return $this->con->getCurrentDevsCons($user, $ch_timestamp);
    }

    public function setDevAsCurrentActive($user, $ch_timestamp) {
        return $this->con->setDevAsCurrentActive($user, $ch_timestamp);
    }

    public function getNewCurrentActiveDev($current_active_dev, $ch_timestamp, $current_time) {
        return $this->con->getNewCurrentActiveDev($current_active_dev, $ch_timestamp, $current_time);
    }

    public function getActiveDevName($user, $current_active_dev) {
        return $this->con->getActiveDevName($user, $current_active_dev);
    }

    public function getCurrActiveDevCons($user, $current_active_dev, $ch_timestamp, $current_time) {
        return $this->con->getCurrActiveDevCons($user, $current_active_dev, $ch_timestamp, $current_time);
    }

    public function getMaxDevsConsumption($user, $ch_timestamp) {
        return $this->con->getMaxDevsConsumption($user, $ch_timestamp);
    }

    public function updateChallengeCons($user, $ch_timestamp, $current_devices_consumption) {
        return $this->con->updateChallengeCons($user, $ch_timestamp, $current_devices_consumption);
    }

    public function getChallengeEnd($user, $ch_timestamp){
        return $this->con->getChallengeEnd($user, $ch_timestamp);
    }

    public function getAvgTwoDaysHHConsumption($user, $begin, $end) {
        return $this->con->getAvgTwoDaysHHConsumption($user, $begin, $end);
    }

    public function getMaxHHConsumption() {
        return $this->con->getMaxHHConsumption();
    }

    public function getDiffRates() {
        return $this->con->getDiffRates();
    }

    public function getUserDeviceIds($user) {
        return $this->con->getUserDeviceIds($user);
    }

    public function getAvgUserDeviceConsumptions($user, $user_dev_id, $current_time, $timestamp_two_days) {
        return $this->con->getAvgUserDeviceConsumptions($user, $user_dev_id, $current_time, $timestamp_two_days);
    }

    public function getDevCategory($user, $worst_device) {
        return $this->con->getDevCategory($user, $worst_device);
    }

    public function getMaxDevConsumption($category_id) {
        return $this->con->getMaxDevConsumption($category_id);
    }

    /* new version */
    public function getLastMonthConsumption($user, $begin, $end) {
        return $this->con->getLastMonthConsumption($user, $begin, $end);
    }

    public function addChallengeEqualDevice($model_id, $year_of_manufacture, $consumption, $category_id) {
        return $this->con->addChallengeEqualDevice($model_id, $year_of_manufacture, $consumption, $category_id);
    }

    public function addChallengeMessage($title, $message, $message_url) {
        $id = hash("md2", $title.time(), false);
        return $this->con->addChallengeMessage($id, $title, $message, $message_url);
    }

    public function addMultChallengeMessages($values_insert_stream) { return $this->con->addMultChallengeMessages($values_insert_stream); }

    public function getChallengeMessage($title, $get_count) {
        return $this->con->getChallengeMessage($title, $get_count);
    }

    public function getCountOfChallengeMessages() {
        return $this->con->getCountOfChallengeMessages();
    }

    public function getChallengeMessageFromID($message_id) {
        return $this->con->getChallengeMessageFromID($message_id);
    }

    public function addChallengeUserMessage($message_id, $user, $timestamp) {
        return $this->con->addChallengeUserMessage($message_id, $user, $timestamp);
    }

    public function getLastChallengeUserMessage($user) {
        return $this->con->getLastChallengeUserMessage($user);
    }

    public function getChallengeUsers($get_user_list) {
        return $this->con->getChallengeUsers($get_user_list);
    }

    public function addChallengeEnergyLevel($title, $description, $energy_level) {
        $id = hash("md2", $title.time(), false);
        return $this->con->addChallengeEnergyLevel($id, $title, $description, $energy_level);
    }

    public function getChallengeEnergyLevels() {
        return $this->con->getChallengeEnergyLevels();
    }

    public function getLevelCount() {
        return $this->con->getLevelCount();
    }

    public function getLevelTitles() {
        return $this->con->getLevelTitles();
    }

    public function addChallengeLevelUser($level_id, $user, $timestamp) {
        return $this->con->addChallengeLevelUser($level_id, $user, $timestamp);
    }

    public function getChallengeLevelUser($user) {
        return $this->con->getChallengeLevelUser($user);
    }

    public function addChallengeIncentive($title, $expiration_date, $incentive_url, $level_id) {
        $id = hash("md2", $title.time(), false);
        return $this->con->addChallengeIncentive($id, $title, $expiration_date, $incentive_url, $level_id);
    }

    public function getIncentiveCount($level_title) {
        return $this->con->getIncentiveCount($level_title);
    }

    public function addChallenge($title, $common_description, $level_id) {
        $id = hash("md2", $title.time(), false);
        return $this->con->addChallenge($id, $title, $common_description, $level_id);
    }

    public function getChallengeCount($level_title) {
        return $this->con->getChallengeCount($level_title);
    }

    public function addChallengeUser($challenge_id, $user, $timestamp, $special_description, $start, $end, $decision, $degree) {
        return $this->con->addChallengeUser($challenge_id, $user, $timestamp, $special_description, $start, $end, $decision, $degree);
    }

    public function getLastUserChallenge($user) {
        return $this->con->getLastUserChallenge($user);
    }

    public function getCurrentUserChallenge($user) {
        return $this->con->getCurrentUserChallenge($user);
    }

    /*
        Connect-Functions of Test-Data-Input-Widget
        added by Wascher Manuel
    */
    public function getAllUsernames() {
        return $this->con->getAllUsernames();
    }

    public function addRoomTestData($user, $bName, $name, $type) {
        $id = hash("md2", $name.time(), false);
        $buildings = $this->con->getBuildings($user);
        $bId = "";
        $i = 0;

        while(($i < count($buildings)) && ($buildings[$i][1] != $bName))
            $i++;

        if(count($buildings) != $i)
        {
            $bId = $buildings[$i][0];
            $b1 = $this->con->addRoom($user, $id, $name, $type, $bId);
            $this->updRoomSetDefault($id);
        }
        else
            $b1 = 0;

        return $b1;
    }

    public function addEventTDI($values_insert_stream) { return $this->con->addEventTDI($values_insert_stream); }

    public function getTestBuildingsCount($user, $building) { return $this->con->getTestBuildingsCount($user, $building); }

    public function delSpecificTDIDataContent($user, $building, $building_devs) {
        $table_information = array(
            0 => array(0 => "consumptionevents", 1 => "owner"),
            1 => array(0 => "devices", 1 => "owner"),
            2 => array(0 => "device_model", 1 => "username"),
            3 => array(0 => "device_status", 1 => "owner"),
            4 => array(0 => "occupancy", 1 => "username"),
	        5 => array(0 => "advices", 1 => "username"));
	
	    $user_letter = substr($user, 0, 4);

        for ($i=0; $i<count($building_devs); $i++)
        {
            for ($j=0; $j<count($table_information); $j++) {
			if ($table_information[$j][0] == "advices")
				$this->con->delSpecificTDIDataContent($table_information[$j][0],$table_information[$j][1],$user,"dev",$building_devs["device ".$i]["ID"].$user_letter);
			else
				$this->con->delSpecificTDIDataContent($table_information[$j][0],$table_information[$j][1],$user,"device",$building_devs["device ".$i]["ID"].$user_letter);
	        }
        }

        $this->con->delProduction($user);
        $circuit_id = $this->con->getCircuitID($user, $building);
	    $this->con->delCircuitData($circuit_id);
        $building_id = $this->con->getBuildingID($user, $building.$user_letter);
        $this->con->delSpecificTDIDataContent("users_rooms", "username", $user, "building", $building_id);
        return $this->con->delSpecificTDIDataContent("users_buildings", "username", $user, "name", "test_".$building.$user_letter);
    }

    public function delConsumptionEvents($user) {
        return $this->con->delConsumptionEvents($user);
    }

    public function delDevices($user) {
        return $this->con->delDevices($user);
    }

    public function delDeviceModelForUser($user) {
        $this->con->delDeviceModelForUser($user);
    }

    public function delDeviceStatus($user) {
        $this->con->delDeviceStatus($user);
    }

    public function delRooms($user) {
        return $this->con->delRooms($user);
    }

    public function updateDeviceModel($user, $device, $mode, $current_hour, $switched_on_count) {
        return $this->con->updateDeviceModel($user, $device, $mode, $current_hour, $switched_on_count);
    }

    public function getExistingAggCons($user, $timestamp) {
        return $this->con->getExistingAggCons($user, $timestamp);
    }

    public function updateAggData($user, $timestamp, $consumption) {
        return $this->con->updateAggData($user, $timestamp, $consumption);
    }

    public function getCircuitID($user, $building) {
        return $this->con->getCircuitID($user, $building);
    }

    public function addCirPowerEventTDI($values_insert_stream) { return $this->con->addCirPowerEventTDI($values_insert_stream); }

    public function getCircuitAvg($id, $hour) {
        return $this->con->getCircuitAvg($id, $hour);
    }

    public function updateCirAvgMinMax($id, $hour, $min, $max) {
        return $this->con->updateCirAvgMinMax($id, $hour, $min, $max);
    }

    public function updateCirAvgRecDays($id, $new_n) {
        return $this->con->updateCirAvgRecDays($id, $new_n);
    }

    public function getCirAvgRecDays($id) {
        return $this->con->getCirAvgRecDays($id);
    }

    public function updateDevType($dev_id, $type) {
        return $this->con->updateDevType($dev_id, $type);
    }

    public function delCircuitData($cir_id) {
	return $this->con->delCircuitPower($cir_id);
    }

    public function getAuthkey($user) {
        return $this->con->getAuthkey($user);
    }
}

$con = new Connection("mysql", $mysql);