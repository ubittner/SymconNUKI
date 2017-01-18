<?

######### NUKI Bridge Module for IP-Symcon 4.1 ##########

/**
 * @file 		module.php
 *
 * @author 		Ulrich Bittner
 * @license		CCBYNC4.0
 * @copyright  (c) 2016, 2017
 * @version 	1.01
 * @date: 		2017-01-18, 13:00
 *
 * @see        https://github.com/ubittner/SymconNUKI
 *
 * @bridgeapi	Version 1.03, 2016-10-07
 *
 * @guids 		{752C865A-5290-4DBE-AC30-01C7B1C3312F} NUKI Library
 *
 *          	{B41AE29B-39C1-4144-878F-94C0F7EEC725} NUKI Bridge
 *          	{73188E44-8BBA-4EBF-8BAD-40201B8866B9} NUKI Bridge (I/O) TX (I)
 *          	{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} NUKI Bridge (I/O) RX (CR)
 *
 *          	{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14} NUKI Smart Lock
 *
 * 				{8062CF2B-600E-41D6-AD4B-1BA66C32D6ED} NUKI Socket (Server Socket)
 *
 * @changelog	2017-01-18, 13:00, initial module script version 1.01
 *
 */


class NUKIBridge extends IPSModule
{
	private $NUKIBridgeIP = "";
	private $NUKIBridgePort = "";
	private $NUKIBridgeAPIToken = "";
	private $NUKISocketIP = "";
	private $NUKISocketPort = "";
	private $CallbackID = "";
	private $NUKISmartLockCategory = 0;


	public function Create()
	{
		parent::Create();

		$this->RegisterPropertyString("NUKIBridgeIP", "");
		$this->RegisterPropertyString("NUKIBridgePort", "8080");
		$this->RegisterPropertyString("NUKIBridgeAPIToken", "");
		$this->RegisterPropertyString("NUKISocketIP", "");
		$this->RegisterPropertyString("NUKISocketPort", "8081");
		$this->RegisterPropertyInteger("NUKICallbackID", "0");
		$this->RegisterPropertyInteger("NUKISmartLockCategory", 0);
	}

	public function ApplyChanges()
	{
		parent::ApplyChanges();

		// Connect to server socket
      $ServerSocketName = "NUKI Socket";
  		$ServerSockedID = @IPS_GetInstanceIDByName($ServerSocketName, 0);
  		if ($ServerSockedID <> 0) {
  			$this->ConnectParent("{8062CF2B-600E-41D6-AD4B-1BA66C32D6ED}");
  			$this->SetReceiveDataFilter(".*NukiBridge.*");
  		}

		$this->validateBridgeConfiguration();
	}


	#####################################################################################################################################
	## start of modul functions 																												  						  ##
	#####################################################################################################################################

	########## public functions ##########

	/**
	 *		NUKI_ReceiveData(int $BridgeInstanceID, $JSONString)
	 *  	Receives callback data from NUKI bridge via the server socket
	 */

	public function ReceiveData($JSONString)
	{
		$Data = json_decode($JSONString);
		IPS_LogMessage("ReceiveData", utf8_decode($Data->Buffer));
		$this->updateStateOfSmartLocks();
	}

	/**
	 *		NUKI_getBridgeInfo(int $BridgeInstanceID)
	 *  	Returns all smartlocks in range and some device information of the bridge itself
	 */

	public function getBridgeInfo()
	{
		$Endpoint = "/info?token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
		if($BridgeData) {
			return $BridgeData;
		}
	}

	/**
	 *		NUKI_getBridgeLog(int $BridgeInstanceID)
	 *  	Retrieves the log of the bridge
	 */

	public function getBridgeLog()
	{
		$Endpoint = "/log?token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
		if($BridgeData) {
			return $BridgeData;
		}
	}

	/**
	 *		NUKI_clearBridgeLog(int $BridgeInstanceID)
	 *  	Clears the log of the bridge
	 */

	public function clearBridgeLog()
	{
		$Endpoint = "/clearlog?token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
	}

