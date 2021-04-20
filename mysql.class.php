<?php
class mysql extends dbConnection {
	public $db;
	
	public function __construct($data) {
		$this->db = @new mysqli($data[0], $data[1], $data[2], $data[3]);
	}
	
	public function escape($value) {
		return $this->db->real_escape_string($value);
	}
	
	public function getUser($key) {
		$result = $this->db->query("SELECT username FROM user WHERE authkey = '$key' LIMIT 1");
		
		if($result->num_rows == 1)
			return $result->fetch_object()->username;
		else
			return -1;
	}
	
	public function getPw($key) {
		$result = $this->db->query("SELECT password FROM user WHERE authkey = '$key' LIMIT 1");
		
		if($result->num_rows == 1)
			return $result->fetch_object()->password;
		else
			return -1;
	}
	
	public function getTheme($key) {
		$result = $this->db->query("SELECT theme FROM user WHERE authkey = '$key' LIMIT 1")->fetch_object()->theme;
		return ($result);
	}
	
	public function updTheme($key, $theme) {
		return $this->db->query("UPDATE user Set theme = '$theme' WHERE authkey = '$key'");
	}
	
	public function getPrivileges($key) {
		$result = $this->db->query("SELECT privileges FROM user WHERE authkey = '$key' LIMIT 1")->fetch_object()->privileges;
		return ($result);
	}
	
	public function getPubData($user) {
		$result = $this->db->query("SELECT theme FROM user WHERE username = '$user' AND pubpage = 1");
		
		if($result->num_rows == 1)
			return $result->fetch_object()->theme;
		else
			return "_access_denied";
	}
	
	public function getPubPage($key) {
		$result = $this->db->query("SELECT pubpage FROM user WHERE authkey = '$key'")->fetch_object();
		return $result->pubpage;
	}
	
	public function getPubPageByUser($user) {
		$result = $this->db->query("SELECT pubpage FROM user WHERE username = '$user'")->fetch_object();
		return $result->pubpage;
	}
	
	public function setPubPage($key, $value) {
		return $this->db->query("UPDATE user Set pubpage = '$value' WHERE authkey = '$key'");
	}
	
	public function setDeviceRoom0($user, $room) {
		return $this->db->query("UPDATE devices Set room='0' WHERE room='$room' AND owner='$user'");
	}
	
	public function delRoom($user, $room) {
		return $this->db->query("DELETE FROM users_rooms WHERE id='$room' AND username='$user'");
	}
	
	public function updRoomName($id, $name) {
		return $this->db->query("UPDATE users_rooms Set descr = '$name' WHERE id = '$id'");
	}
	
	public function updRoomType($id, $type) {
		return $this->db->query("UPDATE users_rooms Set room  = '$type' WHERE id = '$id'");
	}
	
	public function addRoom($user, $id, $name, $type, $bId) {		
		if($this->db->query("INSERT INTO users_rooms (id, username, room, descr, building) VALUES ('$id', '$user', '$type', '$name', '$bId')"))
			return $id;
		else
			return 0;
	}
	
	public function updDeviceName($user, $dev, $name) {
		return $this->db->query("UPDATE devices Set name = '$name' WHERE device = '$dev' AND owner = '$user'");
	}
	
	public function updDeviceCat($user, $dev, $cat) {
		return $this->db->query("UPDATE devices Set category = '$cat' WHERE device = '$dev' AND owner = '$user'");
	}
	
	public function updDeviceRoom($user, $dev, $room) {
		return $this->db->query("UPDATE devices Set room = '$room' WHERE device = '$dev' AND owner = '$user'");
	}
	
	public function updDeviceCredits($user, $dev, $credits) {
		return $this->db->query("UPDATE devices Set credit = '$credits' WHERE owner='$user' AND device='$dev'");
	}
	
	public function addCredits($user, $device, $c, $date) {
		return $this->db->query("INSERT INTO wallet (owner, device, amount, time) VALUES ('$user', '$device', '$c', '$date')");
	}
	
	public function getCredits($user, $device) {
		$result = $this->db->query("SELECT credit FROM devices WHERE owner='$user' AND device='$device'");
		
		if($result->num_rows > 0) {
			$row = $result->fetch_object();
			return $row->credit*1;
		}
		else
			return 0;
	}
	
	public function getTariffData($user, $day) {
		$result = $this->db->query("SELECT startDay, endDay, startTime, endTime, tariff FROM tariffs WHERE username='$user' AND startDay = '$day'");
		$tariff = array();
		$n = 0;
		
		if($result->num_rows > 0) {
			while($row = $result->fetch_object()) {
				$startTime = $row->startTime;
					$startTime = substr($startTime, 0, -3);
				$endTime = $row->endTime;
					$endTime = substr($endTime, 0, -3);
				
				$tariff[$n][0] = $row->startDay." - ".$row->endDay;
				$tariff[$n][1] = $startTime." - ".$endTime;
				$tariff[$n][2] = $row->tariff;
				$n++;
			}
		}
		
		return $tariff;
	}
	
	public function getTariffDESC($user) {
		$result = $this->db->query("SELECT startDay, endDay, startTime, endTime, tariff FROM tariffs WHERE username = '$user' ORDER BY tariff DESC");
		
		$tariff = array();
		$n = 0;
		
		while($row = $result->fetch_object()) {
			$tariff[$n][0] = $row->startDay;
			$tariff[$n][1] = $row->endDay;
			$tariff[$n][2] = $row->startTime;
			$tariff[$n][3] = $row->endTime;
			$tariff[$n][4] = $row->tariff;
			$n++;
		}
		
		return $tariff;
	}
	
	public function getRooms($user) {
		$n = 0;
		$rooms = array();
		$result = $this->db->query("SELECT id, room, descr, building, def FROM users_rooms WHERE username = '$user'");
		while($row = $result->fetch_object()) {
			$rooms[$n][0] = $row->id;
			$rooms[$n][1] = $row->room;
			$rooms[$n][2] = $row->descr;
			$rooms[$n][3] = $row->building;
			$rooms[$n][4] = $row->def;
			$n++;
			}
		return $rooms;
	}
	
	public function updRoomSetDefault($id, $user) {
		$b1 = $this->db->query("UPDATE users_rooms Set def = 1 WHERE id = '$id' AND username = '$user'");
		$b2 = $this->db->query("UPDATE users_rooms Set def = 0 WHERE id != '$id' AND username = '$user'");
		
		return $b1 && $b2;
	}
	
	public function getDevTree($idKid) {
		$result = $this->db->query("SELECT name, parent FROM nilm_devices WHERE id = '$idKid'");
	
		if($result->num_rows > 0) {	
			$result = $result->fetch_object();
			$parent = $result->parent;
			$tree = array();
				$tree[] = $result->name;
			
			while($parent != 1 && $parent != 0) {
				$result = $this->db->query("SELECT name, parent FROM nilm_devices WHERE id = '$parent'")->fetch_object();
				$parent = $result->parent;
					$tree[] = $result->name;
				}
			}
		
		$n = count($tree);
		
		if($tree[$n-1] != "appliance")
			$tree[] = "appliance";
		
		$tree = array_reverse($tree);
		return json_encode($tree);
	}
	
	public function getDevices($user) {
		$result = $this->db->query("SELECT d.device, d.name, credit, category, room, n.usercontrol, n.curtailability, n.standby, n.icon FROM devices AS d, nilm_devices AS n WHERE d.category = n.id AND d.owner = '$user' ");
		$n=0;
		$devices = array();
		while($row = $result->fetch_object()) {
			$devices[$n][0] = $row->device;
			$devices[$n][1] = $row->name;
			$devices[$n][2] = $row->credit;
			$devices[$n][3] = $row->category;
			$devices[$n][4] = $this->getDevTree($row->category);
			$devices[$n][5] = $row->room;
			$devices[$n][6] = $row->usercontrol;
			$devices[$n][7] = $row->curtailability;
			$devices[$n][8] = $row->standby;
			$devices[$n][9] = $row->icon;
			$devices[$n][10] = 0; //device_status - websocket
			$devices[$n][11] = 0; //device_state  - switched on/off
			$n++;
		}
		return $devices;
	}
	
	public function getCategories() {
		$n = 0;
		$categories = $this->db->query("SELECT id, name, parent FROM nilm_devices ORDER BY id");
		$categoriesArr = array();
		while($row = $categories->fetch_object()) {
			$categoriesArr[$row->id][0] = $row->name;
			$categoriesArr[$row->id][1] = $row->parent;
			$n++;
			}
		$categoriesArr[0] = $n;
		return $categoriesArr;
	}
	
