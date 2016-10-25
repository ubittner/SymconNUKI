<?

######### NUKI Bridge Module for IP-Symcon 4.x ########## 

/**
 * @file 		module.php
 * 
 * @author 		Ulrich Bittner
 * @copyright  (c) 2016
 * @version 	1.00
 * @date: 		2016-10-25, 21:00
 *
 * @see        https://github.com/ubittner/SymconNUKI
 *
 * @bridgeapi	Version 1.3, 2016-10-07
 *
 * @guids 		{752C865A-5290-4DBE-AC30-01C7B1C3312F} NUKILibrary
 *          	{B41AE29B-39C1-4144-878F-94C0F7EEC725} NUKIBridge
 *          	{73188E44-8BBA-4EBF-8BAD-40201B8866B9} NUKI Bridge (I/O) TX (I)
 *          	{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} NUKI Bridge (I/O) RX (CR)
 *          	{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14} NUKISmartLock
 */


class NUKIBridge extends IPSModule
{
	private $NUKIBridgeIP = "";
	private $NUKIBridgePort = "";
	private $NUKIBridgeAPIToken = "";
	private $NUKISmartLockCategory = 0;

	public function Create()
	{
		parent::Create();
		
		$this->RegisterPropertyString("NUKIBridgeIP", "");
		$this->RegisterPropertyString("NUKIBridgePort", "8080");
		$this->RegisterPropertyString("NUKIBridgeAPIToken", "");
		$this->RegisterPropertyInteger("NUKISmartLockCategory", 0);
	}

	public function ApplyChanges()
	{
		parent::ApplyChanges();
		
		$this->validateBridgeConfiguration();
	}


	#####################################################################################################################################
	## start of modul functions 																												  						  ##
	#####################################################################################################################################


	########## public functions ##########
	

	/**
	 *		NUKI_showBridgeInfo(int $BridgeInstanceId)
	 *		returns all smartlocks in range and some device information of the bridge itself
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
	 *		NUKI_showBridgeLog(int $BridgeInstanceId)
	 *		retrieves the log of the Bridge
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
	 *		NUKI_clearBridgeLog(int $BridgeInstanceId)
	 *		clears the log of the bridge
	 */
	public function clearBridgeLog()
	{
		$Endpoint = "/clearlog?token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
	}


	/**
	 *		NUKI_updateBridgeFirmware(int $BridgeInstanceId)
	 *		immediately checks for a new firmware update and installs it
	 */
	public function updateBridgeFirmware()
	{
		$Endpoint = "/fwupdate?token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
	}


	/**
	 *		NUKI_getSmartLocks(int $BridgeInstanceId)
	 *		returns a list of all available smartlocks
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
	 *		NUKI_syncSmartLocks(int $BridgeInstanceId)
	 *		sync smartlocks of the bridge
	 */
	public function syncSmartLocks ()
	{
		$SmartLockCategoryId = $this->GetSmartLockCategory();
		$SmartLocks = $this->getSmartLocks();
		if($SmartLocks) {
			foreach ($SmartLocks as $SmartLock) {
				$SmartLockUniqueId = $SmartLock["nukiId"];
				$SmartLockName = utf8_decode((string)$SmartLock["name"]);
				$SmartLockInstanceId = $this->getSmartLockInstanceIdByUniqueId($SmartLockUniqueId);
				if ($SmartLockInstanceId == 0) {
					$SmartLockInstanceId = IPS_CreateInstance($this->getSmartLockModuleGuid());
					IPS_SetProperty($SmartLockInstanceId, 'NUKISmartLockID', $SmartLockUniqueId);
				}
				IPS_SetProperty($SmartLockInstanceId, 'NUKISmartLockName', $SmartLockName);
				IPS_SetName($SmartLockInstanceId, $SmartLockName);
				IPS_SetParent($SmartLockInstanceId, $SmartLockCategoryId);
				if (IPS_GetInstance($SmartLockInstanceId)['ConnectionID'] <> $this->InstanceID) {
					@IPS_DisconnectInstance($SmartLockInstanceId);
					IPS_ConnectInstance($SmartLockInstanceId, $this->InstanceID);
				}
				IPS_ApplyChanges($SmartLockInstanceId);
			}
		}
		echo "Fetig!";
		IPS_LogMessage("SymconNUKI", "Syncronisierung abgeschlossen.");
	}


	/**
	 *		NUKI_getLockStateOfSmartLock(int $BridgeInstanceId, int $SmartLockUniqueId)
	 *		returns the current lock state of a given smartlock
	 */
	public function getLockStateOfSmartLock (int $SmartLockUniqueId)
	{
		$Endpoint = "/lockState?nukiId=".$SmartLockUniqueId."&token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
		if($BridgeData) {
			return $BridgeData;
		}
		/**
		 * 	response example
		 * 	locked
		 * 	{“state”: 1, “stateName”: “locked”, “batteryCritical”: false, success: “true”}
		 * 
		 * 	possible state values are:
		 * 	0 uncalibrated
		 * 	1 locked
		 * 	2 unlocked
		 * 	3 unlocked (lock ‘n’ go)
		 * 	4 unlatched
		 * 	5 locking
		 * 	6 unlocking
		 * 	7 unlatching
		 * 	254 motor blocked
		 * 	255 undefined
    	 */
	}


	/**
	 *		NUKI_setLockActionOfSmartLock(int $BridgeInstanceID, int $SmarLockUniqueId, int $LockAction)
	 *		performs a lock operation on the given smartlock
	 */
	public function setLockActionOfSmartLock (int $SmartLockUniqueId, int $LockAction)
	{
		/**
		 * 	$LockAction
		 * 	1 unlock
		 * 	2 lock
		 * 	3 unlatch
		 * 	4 lock ‘n’ go
		 * 	5 lock ‘n’ go with unlatch
		 */
		$Endpoint = "/lockAction?nukiId=".$SmartLockUniqueId."&action=".$LockAction."&token=";
		$BridgeData = $this->sendDataToBridge($Endpoint);
		if($BridgeData) {
			return $BridgeData;
		}
		/**
		 * 	response example
		 * 	{“success”: true, “batteryCritical”: false}
       */
	}


	########## private functions ##########


	/**
	 *		validates the configuration
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
	 *		gets the smartlock category
	 */
	private function getSmartLockCategory() 
	{
   	if ($this->NUKISmartLockCategory == "") {
   		$this->NUKISmartLockCategory = $this->ReadPropertyString('NUKISmartLockCategory');
    	}
    	return $this->NUKISmartLockCategory;
  	}


	/**
	 *		gets the ip-address, port and api token of the bridge
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
	 *		sends data to the bridge endpoint
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
	*		gets the guid of the smartlock module
	*/
	private function getSmartLockModuleGuid()
	{
		return "{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}";
	}


	/**
	 *		gets the instance id of a smartlock by his uniquie id 
	 */
	private function getSmartLockInstanceIdByUniqueId($UniqueId) {
	$SmartLockInstanceIds = IPS_GetInstanceListByModuleID($this->getSmartLockModuleGuid());
	foreach($SmartLockInstanceIds as $SmartLockInstanceId) {
		if (IPS_GetProperty($SmartLockInstanceId, "NUKISmartLockID") == $UniqueId) {
		  return $SmartLockInstanceId;
		}
	 }
  }

}
?>