	/**
	 *		NUKI_updateBridgeFirmware(int $BridgeInstanceID)
	 *  	Immediately checks for a new firmware update and installs it
	 */

	public function updateBridgeFirmware()
	{
		$Endpoint = "/fwupdate?token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
	}

	/**
	 *		NUKI_getSmartLocks(int $BridgeInstanceID)
	 *  	Returns a list of all available smartlocks
	 */

	public function getSmartLocks()
	{
		$Endpoint = "/list?token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
		if ($BridgeData) {
			return ($BridgeData);
		}
	}

	/**
	 *		NUKI_syncSmartLocks(int $BridgeInstanceID)
	 *		Syncs smartlocks of the bridge
	 */

	public function syncSmartLocks()
	{
		$SmartLockCategoryID = $this->GetSmartLockCategory();
		$SmartLocks = $this->getSmartLocks();
		if($SmartLocks) {
			foreach ($SmartLocks as $SmartLock) {
				$SmartLockUniqueID = $SmartLock["nukiId"];
				$SmartLockName = utf8_decode((string)$SmartLock["name"]);
				$SmartLockInstanceID = $this->getSmartLockInstanceIdByUniqueId($SmartLockUniqueID);
				if ($SmartLockInstanceID == 0) {
					$SmartLockInstanceID = IPS_CreateInstance($this->getSmartLockModuleGuid());
					IPS_SetProperty($SmartLockInstanceID, 'NUKISmartLockUID', $SmartLockUniqueID);
				}
				IPS_SetProperty($SmartLockInstanceID, 'NUKISmartLockName', $SmartLockName);
				IPS_SetName($SmartLockInstanceID, $SmartLockName);
				IPS_SetParent($SmartLockInstanceID, $SmartLockCategoryID);
				if (IPS_GetInstance($SmartLockInstanceID)['ConnectionID'] <> $this->InstanceID) {
					@IPS_DisconnectInstance($SmartLockInstanceID);
					IPS_ConnectInstance($SmartLockInstanceID, $this->InstanceID);
				}
				IPS_ApplyChanges($SmartLockInstanceID);
			}
		}
		echo "Smart Locks wurden abgeglichen / angelegt!";
		IPS_LogMessage("SymconNUKI", "Syncronisierung der Smart Locks abgeschlossen.");
	}

	/**
	 *		NUKI_getLockStateOfSmartLock(int $BridgeInstanceID, int $SmartLockUniqueID)
	 *		Returns the current lock state of a given smartlock
	 */

	public function getLockStateOfSmartLock(int $SmartLockUniqueID)
	{
		$Endpoint = "/lockState?nukiId=".$SmartLockUniqueID."&token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
		if($BridgeData) {
			return $BridgeData;
		}

		/**
		 *		Response example for a locked smart lock
		 *
		 *   	{“state”: 1, “stateName”: “locked”, “batteryCritical”: false, success: “true”}
		 *
		 *		Possible state values are:
		 *  	0 uncalibrated
		 *   	1 locked
		 *    2 unlocking
	 	 *   	3 unlocked
	 	 *    4 locking
		 *		5 unlatched
		 *		6 unlocked (lock ‘n’ go)
		 *		7 unlatching
		 *		254 motor blocked
		 *		255 undefined
    	 */
	}

	/**
	 *		NUKI_setLockActionOfSmartLock(int $BridgeInstanceID, int $SmarLockUniqueID, int $LockAction)
	 *  	Performs a lock operation on the given smartlock
	 */

	public function setLockActionOfSmartLock(int $SmartLockUniqueID, int $LockAction)
	{
		/**
		 * 	$LockAction
		 * 	1 unlock
		 * 	2 lock
		 * 	3 unlatch
		 * 	4 lock ‘n’ go
		 * 	5 lock ‘n’ go with unlatch
		 */

		$Endpoint = "/lockAction?nukiId=".$SmartLockUniqueID."&action=".$LockAction."&token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
		if($BridgeData) {
			return $BridgeData;
		}

		/**
		 *		Response example
		 * 	{“success”: true, “batteryCritical”: false}
       */
	}