	public function getCategorieTree() {
		$n = 0;
		$cur = -1;
		$catArr2 = array();
		$categories = $this->db->query("SELECT * FROM nilm_devices ORDER BY parent, id");
		while($row = $categories->fetch_object()) {
			if($cur != $row->parent) {
				$n = 0;
				$cur = $row->parent;
				}
			
			$catArr2[$cur][$n][0] = $row->parent;
			$catArr2[$cur][$n][1] = $row->id;
			$catArr2[$cur][$n][2] = $row->name;
			$n++;
			}
		return $catArr2;
	}
	
	public function getWidgets($user, $page) {
		$i = 0;
		
		$widgets = array();
		$widgets_query = $this->db->query("SELECT id, widget FROM dashboard WHERE username = '$user' AND page = '$page'");
		
		while($row = $widgets_query->fetch_object()) {
			$w_row = json_decode($row->widget);
			$path = $w_row->path;
			
			include("../../widgets/".$path."/config.php");
			
			$widgets[$i][0] = $row->id; //id in DB
			$widgets[$i][1] = "";
			$widgets[$i][2] = "";
			$widgets[$i][3] = $path;
			$widgets[$i][4] = "";
			$widgets[$i][5] = $w_row->inputCur;
			$widgets[$i][6] = "";
			$i++;
		}
		
		return $widgets;
	}
	
	public function delWidget($id) {
		return $this->db->query("DELETE FROM dashboard WHERE id = '$id'");
	}
	
	public function getWidget($id) {
		return $this->db->query("SELECT widget FROM dashboard WHERE id = '$id'")->fetch_object()->widget;
	}
	
	public function updWidgetInput($id, $value) {
		return $this->db->query("UPDATE dashboard Set widget = '$value' WHERE id = '$id'");
	}
	
	public function isAggInstalled($user) {
		$result = $this->db->query("SELECT username FROM production WHERE username = '$user' LIMIT 1");
		
		if($result->num_rows == 1)
			return true;
		else
			return false;
	}
	
	public function isDisInstalled($user) {
		$result = $this->db->query("SELECT owner FROM consumptionevents WHERE owner = '$user' LIMIT 1");
		
		if($result->num_rows == 1)
			return true;
		else
			return false;
	}
	
	public function delTariffData($user) {
		return $this->db->query("DELETE FROM tariffs WHERE username = '$user'");
	}
	
	public function insTariffData($user, $from, $to, $fromtp, $totp, $tariff) {
		return $this->db->query("INSERT INTO tariffs (username, startDay, endDay, startTime, endTime, tariff) VALUES ('$user', '$from', '$to', '$fromtp', '$totp', '$tariff')");
	}
	
	public function getUserAmount($user, $email) {
		return $this->db->query("SELECT username FROM user WHERE username = '$user' OR mail = '$email' LIMIT 1")->num_rows;
	}
	
	public function existsUser($user) {
		$result = $this->db->query("SELECT username FROM user WHERE username = '$user'");
		
		return $result->num_rows == 1;
	}
	
	public function getUserAmountByPassword($user, $pw) {		
		$result =  $this->db->query("SELECT authkey FROM user WHERE username = '$user' AND password = '$pw'");
		
		if($result->num_rows == 1)
			return $result->fetch_object()->authkey;
		else
			return 0;		
	}
	
	public function insUser($user, $pw, $mail, $key) {
		return $this->db->query("INSERT INTO user (username, password, mail, authkey, privileges) VALUES ('$user', '$pw', '$mail', '$key', 'L')");
	}
	
	public function insDashboard($user, $page, $widget) {
		return $this->db->query("INSERT INTO dashboard (username, page, widget) VALUES ('$user', '$page', '$widget')");
	}
	
	public function getDashboardID() {
		return $this->db->query("SELECT id FROM dashboard ORDER BY id DESC")->fetch_object()->id;
	}
	
	public function getDevicesByCategory($cat) {
		$devices = array();
		$result  = $this->db->query("SELECT device FROM devices WHERE category = '$cat'");
		
		if($result->num_rows > 0)
			while($row = $result->fetch_object()) {
				$devices[] = $row->device;
			}
		
		return $devices;
	}
	
	public function getEventsByDevice($dev) {
		$events = array();
		$n = 0;
		
		$result = $this->db->query("SELECT consumption, duration FROM consumptionevents WHERE device = '$dev'");
		
		if($result->num_rows > 0)
			while($row = $result->fetch_object()) {
				$events[$n][0] = $row->consumption;
				$events[$n][1] = $row->duration;
				$n++;
			}
		
		return $events;
	}
	
	public function updAnalyticsUsages($cat, $n) {
		return $this->db->query("UPDATE analytics Set usages = '$n' WHERE id = '$cat'");
	}
	
	public function updAnalyticsAvrg($cat, $avrg) {
		return $this->db->query("UPDATE analytics Set consumption = '$avrg' WHERE id = '$cat'");
	}
	
	public function delOldEvents() {
		$start = time()-86400*30;
		return $this->db->query("DELETE FROM consumptionevents WHERE start < '$start'");
	}
	
	public function getDevicesAll() {
		$n = 0;
		$devices = array();
		$result = $this->db->query("SELECT owner, device FROM devices");
		
		if($result->num_rows > 0)
			while($row = $result->fetch_object()) {
				$devices[$n][0] = $row->owner;
				$devices[$n][1] = $row->device;
				$n++;
			}
		
		return $devices;
	}
	
	public function getDevicesUserdriven() {
		$result = $this->db->query("SELECT device FROM devices AS d, nilm_devices AS n WHERE d.category = n.id AND n.usercontrol = 1");
		$devArr = array();
		
		while($device = $result->fetch_object()) {
			$devArr[]	= $device->device;
		}
		
		return $devArr;
	}
	
	public function getDevicesOwner() {
		$result = $this->db->query("SELECT owner FROM devices AS d, nilm_devices AS n WHERE d.category = n.id AND n.usercontrol = 1");
		$ownArr = array();
		
		while($owner = $result->fetch_object()) {
			$ownArr[]	= $owner->owner;
		}
		
		return $ownArr;
	}
	
	public function getEventsAll() {
		$result = $this->db->query("SELECT owner, device, start, duration FROM consumptionevents");
		$events = array();
		
		$n = 0;
		while($event = $result->fetch_object()) {
			$events[$n][0] = $event->owner;
			$events[$n][1] = $event->device;
			$events[$n][2] = $event->start;
			$events[$n][3] = $event->duration;
			$n++;
		}
		
		return $events;
	}
	
	public function delOccupancy() {
		return $this->db->query("DELETE FROM occupancy");
	}
	
	public function writeOccupancy($user, $dev, $p, $data) {
		$string = 'INSERT INTO occupancy (username, device, p, h0, h1, h2, h3, h4, h5, h6, h7, h8, h9, h10, h11, h12, h13, h14, h15, h16, h17, h18, h19, h20, h21, h22, h23) VALUES ("'.$user.'", "'.$dev.'", '.$p.', ';
		
		for($i=0;$i<count($data);$i++) {
			$string .= $data[$i];
			
			if($i<23) {
				$string .= ', ';
			}
			else {
				$string .= ')';
			}
		}
		
		return $this->db->query($string);
	}
	
	public function getEventsByDeviceOwner($dev, $user) {
		$result = $this->db->query("SELECT * FROM consumptionevents WHERE owner = '$user' AND device = '$dev' ORDER BY start");
		$n = 0;
		$events = array();
		
		while($row = $result->fetch_object()) {
			$events[$n][0] = $row->device;
			$events[$n][1] = $dev;
			$events[$n][2] = $row->start;
			$events[$n][3] = $row->duration;
			$n++;
		}
		
		return $events;
	}
	
	public function getEventsByOwner($user) {
		$result = $this->db->query("SELECT * FROM consumptionevents WHERE owner = '$user' ORDER BY start");
		$n = 0;
		$events = array();
		
		while($row = $result->fetch_object()) {
			$events[$n][0] = $row->device;
			$events[$n][1] = $row->start;
			$events[$n][2] = $row->duration;
			$events[$n][3] = $row->consumption;
			$n++;
		}
		
		return $events;
	}
	
	public function getLastAggData($user) {
		$result = $this->db->query("SELECT production, consumption, timestamp FROM production WHERE username = '$user' ORDER BY timestamp DESC LIMIT 1")->fetch_object();
		
		$data = array();
		
		$data[] = $result->production;
		$data[] = $result->consumption;
		$data[] = $result->timestamp;
		
		return $data;
	}
	
	public function insertAggData($user, $timestamp, $consumption, $production) {
		return $this->db->query("INSERT INTO production (username, timestamp, production, consumption) VALues ('$user', '$timestamp', '$production', '$consumption')");
	}
	
	public function getAggData($user) {
		$result = $this->db->query("SELECT production, consumption, timestamp FROM production WHERE username = '$user' ORDER BY timestamp DESC LIMIT 30");
		$n = 0;
		$data = array();
		
		while($row = $result->fetch_object()) {
			$data[$n][0] = $row->production;
			$data[$n][1] = $row->consumption;
			$data[$n][2] = $row->timestamp;
			$n++;
		}
		
		return $data;
	}
	
	public function delDeviceModel($dev) {
		return $this->db->query("DELETE FROM device_model WHERE device = '$dev'");
	}
	
	public function insDeviceModel($user, $dev, $result) {
		for($i=0;$i<3;$i++) {
			$per = $i;
			$array = $result[$i];
			$string = 'INSERT INTO device_model (username, device, p, h0, h1 , h2 , h3 , h4 , h5 , h6 , h7 , h8 , h9 , h10 , h11 , h12 , h13 , h14 , h15 , h16 , h17 , h18 , h19 , h20 , h21 , h22 , h23) VALUES ("'.$user.'", "'.$dev.'", '.$per.', ';
			
			for($j=0;$j<24;$j++) {
				$string .= $array[$j];
				if($j<23)
					$string .= ", ";
				else
					$string .= ")";
			}
			
			$this->db->query($string);
		}
		
		return true;
	}
	
	public function getEventsByUserAndTime($user, $t) {
		$result = $this->db->query("SELECT consumption, start, device, duration FROM consumptionevents WHERE owner = '$user' AND start >= $t ORDER BY start");
		$n = 0;
		$sum = 0;
		$max = 0;
		$lastDate = 0;
		$data = array();
		
		$events = array();
		
		while($row = $result->fetch_object()) {
			$sum = $sum+$row->consumption;
			$lastDate = $row->start;
			
			if($row->consumption > $max)
				$max = $row->consumption;
			
			$events[$n][0] = $row->start;
			$events[$n][1] = $row->consumption;
			$events[$n][2] = $row->device;
			$events[$n][3] = $row->duration;
			
			$n++;
			}
		
		$data[] = $sum;
		$data[] = $lastDate;
		$data[] = $n;
		$data[] = $max;
		$data[] = $events;
		
		return $data;	
	}
	
	public function getAdvices($user) {
		$result = $this->db->query("SELECT ad_type, dev, data, up, down FROM advices WHERE username = '$user' AND enabled = 1");
		$n = 0;
		
		$advices = array();
		
		while($row = $result->fetch_object()) {
			$advices[$n][0] = $row->ad_type;
			$advices[$n][1] = $row->dev;
			$advices[$n][2] = $row->data;
			$advices[$n][3] = $row->up;
			$advices[$n][4] = $row->down;
			$n++;
		}
		
		return $advices;
	}
	
	public function advisorQuery1($user) {
		$devs_cons = array();
		$result = $this->db->query("SELECT device, count(*) as events, AVG(consumption) as avg_cons, AVG(duration) as avg_dur FROM consumptionevents WHERE owner = '$user' GROUP BY device ORDER BY avg_cons DESC");
		while($row = $result->fetch_assoc()) {
			$devs_cons[] = $row;
		}
		
		return $devs_cons;
	}
	
	public function advisorQuery2($user) {
		$tariff = array();
		$result = $this->db->query("SELECT startDay, endDay, startTime, endTime, tariff FROM tariffs WHERE username = '$user' ORDER BY tariff DESC");
		while($row = $result->fetch_assoc()) $tariff[] = $row;
		
		return $tariff;
	}
	
	public function updAdvice($user, $dev, $type, $data) {		
		$exist = $this->db->query("SELECT 'a' FROM advices WHERE username='$user' AND ad_type='$type' AND dev='$dev'");
			if($exist->num_rows == 1)
				return($this->db->query("UPDATE advices Set data = '$data' WHERE username='$user' AND ad_type='$type' AND dev='$dev'"));
			else
				return($this->db->query("INSERT INTO advices (username, ad_type, dev, data) VALUES ('$user', '$type', '$dev', '$data')"));
	}
	
	public function advisorQuery3() {
		$result = $this->db->query("SELECT device, devices.name AS name, nilm.name AS catName, consumption FROM nilm_devices AS nilm, devices, analytics WHERE usercontrol=0 AND nilm.id=devices.category AND nilm.id=analytics.id");
		
		$dev = array();
		$n = 0;
		
		while($row = $result->fetch_object()) {
			$dev[$n][0] = $row->device;
			$dev[$n][1] = $row->name;
			$dev[$n][2] = $row->catName;
			$dev[$n][3] = $row->consumption;
			$n++;
		}
		
		return $dev;
	}
	
	public function delAdvices() {
		return $this->db->query("DELETE FROM advices");
	}
	
	public function voteAdvice($user, $type, $dev, $n) {
		switch($n) {
			case 1:
				return $this->db->query("UPDATE advices Set up = advices.up+1 WHERE username = '$user' AND ad_type = '$type' AND dev = '$dev'");
			break;
			case 2:
				return $this->db->query("UPDATE advices Set enabled = 0 WHERE username = '$user' AND ad_type = '$type' AND dev = '$dev'");
			break;
			case 3:
				return $this->db->query("UPDATE advices Set down = advices.down+1 WHERE username = '$user' AND ad_type = '$type' AND dev = '$dev'");
			break;
			case 4:
				return $this->db->query("UPDATE advices Set down = advices.down+1 WHERE username = '$user' AND ad_type = '$type'");
			break;
			case 5:
				return $this->db->query("UPDATE advices Set down = advices.down+1 WHERE username = '$user' AND ad_type = '$type'");
			break;
			
			
		}
	}
	
	public function rtPowerQuery1($user) {
		$result = $this->db->query("SELECT name, device FROM devices WHERE owner = '$user'");
		$data = array();
		$upt = 0;
		
		while($row = $result->fetch_object()) {
			$devID = $row->device;
			
			$model = $this->db->query("SELECT power, last_updated FROM device_status WHERE owner = '$user' AND device = '$devID' ORDER BY last_updated DESC")->fetch_object();
			
			if($upt == 0)
				$upt	 = $model->last_updated;
			
			if($model->last_updated == $upt) {
				$dev[]   = $row->name;
				$power[] = $model->power;
			}
			
		}
		
		$data[] = $dev;
		$data[] = $power;
		$data[] = $upt;
		
		return $data;
	}
	
	public function getLastEvent($user) {
		$result = $this->db->query("SELECT device, start, duration, consumption FROM consumptionevents WHERE owner = '$user' ORDER BY start DESC LIMIT 1");
		$data = array();
		
		if($result->num_rows == 1) {
			$row = $result->fetch_object();
			$data[] = $row->device;
			$data[] = $row->start;
			$data[] = $row->duration;
			$data[] = $row->consumption;
		}
		
		return $data;
	}
	
	public function getTariffGroup($user) {
		$tariffs = $this->db->query("SELECT startDay, endDay, startTime, endTime, tariff FROM tariffs WHERE username = '$user'");
		
		while($row = $tariffs->fetch_object()) {
			$tariffGroup[] = $row;
			}
		
		return $tariffGroup;
	}

	public function getWallet($user, $t) {
		$result = $this->db->query("SELECT device, amount, time FROM wallet WHERE owner = '$user' AND time >= '$t'");
		$data = array();
		$n = 0;
		
		while($row = $result->fetch_object()) {
			$data[$n][0] = $row->device;
			$data[$n][1] = $row->amount;
			$data[$n][2] = $row->time;
			$n++;
		}
		
		return $data;
	}
	
	public function cakeQuery1($user, $t1, $t2) {
		$result = $this->db->query("SELECT device, start, consumption, duration FROM consumptionevents WHERE owner = '$user' AND start >= $t1 AND start <= '$t2'");
		
		$event = array();
		$n = 0;
		
		while($row = $result->fetch_object()) {
			$event[$n][0] = $row->device;
			$event[$n][1] = $row->start;
			$event[$n][2] = $row->consumption;
			$event[$n][3] = $row->duration;
			$n++;
		}
		
		return $event;
	}
	
	public function getDeviceModel($user, $mode, $dev) {
		$result = $this->db->query("SELECT * FROM device_model WHERE username='$user' AND p='$mode' AND device = '$dev' LIMIT 1")->fetch_array();
		
		$data = array();
		
		for($i=0;$i<24;$i++) {
			$data[] = $result["h".$i];
		}
		
		return $data;
	}
	