	/**
	 *		NUKI_updateStateOfSmartLocks(int $BridgeInstanceID)
	 *  	updates the state of all smartlocks of a bridge
	 */

	public function updateStateOfSmartLocks()
	{
		$SmartLockInstanceIDs = IPS_GetInstanceListByModuleID($this->getSmartLockModuleGuid());
		foreach($SmartLockInstanceIDs as $SmartLockInstanceID) {
	    	if(IPS_GetInstance($SmartLockInstanceID)['ConnectionID'] == $this->InstanceID) {
	      	$SmartLockUniqueID = IPS_GetProperty($SmartLockInstanceID, "NUKISmartLockUID");
			   $SmartLockData = $this->getLockStateOfSmartLock($SmartLockUniqueID);
			   $BatteryState = $SmartLockData["batteryCritical"];
				$State = $SmartLockData["state"];
				$StateName = $SmartLockData["stateName"];
				switch ($State) {
					// switch off (locked) = false, switch on (unlocked) = true
					case 0:
						// uncalibrated
						$State = false;
						break;
					case 1:
						// locked
						$State = false;
						break;
					case 2:
						// unlocking
						$State = true;
						break;
					case 3:
						// unlocked
						$State = true;
						break;
					case 4:
						// locking
						$State = false;
						break;
					case 5:
						// unlatched
						$State = true;
						break;
					case 6:
						// unlocked (lock ‘n’ go)
						$State = true;
						break;
					case 7:
						// unlatching
						$State = true;
						break;
					default:
						$State = false;
						break;
				}
				$SmartLockSwitchObjectID = IPS_GetObjectIDByIdent("NUKISmartLockSwitch", $SmartLockInstanceID);
				$UpdateSmartLockSwitch = SetValue($SmartLockSwitchObjectID, $State);
				$SmartLockStateObjectID = IPS_GetObjectIDByIdent("NUKISmatLockStatus", $SmartLockInstanceID);
				$UpdateSmartLockState = SetValue($SmartLockStateObjectID, $StateName);
				$SmartLockBatteryStateObjectID = IPS_GetObjectIDByIdent("NUKISmartLockBatteryState", $SmartLockInstanceID);
				$UpdateSmartLockBatteryState = SetValue($SmartLockBatteryStateObjectID, $BatteryState);
			}
		}
  	}

  /**
	 *		NUKI_addCallback(int $BridgeInstanceID)
	 *  	Adds a callback of a bridge
	 */

  	public function addCallback ()
  	{
  		$CallbackServerIP = $this->ReadPropertyString("NUKISocketIP");
  		$CallbackServerPort = $this->ReadPropertyString("NUKISocketPort");
  		if (!empty($CallbackServerIP) && !empty($CallbackServerPort)) {
  			$this->createCallbackSocket();
  			$Endpoint = "/callback/add?url=http%3A%2F%2F".$CallbackServerIP."%3A".$CallbackServerPort."&token=";
			$BridgeData = $this->sendDataToBridge($Endpoint);
			if ($BridgeData) {
				return ($BridgeData);
			}
  		}
  		if (empty($CallbackServerIP) || empty($CallbackServerPort)) {
  			echo "Bitte IP-Adresse des IP-Symcon Servers und Port des NUKI Server Sockets eintragen!";
  		}
  	}

  	/**
	 *		NUKI_listCallback(int $BridgeInstanceID)
	 *  	Lists the callbacks of a bridge
	 */

  	public function listCallback ()
  	{
  		$Endpoint = "/callback/list?token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
		if ($BridgeData) {
			return ($BridgeData);
		}
  	}

	/**
	 *		NUKI_deleteCallback(int $BridgeInstanceID, int $CallbackID)
	 *		Deletes a callback of the bridge
	 */

  	public function deleteCallback (int $CallbackID)
  	{
  		$Endpoint = "/callback/remove?id=".$CallbackID."&token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
		if ($BridgeData) {
			return ($BridgeData);
		}
  	}

	########## protected functions ##########


	########## private functions ##########

	/**
	 *		Validates the bridge configuration
	 */