	public function getDeviceName($dev) {
		$result = $this->db->query("SELECT name FROM devices WHERE device = '$dev'");
		
		if($result->num_rows > 0)
			return $result->fetch_object()->name;
		else
			return "no name available";
	}
	
	public function getEventRange($user) {
		$result = $this->db->query("SELECT max(start) AS smax, min(start) AS smin FROM consumptionevents WHERE owner = '$user'")->fetch_object();
		$data = array();
		
		$data[] = $result->smin;
		$data[] = $result->smax;
		
		return $data;
	}
	
	public function getOccupancy($user, $mode) {
		$result = $this->db->query("SELECT * FROM occupancy WHERE username='$user' AND p='$mode'");
		$data = array();
		
		while($row = $result->fetch_array()) {
			$data[] = $row;
		}
		
		return $data;
	}
	
	public function getKey($user) {
		return $this->db->query("SELECT authkey FROM user WHERE username = '$user'")->fetch_object()->authkey;
	}
	
	public function switchQuery1($user, $day) {
		$result = $this->db->query("SELECT * FROM tariffs WHERE username = '$user' AND startDay = '$day' ORDER BY startTime");
		
		$tariffs = array();
		$n = 0;
		
		while($row = $result->fetch_object()) {
			$tariffs[$n][0] = $row->startDay;
			$tariffs[$n][1] = $row->endDay;
			$tariffs[$n][2] = $row->startTime;
			$tariffs[$n][3] = $row->endTime;
			$tariffs[$n][4] = $row->tariff;
			$n++;
		}
		
		return $tariffs;
	}
	
	public function addBuilding($id, $user, $name) {
		return $this->db->query("INSERT INTO users_buildings (id, username, name) VALUES ('$id', '$user', '$name')");
	}
	
	public function delBuilding($id) {
		$this->db->query("UPDATE users_rooms Set building = '0' WHERE building = '$id'");
		return $this->db->query("DELETE FROM users_buildings WHERE id = '$id'");
	}
	
	public function addRoomToBuilding($rId, $bId) {
		return $this->db->query("UPDATE users_rooms Set building = '$bId' WHERE id = '$rId'");
	}
	
	public function getBuildings($user) {
		$result = $this->db->query("SELECT * FROM users_buildings WHERE username = '$user' ORDER BY name");
		
		$data = array();
		$n = 0;
		
		while($row = $result->fetch_object()) {
			$data[$n][0] = $row->id;
			$data[$n][1] = $row->name;
			$data[$n][2] = $row->def;
			$n++;
		}
		
		return $data;
	}
	
	public function updBuilding($id, $name) {
		return $this->db->query("UPDATE users_buildings Set name = '$name' WHERE id = '$id'");
	}
	
	public function updBuildingSetDefault($id, $user) {
		$this->db->query("UPDATE users_buildings Set def = 0 WHERE id != '$id' AND username = '$user'");		
		return $this->db->query("UPDATE users_buildings Set def = 1 WHERE id = '$id' AND username = '$user'");
	}
	
	public function getCreditByDevice($user, $device) {
		$result = $this->db->query("SELECT credit FROM devices WHERE owner = '$user' AND device = '$device'");
		
		if($result->num_rows == 1)
			return $result->fetch_object()->credit;
		else
			return -1;
	}
	
	public function addDevice($owner, $dev, $name, $defRoom) {
		$b1 = $this->db->query("INSERT INTO devices (owner, device, name, room) VALUES ('$owner', '$dev', '$name', '$defRoom')");
		$b2 = $this->db->query("INSERT INTO device_status (owner, device) VALUES ('$owner', '$dev')");
		$b3 = $this->db->query("INSERT INTO device_model (username, device, p) VALUES ('$owner', '$dev', 1)");
		$b4 = $this->db->query("INSERT INTO device_model (username, device, p) VALUES ('$owner', '$dev', 2)");
		$b5 = $this->db->query("INSERT INTO device_model (username, device, p) VALUES ('$owner', '$dev', 3)");
		
		return $b1 && $b2 && $b3 && $b4 && $b5;
	}
	
	public function updCredits($user, $dev, $c) {
		return $this->db->query("UPDATE devices Set credit = '$c' WHERE owner = '$user' AND device = '$dev'");
	}
	
	public function addEvent($user, $dev, $start, $dur, $con) {
        return $this->db->query("INSERT INTO consumptionevents (owner, device, start, duration, consumption) VALUES ('$user', '$dev', '$start', '$dur', '$con')");
	}
	
	public function setMaxConsumption($user) {
		$query = sprintf("
			SELECT MAX(a.consumption) AS max FROM (
				SELECT  DATE(FROM_UNIXTIME(start)) AS mydate, SUM(consumption) AS consumption FROM consumptionevents WHERE owner LIKE '%s' GROUP BY DATE(FROM_UNIXTIME(start))
			) a;
		", $user);
		
		$result = $this->db->query($query);
		
		return $result->fetch_array();
	}
	
	public function getNotKey($user) {
		$result = $this->db->query("SELECT notificationkey FROM notifications WHERE username = '$user'");
		
		$data = array();
		
		if($result->num_rows > 0) {
			while($row = $result->fetch_object()) {
				$data[] = $row->notificationkey;
			}
			
			return $data;
		}
		else
			return -1;
	}
	
	public function delNotKey($key) {
		return $this->db->query("DELETE FROM notifications WHERE notificationkey = '$key'");
	}
	
	public function getRoom($id) {
		return $this->db->query("SELECT username, room, descr, building, def FROM users_rooms WHERE id = '$id'")->fetch_array();
	}
	
	public function getBuilding($id) {
		return $this->db->query("SELECT username, name, def FROM users_buildings WHERE id = '$id'")->fetch_array();
	}
	
	public function insWallet($user, $device, $amount, $time) {
		return $this->db->query("INSERT INTO wallet (owner, device, amount, time) VALUES ('$user', '$device', '$amount', '$time')");
	}
	
	public function isValidated($user) {
		$result = $this->db->query("SELECT validated FROM user WHERE username = '$user'");
		
		if($result->num_rows > 0)
			return $result->fetch_object()->validated;
		else
			return -1;
	}
	
	public function insNotKey($user, $key) {
		return $this->db->query("INSERT INTO notifications (username, notificationkey) VALUES ('$user', '$key')");
	}
	
	public function addCircuit($id, $key, $name) {
		$b1 = $this->db->query("INSERT INTO circuit (id, authkey, name) VALUES ('$id', '$key', '$name')");
		$b2 = $this->db->query("INSERT INTO circuit_avg (id) VALUES ('$id')");
		
		if($b1 && $b2)
			return $id;
		else
			return -1;
	}
	
	public function getCircuits($key) {
		$result = $this->db->query("SELECT id, name, type, map_id FROM circuit WHERE authkey = '$key'");
		$data	= array();
		$n		= 0;
		
		if($result->num_rows > 0) {
			while($row = $result->fetch_object()) {
				$data[$n][0] = $row->id;
				$data[$n][1] = $row->name;
				$data[$n][2] = $row->type;
				$data[$n][3] = $row->map_id;
				$n++;
			}
		}
		
		return $data;
	}
	
	public function delUser($key) {
		return $this->db->query("DELETE FROM user WHERE authkey = '$key'");
	}
	
	public function delDevices($user) {
		return $this->db->query("DELETE FROM devices WHERE owner = '$user'");
	}
	
	public function delDevice($dev) {
		return $this->db->query("DELETE FROM devices WHERE device = '$dev'");
	}
	
	public function delDashboard($user) {
		return $this->db->query("DELETE FROM dashboard WHERE username = '$user'");
	}
	
	public function delRooms($user) {
		return $this->db->query("DELETE FROM users_rooms WHERE username = '$user'");
	}
	
	public function delBuildings($user) {
		return $this->db->query("DELETE FROM users_buildings WHERE username = '$user'");
	}
	
	public function delTariffs($user) {
		return $this->db->query("DELETE FROM tariffs WHERE username = '$user'");
	}
	
	public function delProduction($user) {
		return $this->db->query("DELETE FROM production WHERE username = '$user'");
	}
	
	public function delOccupancyForUser($user) {
		return $this->db->query("DELETE FROM occupancy WHERE username = '$user'");
	}
	
	public function delNotifications($user) {
		return $this->db->query("DELETE FROM notifications WHERE username = '$user'");
	}
	
	public function delDeviceModelForUser($user) {
		return $this->db->query("DELETE FROM device_model WHERE username = '$user'");
	}
	
	public function delConsumptionEvents($user) {
		return $this->db->query("DELETE FROM consumptionevents WHERE owner = '$user'");
	}
	
	public function delAdvicesForUser($user) {
		return $this->db->query("DELETE FROM advices WHERE username = '$user'");
	}
	
	public function delCircuits($user, $circuits) {
		$succ = true;
		
		for($i=0; $i<count($circuits); $i++) {
			$id = $circuits[$i][0];
			
			if(!$this->db->query("DELETE FROM circuit_power WHERE id = '$id'"))
				$succ = false;
		}
		
		if(!$this->db->query("DELETE FROM circuit WHERE username = '$user'"))
			$succ = false;
		
		return $succ;
	}
	
	public function existsCircuit($id) {
		$result = $this->db->query("SELECT 'a' FROM circuit WHERE id = '$id'");
		
		if($result->num_rows == 1)
			return true;
		else
			return false;
	}
	
	public function insCircuitPower($id, $time, $power) {
		return $this->db->query("INSERT INTO circuit_power (id, timestamp, power) VALUES ('$id', '$time', '$power')");
	}
	
	public function delCircuitPower($id) {
		return $this->db->query("DELETE FROM circuit_power WHERE id = '$id'");
	}
	
	public function delCircuit($id) {
		return $this->db->query("DELETE FROM circuit WHERE id = '$id'");
	}
	
	public function modifyCircuit($id, $t, $v) {
		$b1 = $this->db->query("UPDATE circuit Set type = '$t' WHERE id = '$id'");
		$b2 = $this->db->query("UPDATE circuit Set map_id = '$v' WHERE id = '$id'");
		
		return $b1 && $b2;
	}
	
	public function getCircuitPower($c) {
		$data = array();
		$n = 0;
		$result = $this->db->query("SELECT timestamp, power FROM circuit_power WHERE id = '$c' ORDER BY timestamp");
		
		while($row = $result->fetch_object()) {
			$data[$n][0] = $row->timestamp;
			$data[$n][1] = $row->power;
			$n++;
		}
		
		return $data;
	}
	
	public function getPowAvrg($c, $t) {
		$result = $this->db->query("SELECT timestamp, power FROM circuit_power WHERE id = '$c' AND timestamp >= '$t'-10 AND timestamp < '$t'");
		
		$data = array();
		$n = 0;
		
		//get events in this timestamp
		if($result->num_rows == 0)
			return 0;
		else {
			while($row = $result->fetch_object()) {
				$data[$n][0] = $row->timestamp;
				$data[$n][1] = $row->power;
				$n++;
			}
			return $data;
		}
	}
	
	public function updPowerMoAverage($id, $h) {
		$begin = strtotime(date("m/d/Y", time()));
		
		$start = $begin + ($h-1)*3600;
		$end   = $begin + $h*3600;
		
		$result = $this->db->query("SELECT MAX(power) AS max, MIN(power) AS min FROM circuit_power WHERE id = '$id' AND timestamp >= '$start' AND timestamp < '$end'")->fetch_object();
		
		if($result->max == "") {
			$b1 = true;
			$b2 = true;
			$b3 = true;
		} else {
			//there are values
			$newMin = $result->min;
			$newMax = $result->max;
			
			$string  = 'SELECT n, min'.$h.', max'.$h.' FROM circuit_avg WHERE id="'.$id.'"';
			$result2 = $this->db->query($string)->fetch_array();
			
			$n = $result2["n"];
			$min = $result2["min".$h];
			$max = $result2["max".$h];
			
			if($n > 0) {
				$newMin = ($newMin+ $n*$min)/($n+1);
				$newMax = ($newMax+ $n*$max)/($n+1);
			}
			
			$string = 'UPDATE circuit_avg Set min'.$h.' = '.$newMin.' WHERE id="'.$id.'"';				
			$b1 = $this->db->query($string);
			$string = 'UPDATE circuit_avg Set max'.$h.' = '.$newMax.' WHERE id="'.$id.'"';
			$b2 = $this->db->query($string);
			$b3 = true;
		}
		if($h==23) {
			$b3 = $this->db->query("UPDATE circuit_avg Set n = n+1 WHERE id='$id'");
			/*
				code to delete all power data
			*/
		}
		
		if($b1 && $b2 && $b3)
			return true;
		else
			return false;		
	}
	
	public function getCircuitAvrgData($id) {
		$result = $this->db->query("SELECT * FROM circuit_avg WHERE id = '$id'")->fetch_object();
		
		$data = array();
		
		$data[] = $result->min0;
		$data[] = $result->min1;
		$data[] = $result->min2;
		$data[] = $result->min3;
		$data[] = $result->min4;
		$data[] = $result->min5;
		$data[] = $result->min6;
		$data[] = $result->min7;
		$data[] = $result->min8;
		$data[] = $result->min9;
		$data[] = $result->min10;
		$data[] = $result->min11;
		$data[] = $result->min12;
		$data[] = $result->min13;
		$data[] = $result->min14;
		$data[] = $result->min15;
		$data[] = $result->min16;
		$data[] = $result->min17;
		$data[] = $result->min18;
		$data[] = $result->min19;
		$data[] = $result->min20;
		$data[] = $result->min21;
		$data[] = $result->min22;
		$data[] = $result->min23;
		$data[] = $result->max0;
		$data[] = $result->max1;
		$data[] = $result->max2;
		$data[] = $result->max3;
		$data[] = $result->max4;
		$data[] = $result->max5;
		$data[] = $result->max6;
		$data[] = $result->max7;
		$data[] = $result->max8;
		$data[] = $result->max9;
		$data[] = $result->max10;
		$data[] = $result->max11;
		$data[] = $result->max12;
		$data[] = $result->max13;
		$data[] = $result->max14;
		$data[] = $result->max15;
		$data[] = $result->max16;
		$data[] = $result->max17;
		$data[] = $result->max18;
		$data[] = $result->max19;
		$data[] = $result->max20;
		$data[] = $result->max21;
		$data[] = $result->max22;
		$data[] = $result->max23;
		
		return $data;
	}
	
	public function delOldTimeSeriesData($t, $c) {
		$begin = strtotime(date("m/d/Y", time()));
		$begin = $begin - ($t*86400);
		
		return $this->db->query("DELETE FROM circuit_power WHERE timestamp < '$begin' AND id = '$c'");
	}
	
	public function getDevice($dev) {
		$query = "SELECT owner, name, credit, category, room FROM devices WHERE device = '".$dev."'";
		$result = $this->db->query($query);
		
		if($result->num_rows == 1) {
			$row = $result->fetch_array();
			return $row;
		}
		else return -1;
	}
	
	public function existsMeterData($authkey, $input) {
		
		$table = "meter_consumption";
		if($input == "p")
			$table = "meter_production";
		
		$query = "SELECT 'a' FROM ".$table." WHERE authkey = '".$authkey."'";
		$result = $this->db->query($query);
		
		return $result->num_rows == 1;
	}
	
	public function addMeterData($authkey, $input, $value, $timestamp) {
		$table = "meter_consumption";
		if($input == "p")
			$table = "meter_production";
		
		$query = "INSERT INTO ".$table." (authkey, reading, timestamp) VALUES ('".$authkey."', ".$value.", ".$timestamp.")";
		
		return $this->db->query($query);
	}
	
	public function updMeterData($authkey, $input, $value, $timestamp) {
		$table = "meter_consumption";
		if($input == "p")
			$table = "meter_production";
		
		$query = "UPDATE ".$table." Set reading = ".$value." WHERE authkey = '".$authkey."'";
		$b1 = $this->db->query($query);
		
		$query = "UPDATE ".$table." Set timestamp = ".$timestamp." WHERE authkey = '".$authkey."'";
		$b2 = $this->db->query($query);
		
		return $b1 && $b2;
	}
	
	public function getMeterData($authkey, $output) {
		$table = "meter_consumption";
		if($output == "p")
			$table = "meter_production";
		
		$query = "SELECT reading, timestamp FROM ".$table." WHERE authkey = '".$authkey."'";
		$result = $this->db->query($query)->fetch_object();
		
		$data = array();
		
		$data["reading".strtoupper($output)] = $result->reading;
		$data["timestamp".strtoupper($output)] = $result->timestamp;
		
		
		return $data;
	}

	/*
	    SQL-Functions for Challenge-Goal-Setting-Widget
	    added by Wascher Manuel
	*/

	/* old version */
    public function getTimesOfUnderraining($user)
    {
        $query_1 = "SELECT times_of_underraining FROM challenges WHERE timestamps=(";
        $query_2 = "SELECT MAX(timestamps) FROM challenges WHERE username=".$user.")";
        $query = $query_1.$query_2;
        $result = $this->db->query($query)->fetch_object();

        if($result->row_num == 1)
            return $result->times_of_underraining;
        else
            return false;
    }

    public function insertChallenge($user, $timestamp, $category, $challenge_begin, $challenge_end, $incentive, $challenge_decision, $max_devs_consumption, $cur_devs_consumption, $times_of_underraining, $challenge_finished)
    {
        $query_1 = "INSERT INTO challenges (username, timestamps, category, challenge_begin, challenge_end, incentive, challenge_decision, max_devices_consumption, current_devices_consumption, times_of_underraining, challenge_finished) ";
        $query_2 = "VALUES ('$user','$timestamp','$category','$challenge_begin','$challenge_end','$incentive','$challenge_decision','$max_devs_consumption','$cur_devs_consumption','$times_of_underraining','$challenge_finished')";
        return $this->con->query($query_1.$query_2);
    }

    public function getNewIncentive($category)
    {
        $query = "SELECT incentive_description FROM incentive_config WHERE category=".$category.";";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_num == 4)
        {
            $rand_incentive = rand(0,3);
            return $result->incentive_description[$rand_incentive];
        }
        else
            return false;
    }