	private function validateBridgeConfiguration()
	{
		if ($this->ReadPropertyString("NUKIBridgeIP") == "" || $this->ReadPropertyString("NUKIBridgePort") == "" || $this->ReadPropertyString("NUKIBridgeAPIToken") == "") {
			$this->SetStatus(104);
		}
		else{
			$this->SetStatus(102);
		}
	}

	/**
	 *		Gets the smartlock category
	 */

	private function getSmartLockCategory()
	{
   	if ($this->NUKISmartLockCategory == "") {
   		$this->NUKISmartLockCategory = $this->ReadPropertyString('NUKISmartLockCategory');
    	}
    	return $this->NUKISmartLockCategory;
  	}

	/**
	 *	Gets the ip-address, port and api token of the bridge
	 */

	private function getBridgeAccessInformation()
	{
		if ($this->NUKIBridgeIP == "") {
			$this->NUKIBridgeIP = $this->ReadPropertyString("NUKIBridgeIP");
		}
		if ($this->NUKIBridgePort == "") {
			$this->NUKIBridgePort = $this->ReadPropertyString("NUKIBridgePort");
		}
		if ($this->NUKIBridgeAPIToken == "") {
			$this->NUKIBridgeAPIToken = $this->ReadPropertyString("NUKIBridgeAPIToken");
		}
		return array(  "NUKIBridgeIP"       => $this->NUKIBridgeIP,
							"NUKIBridgePort"     => $this->NUKIBridgePort,
							"NUKIBridgeAPIToken" => $this->NUKIBridgeAPIToken);
	}

	/**
	 *		Sends data to the bridge endpoint
	 */

	private function sendDataToBridge(int $Endpoint)
	{
		$Bridge = $this->getBridgeAccessInformation();
		$BridgeData = false;
		$cURLHandle = curl_init();
		curl_setopt_array($cURLHandle, array(
			CURLOPT_URL => "http://".$Bridge["NUKIBridgeIP"].":".$Bridge["NUKIBridgePort"].$Endpoint.$Bridge["NUKIBridgeAPIToken"],
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT, 20));
		$Response = curl_exec($cURLHandle);
		if ($Response !== false) {
			$BridgeData = json_decode($Response, true);
		}
		curl_close($cURLHandle);
		if ($BridgeData) {
			return $BridgeData;
		}
	}

  /**
   *		Gets the guid of the smartlock module
	*/

	private function getSmartLockModuleGuid()
	{
		return "{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}";
	}

	/**
	 *		Gets the instance id of a smartlock by his uniquie id
	 */

	private function getSmartLockInstanceIdByUniqueId($UniqueID)
	{
		$SmartLockInstanceIDs = IPS_GetInstanceListByModuleID($this->getSmartLockModuleGuid());
		foreach($SmartLockInstanceIDs as $SmartLockInstanceID) {
			if (IPS_GetProperty($SmartLockInstanceID, "NUKISmartLockUID") == $UniqueID) {
		  	return $SmartLockInstanceID;
			}
	 	}
  	}

	/**
	 *		Creates a NUKI server socket for callbacks
	 */

	private function createCallbackSocket()
	{
		$ServerSocketPort = $this->ReadPropertyString("NUKISocketPort");
		$ServerSocketModuleID = "{8062CF2B-600E-41D6-AD4B-1BA66C32D6ED}";
		$ServerSocketName = "NUKI Socket";
		$ServerSocketInstanceID = @IPS_GetInstanceIDByName($ServerSocketName, 0);
		if ($ServerSocketInstanceID === false) {
			$ServerSocketInstanceID = IPS_CreateInstance($ServerSocketModuleID);
			$SetInstanceName = IPS_SetName($ServerSocketInstanceID, $ServerSocketName);
		}
		$SetServerSocketPort =  IPS_SetProperty($ServerSocketInstanceID, "Port", $ServerSocketPort);
		$ActivateServerSocket =  IPS_SetProperty($ServerSocketInstanceID, "Open", true);
		IPS_ApplyChanges($ServerSocketInstanceID);
	}

}
?>