    public function get_current_Incentive($user, $timestamp)
    {
        $query = "SELECT incentive FROM challenges WHERE username='".$user."' AND timestamps=".$timestamp.";";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_num == 1)
            return $result->incentive;
        else
            return false;
    }

    public function updateChDesFin($user, $timestamp, $column, $value)
    {
        $query = "UPDATE challenges SET ".$column."='".$value."' WHERE username='".$user."' AND timestamps=".$timestamp.";";
        return $this->db->query($query);
    }

    public function isCTInside($user, $timestamp)
    {
        $query_1 = "SELECT begin_challenge, end_challenge FROM challenges WHERE timestamps=(";
        $query_2 = "SELECT MAX(timestamps) FROM challenges WHERE username=".$user.")";
        $query = $query_1.$query_2;
        $result = $this->db->query($query)->fetch_object();
        $result_array = array();

        if(($result->row_num == 1) && ($result->begin_challenge < $timestamp) && ($result->end_challenge > $timestamp))
        {
            $result_array[0] = true;
            $result_array[1] = 0;
            return $result_array;
        }
        elseif (($result->row_num == 1) && ($timestamp < $result->begin_challenge))
        {
            $result_array[0] = false;
            $result_array[1] = true;
            return $result_array;
        }
        elseif (($result->row_num == 1) && ($timestamp > $result->end_challenge))
        {
            $result_array[0] = false;
            $result_array[1] = false;
            return $result_array;
        }
        else
        {
            $result_array[0] = false;
            $result_array[1] = 0;
            return $result_array;
        }
    }

    public function getChTimestamp($user)
    {
        $query = "SELECT MAX(timestamps) FROM challenges WHERE username='".$user."'";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_num == 1)
            return $result->timestamps;
        else
            return -1;
    }

    public function getCurrentActiveDev($user, $ch_timestamp)
    {
        $query = "SELECT device_id, active FROM challenge_devices WHERE username='".$user."' AND timestamps=".$ch_timestamp.";";
        $result = $this->db->query($query)->fetch_object();
        $device_id = "";
        $current_row = 0;

        if($result->row_count == 3)
        {
            while (($current_row < $result->row_count) && ($device_id == ""))
            {
                if ($result[$current_row]->active == "Yes")
                    $device_id = $result[$current_row]->device_id;
                else
                    $current_row = $current_row + 1;
            }

            return $device_id;
        }
        else
            return -1;
    }

    public function getCurrentDevsCons($user, $ch_timestamp)
    {
        $query = "SELECT current_devices_consumption FROM challenges WHERE username='".$user."' AND timestamps=".$ch_timestamp.";";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_num == 1)
            return $result->current_devices_consumption;
        else
            return -1;
    }

    public function setDevAsCurrentActive($user, $ch_timestamp)
    {
        $query_1 = "SELECT device_id FROM challenge_devices WHERE username='".$user."' AND begin_time=(";
        $query_2 = "SELECT challenge_begin FROM challenges WHERE username='".$user."' AND timestamps=".$ch_timestamp.")";
        $result = $this->db->query($query_1.$query_2)->fetch_object();

        if($result->row_count == 1)
        {
            $this->db->query("UPDATE challenge_devices SET active_device='Yes' WHERE username='".$user."' AND timestamps=".$ch_timestamp." AND device_id='".$result->device_id."'");
            return $result->device_id;
        }
        else
            return -1;
    }

    public function getNewCurrentActiveDev($current_active_dev, $ch_timestamp, $current_time)
    {
        $query_1 = "SELECT device_id FROM challenge_devices WHERE device_id='".$current_active_dev."' AND timestamps=".$ch_timestamp." AND end_time>".$current_time.";";
        $result_1 = $this->db->query($query_1)->fetch_object();
        $data = array();

        if($result_1->row_count == 1)
        {
            $data[0] = $result_1->device_id;
            $data[1] = "No end";
            return $data;
        }
        elseif ($result_1->row_count == 0)
        {
            $query_2 = "SELECT next_challenge_device FROM challenge_devices WHERE device_id='".$current_active_dev."' AND timestamps=".$ch_timestamp.";";
            $result_2 = $this->db->query($query_2)->fetch_object();

            if($result_2->next_challenge_device != "0")
            {
                $this->db->query("UPDATE challenge_devices SET active_device='No' WHERE timestamps=".$ch_timestamp." AND device_id='".$current_active_dev."'");
                $this->db->query("UPDATE challenge_devices SET active_device='Yes' WHERE timestamps=".$ch_timestamp." AND device_id='".$result_2->next_challenge_device."'");
                $data[0] = $result_2->next_challenge_device;
                $data[1] = "No end";
                return $data;
            }
            else
            {
                $this->db->query("UPDATE challenge_devices SET active_device='No' WHERE timestamps=".$ch_timestamp." AND device_id='".$current_active_dev."'");
                $data[0] = $current_active_dev;
                $data[1] = "challenge end";
                return $data;
            }
        }
        else
            return -1;
    }

    public function getActiveDevName($user, $current_active_dev)
    {
        $query = "SELECT name FROM devices WHERE owner='".$user."' AND device='".$current_active_dev."'";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_count == 1)
            return $result->name;
        else
            return -1;
    }

    public function getCurrActiveDevCons($user, $current_active_dev, $ch_timestamp, $current_time)
    {
        $query_1 = "SELECT last_control FROM challenge_devices WHERE device_id='".$current_active_dev."' AND timestamps=".$ch_timestamp.";";
        $query_2 = "SELECT consumption FROM consumptionevents WHERE owner='".$user."' AND device='".$current_active_dev."' AND start>=".$query_1." AND start<=".$current_time.";";
        $result = $this->db->query($query_2)->fetch_object();

        if($result->row_count >= 1)
        {
            $consumption_sum = 0;
            $i = 0;

            while ($i < $result->row_count)
            {
                $consumption_sum = $consumption_sum + $result->consumption[$i];
                $i = $i + 1;
            }

            return $consumption_sum;
        }
        else
            return -1;
    }

    public function getMaxDevsConsumption($user, $ch_timestamp)
    {
        $query = "SELECT max_devices_consumption FROM challenges WHERE username='".$user."' AND timestamps=".$ch_timestamp.";";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_count == 1)
            return $result->max_devices_consumption;
        else
            return -1;
    }

    public function updateChallengeCons($user, $ch_timestamp, $current_devices_consumption)
    {
        $query = "UPDATE challenges SET current_devices_consumption='".$current_devices_consumption."' WHERE username='".$user."' AND timestamps=".$ch_timestamp.";";
        return $this->db->query($query);
    }

    public function getChallengeEnd($user, $ch_timestamp)
    {
        $query = "SELECT challenge_end FROM challenges WHERE username='".$user."' AND timestamps=".$ch_timestamp.";";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_count == 1)
            return $result->challenge_end;
        else
            return -1;
    }

    public function getAvgTwoDaysHHConsumption($user, $begin, $end)
    {
        $query = "SELECT consumptions FROM consumptionevents WHERE owner='".$user."' AND start BETWEEN (".$begin." AND ".$end.")";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_count >= 1)
        {
            $sum = 0;
            for ($i = 0; $i < $result->row_count; $i++)
                $sum = $sum + $result->consumptions[$i];
            return $sum/2;
        }
        else
            return -1;
    }

    public function getMaxHHConsumption()
    {
        $query = "SELECT max_devices_consumption FROM challenges WHERE username='admin' AND category=1";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_count == 1)
            return $result->max_devices_consumption;
        else
            return -1;
    }

    public function getDiffRates()
    {
        $query = "SELECT max_difference FROM category_config";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_count > 1)
            return $result->max_difference;
        else
            return -1;
    }

    public function getUserDeviceIds($user)
    {
        $query = "SELECT device FROM devices WHERE owner='".$user."'";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_count >= 1)
            return $result->device;
        else
            return -1;
    }

    public function getAvgUserDeviceConsumptions($user, $user_dev_id, $current_time, $timestamp_two_days)
    {
        $query = "SELECT consumption FROM consumptionevents WHERE owner='".$user."' AND device='".$user_dev_id."' AND start BETWEEN (".$current_time." AND ".$timestamp_two_days.")";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_count >= 1)
        {
            $consumption_sum = 0;

            for($i = 0; $i < $result->row_count; $i++)
                $consumption_sum = $consumption_sum + $result->consumption[$i];

            $consumption_avg = $consumption_sum/2;

            return $consumption_avg;
        }
        else
            return -1;
    }

    public function getDevCategory($user, $worst_device)
    {
        $query = "SELECT category FROM devices WHERE owner='".$user."' AND device='".$worst_device."'";
        $result = $this->db->query($query)->fetch_object();

        if($result->row_count == 1)
            return $result->category;
        else
            return -1;
    }

    public function getMaxDevConsumption($category_id)
    {
        $query = "SELECT max_dev_consumption FROM nilm_devices WHERE id=".$category_id.";";
        $result = $this->db->query($query);

        if($result->num_rows == 1)
            return $result->max_dev_consumption;
        else
            return -1;
    }

    /* new version */
    public function getLastMonthConsumption($user, $begin, $end)
    {
        $query = "SELECT SUM(consumption) AS wholeConsumption FROM consumtionevents WHERE owner=".$user." AND start BETWEEN ".$begin." AND ".$end.";";
        $result = $this->db->query($query);

        if ($result->num_rows >= 1) {
            $whole_consumption = $result->fetch_object();
            return $whole_consumption->wholeConsumption;
        }
        else
            return -1;
    }

    public function addChallengeEqualDevice($model_id, $year_of_manufacture, $consumption, $category_id)
    {
        $query_1 = "INSERT INTO challenge_equal_devices (type_id, year_ofmanufacture, consumption, cathegory) ";
        $query_2 = "VALUES ('$model_id','$year_of_manufacture','$consumption','$category_id')";
        return $this->con->query($query_1.$query_2);
    }

    public function addChallengeMessage($id, $title, $message, $message_url) {
        $query_1 = "INSERT INTO challenge_message (id, title, message, message_url) ";
        $query_2 = "VALUES ('$id','$title','$message','$message_url')";
        return $this->con->query($query_1.$query_2);
    }

    public function addMultChallengeMessages($values_insert_stream) { return $this->db->query($values_insert_stream); }

    public function getChallengeMessage($title, $get_count) {
        $query = "SELECT * FROM challenge_message WHERE title='".$title."';";
        $result = $this->db->query($query);
        $result_array = array();

        if(($result->num_rows == 1) && ($get_count == 0)) {
            $result_object = $result->fetch_object();
            $result_array[0] = $result_object->id;
            $result_array[1] = $result_object->title;
            $result_array[2] = $result_object->message;
            $result_array[3] = $result_object->message_url;
            return $result_array;
        }
        elseif (($result->num_rows == 1) && ($get_count == 1))
            return $result->num_rows;
        else
            return -1;
    }

    public function getCountOfChallengeMessages() {
        $query = "SELECT * FROM challenge_message;";
        $result = $this->db->query($query);

        if($result->num_rows > 0)
            return $result->num_rows;
        else
            return 0;
    }

    public function getChallengeMessageFromID($message_id) {
        $query = "SELECT title FROM challenge_message WHERE id='".$message_id."';";
        $result = $this->db->query($query);

        if($result->num_rows == 1) {
            $result_object = $result->fetch_object();
            return $result_object->title;
        }
        else
            return -1;
    }

    public function addChallengeUserMessage($message_id, $user, $timestamp) {
        $query_1 = "INSERT INTO challenge_user_message (message_id, username, now) ";
        $query_2 = "VALUES ('$message_id','$user','$timestamp');";
        return $this->db->query($query_1.$query_2);
    }

    public function getLastChallengeUserMessage($user) {
        $date_format_morning = DateTime::createFromFormat('d.m.y H:i:s', date('d.m.y').' 00:00:00'); // creates an instance of the DateTime-object, with a special format
        $today_first_timestamp = $date_format_morning->getTimestamp();
        $date_format_night = DateTime::createFromFormat('d.m.y H:i:s', date('d.m.y').' 23:59:59');
        $today_last_timestamp = $date_format_night->getTimestamp();

        $query = "SELECT message_id, timestamp FROM challenge_user_message WHERE username='".$user."' AND timestamp BETWEEN ".$today_first_timestamp." AND ".$today_last_timestamp." ORDER BY timestamp ASC;";
        $result = $this->db->query($query);

        if($result->num_rows == 1) {
            $result_object = $result->fetch_object();
            return $result_object->message_id;
        }
        elseif($result->num_rows > 1) {
            $id_array = array();
            $i = 0;

            while (($result_object = $result->fetch_object(MYSQLI_BOTH)) != false) {
                $id_array[$i] = $result_object->message_id;
                $i++;
            }

            return $id_array[count($id_array) - 1];
        }
        else
            return -1;
    }

    public function getChallengeUsers($get_user_list) {
        $query = "SELECT * FROM user WHERE username <> 'Administrator';";
        $result = $this->db->query($query);

        if(($result->num_rows >= 1) && ($get_user_list == 0))
            return "EXISTS";
        elseif (($result->num_rows >= 1) && ($get_user_list == 1)) {
            $result_object = $result->fetch_object();
            $return_array = array();

            for($i = 0; $i < $result->num_rows; $i++)
                $return_array[$i] = $result_object->username[$i];

            return $return_array;
        }
        else
            return -1;
    }

    public function addChallengeEnergyLevel($id, $title, $description, $energy_level) {
        $query_1 = "INSERT INTO challenge_energy_level (id, title, description, energy_level) ";
        $query_2 = "VALUES ('$id','$title','$description','$energy_level')";
        return $this->con->query($query_1.$query_2);
    }

    public function getChallengeEnergyLevels() {
        $query = "SELECT * FROM challenge_energy_level ORDER BY title ASC;";
        $result = $this->db->query($query);

        if($result->num_rows >= 1) {
            $i = 0;
            $return_array = array();

            while (($result_array = $result->fetch_array(MYSQLI_BOTH)) != false) { // returns one row of the proceeded sql-query as array
                for ($j = 0; $j < count($result_array); $j++)
                    $return_array[$i][$j] = $result_array[$j];
            }
            return $return_array;
        }
        else
            return -1;
    }

    public function getLevelCount() {
        $query = "SELECT COUNT(id) AS level_id_count FROM challenge_energy_level;";
        $result = $this->db->query($query);
        $result_object = $result->fetch_object();

        if($result->num_rows == 1)
            return $result_object->level_id_count;
        else
            return -1;
    }

    public function getLevelID($title) {
        $query = "SELECT id FROM challenge_energy_level WHERE title='$title';";
        $result = $this->db->query($query);
        $result_object = $result->fetch_object();

        if($result->num_rows == 1)
            return $result_object->id;
        else
            return -1;
    }

    public function getLevelTitles() {
        $query = "SELECT title FROM challenge_energy_level;";
        $result = $this->db->query($query);
        $result_object = $result->fetch_object();
        $title_array = array();

        if($result->num_rows >= 1)
        {
            for ($i = 0; $i < $result->num_rows; $i++)
                $title_array[$i] = $result_object->title[$i];

            return $title_array;
        }
        else {
            $title_array[0] = -1;
            return $title_array;
        }

    }

    public function addChallengeLevelUser($level_id, $user, $timestamp) {
        $query_1 = "INSERT INTO challenge_level_user (level_id, username, timestamp) ";
        $query_2 = "VALUES ('$level_id','$user','$timestamp')";
        return $this->con->query($query_1.$query_2);
    }

    public function getChallengeLevelUser($user) {
        $current_timestamp = date();
        $timestamp_5_weeks_ago = date() - (3600 * 24 * 7 * 5);
        $query = "SELECT level_id FROM challenge_level_user WHERE username='$user' AND timestamp BETWEEN ".$timestamp_5_weeks_ago." AND ".$current_timestamp.";";
        $result = $this->db->query($query);
        $result_object = $result->fetch_object();

        if($result->num_rows == 1)
            return $result_object->level_id;
        else
            return -1;
    }

    public function addChallengeIncentive($id, $title, $expiration_date, $incentive_url, $level_id) {
        $query_1 = "INSERT INTO challenge_incentive (id, expiration_date, incentive_url, level_id) ";
        $query_2 = "VALUES ('$id','$title','$expiration_date','$incentive_url','$level_id')";
        return $this->con->query($query_1.$query_2);
    }

    public function getIncentiveCount($level_title) {
        $level_id = $this->getLevelID($level_title);
        $query = "SELECT COUNT(id) AS inc_id_count FROM challenge_incentive WHERE level_id='$level_id';";
        $result = $this->db->query($query);
        $result_object = $result->fetch_object();

        if($result->num_rows == 1)
            return $result_object->inc_id_count;
        else
            return -1;
    }

    public function addChallenge($id, $title, $common_description, $level_id) {
        $query_1 = "INSERT INTO challenges (id, title, common_description, evel_id) ";
        $query_2 = "VALUES ('$id','$title','$common_description','$level_id')";
        return $this->con->query($query_1.$query_2);
    }

    public function getChallengeCount($level_title) {
        $level_id = $this->getLevelID($level_title);
        $query = "SELECT COUNT(id) AS ch_id_count FROM challenge WHERE level_id='$level_id';";
        $result = $this->db->query($query);
        $result_object = $result->fetch_object();

        if($result->num_rows == 1)
            return $result_object->ch_id_count;
        else
            return -1;
    }

    public function addChallengeUser($challenge_id, $user, $timestamp, $special_description, $start, $end, $decision, $degree) {
        $query_1 = "INSERT INTO challenge_user (challenge_id, username, timestamp, special_description, start, end, decision, degree) ";
        $query_2 = "VALUES ('$challenge_id','$user','$timestamp','$special_description','$start','$end','$decision','$degree')";
        return $this->con->query($query_1.$query_2);
    }

    public function getLastUserChallenge($user) {
        $current_timestamp = time();
        $timestamp_5_weeks_ago = time() - (3600 * 24 * 7 * 5);
        $query = "SELECT * FROM challenge_user WHERE username='$user' AND timestamp BETWEEN ".$timestamp_5_weeks_ago." AND ".$current_timestamp.";";
        $result = $this->db->query($query);
        $result_object = $result->fetch_object();

        if($result->num_rows == 1)
            return $result_object;
        else
            return -1;
    }

    public function getCurrentUserChallenge($user) {
        // creates the variables for the BETWEEN-Operator
        $date_format_morning = DateTime::createFromFormat('d.m.y H:i:s', (date('d.m.y')).' 00:00:00');
        $first_timestamp = $date_format_morning->getTimestamp();
        $date_format_night = DateTime::createFromFormat('d.m.y H:i:s', (date('d.m.y') + 1).' 00:00:00');
        $last_timestamp = $date_format_night->getTimestamp();

        $query = "SELECT * FROM challenge_user WHERE username='$user' AND timestamp BETWEEN ".$first_timestamp." AND ".$last_timestamp.";";
        $result = $this->db->query($query);
        $result_object = $result->fetch_object();

        if($result->num_rows == 1)
            return $result_object;
        else
            return -1;
    }

    /*
	    SQL-Functions of Test-Data-Input-Widget
	    added by Wascher Manuel
	*/
    public function getAllUsernames() {
        $result = $this->db->query("SELECT username FROM user");

        if($result->num_rows >= 1) {
            $return_array = array();
            $i = 0;

            while($current_row = $result->fetch_object()) {
                $return_array[$i] = $current_row->username;
                $i++;
            }

            return $return_array;
        }
        else
            return -1;
    }

    public function delSpecificTDIDataContent($table, $column1, $user, $column2, $text) {
        return $this->db->query("DELETE FROM ".$table." WHERE ".$column1." = '".$user."' AND ".$column2." = '".$text."'");
    }

    public function getBuildingID($user, $building)
    {
        $query = "SELECT id FROM users_buildings WHERE username='".$user."' AND name='test_".$building."';";
        $result = $this->db->query($query);
        $selected_building = $result->fetch_object();

        if($result->num_rows == 1)
            return $selected_building->id;
        else
            return -1;
    }

    public function getTestBuildingsCount($user, $building)
    {
        $query = "SELECT id FROM users_buildings WHERE username='".$user."' AND name='test_".$building.substr($user, 0, 4)."';";
        $result = $this->db->query($query);
        $selected_buildings_count = $result->fetch_object();

        if($result->num_rows >= 1)
            return $selected_buildings_count->id;
        else
            return -1;
    }

    public function addEventTDI($values_insert_stream) { return $this->db->query($values_insert_stream); }

    public function updateDeviceModel($user, $device, $mode, $current_hour, $switched_on_count) {
        $query = "UPDATE device_model SET h".$current_hour."=".$switched_on_count." WHERE username='".$user."' AND device='".$device."' AND p=".$mode.";";
        return $this->db->query($query);
    }

    public function getExistingAggCons($user, $timestamp) {
        $query = "SELECT consumption FROM production WHERE username='".$user."' AND timestamp=".$timestamp.";";
        $result = $this->db->query($query);
        $return_array = array();

        if($result->num_rows == 1) {
            $selected_cons = $result->fetch_object();
            $return_array[0] = "EXISTS";
            $return_array[1] = $selected_cons->consumption;
            return $return_array;
        }
        else {
            $return_array[0] = "NOT EXISTS";
            $return_array[1] = 0;
            return $return_array;
        }
    }

    public function updateAggData($user, $timestamp, $consumption) {
        $query = "UPDATE production SET consumption=".$consumption." WHERE username='".$user."' AND timestamp=".$timestamp.";";
        return $this->db->query($query);
    }

    public function getCircuitID($user, $building) {
        $query_building_id = "SELECT id FROM users_buildings WHERE username='".$user."' AND name='test_".$building.substr($user, 0, 4)."';";
        $result_building = $this->db->query($query_building_id);

        if($result_building->num_rows == 1) {
            $building_id_object = $result_building->fetch_object();

            $query_circuit_id = "SELECT id FROM circuit WHERE map_id='".$building_id_object->id."' AND type='b';";
            $result_circuit = $this->db->query($query_circuit_id);

            if($result_circuit->num_rows == 1) {
                $circuit_id_object = $result_circuit->fetch_object();
                return $circuit_id_object->id;
            }
            else
                return -1;
        }
        else
            return -1;
    }

    public function addCirPowerEventTDI($values_insert_stream) { return $this->db->query($values_insert_stream); }

    public function getCircuitAvg($id, $hour) {
        $query = "SELECT min".$hour." AS min, max".$hour." AS max FROM circuit_avg WHERE id='".$id."';";
        $result = $this->db->query($query);

        if($result->num_rows == 1) {
            $min_max_array = array();
            $result_object = $result->fetch_object();

            $min_max_array["min"] = $result_object->min;
            $min_max_array["max"] = $result_object->max;

            return $min_max_array;
        }
        else
            return -1;
    }

    public function updateCirAvgMinMax($id, $hour, $min, $max) {
        $query_min = "UPDATE circuit_avg SET min".$hour."=".$min." WHERE id='".$id."';";
        $this->db->query($query_min);
        $query_max = "UPDATE circuit_avg SET max".$hour."=".$max." WHERE id='".$id."';";
        return $this->db->query($query_max);
    }

    public function updateCirAvgRecDays($id, $new_n) {
        $query = "UPDATE circuit_avg SET n=".$new_n." WHERE id='".$id."';";
        return $this->db->query($query);
    }

    public function getCirAvgRecDays($id) {
        $query = "SELECT n FROM circuit_avg WHERE id='".$id."';";
        $result = $this->db->query($query);

        if($result->num_rows == 1) {
            $selected_cirID = $result->fetch_object();
            return $selected_cirID->n;
        }
        else
            return -1;
    }

    public function updateDevType($dev_id, $type) {
        $query = "UPDATE devices SET category=".$type." WHERE id='".$dev_id."';";
        return $this->db->query($query);
    }

    public function delCircuitData($cir_id) {
        $query_del_cir_power = "DELETE FROM circuit_power WHERE id='".$cir_id."';";
	    $this->db->query($query_del_cir_power);
	    $query_del_cir_avg = "DELETE FROM circuit_avg WHERE id='".$cir_id."';";
	    $this->db->query($query_del_cir_avg);
	    $query_del_circuit = "DELETE FROM circuit WHERE id='".$cir_id."';";
        return $this->db->query($query_del_circuit);
    }

    public function getAuthkey($user) {
        $query = "SELECT authkey FROM user WHERE username='".$user."';";
        $result = $this->db->query($query);

        if($result->num_rows == 1) {
            $selected_authkey = $result->fetch_object();
            return $selected_authkey->authkey;
        }
        else
            return -1;

    }
}
