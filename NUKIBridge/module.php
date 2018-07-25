<?

######### NUKI Bridge Module for IP-Symcon 5.0 ##########

/**
 * @file		module.php
 *
 * @author		Ulrich Bittner
 * @license		CCBYNC4.0
 * @copyright		(c) 2016, 2017, 2018
 * @version		1.02
 * @date:		2018-04-21, 12:30
 *
 * @see			https://github.com/ubittner/SymconNUKI
 *
 * @bridgeapi	Version 1.7, 30.03.2018
 *
 * @guids		Library
 * 				{752C865A-5290-4DBE-AC30-01C7B1C3312F}
 *
 *				Virtual I/O (Server Socket NUKI Callback)
 *				{018EF6B5-AB94-40C6-AA53-46943E824ACF} (CR:	IO_RX)
 *				{79827379-F36E-4ADA-8A95-5F8D1DC92FA9} (I: 	IO_TX)
 *
 *				Spliter (NUKI Bridge)
 *				{B41AE29B-39C1-4144-878F-94C0F7EEC725} (Module GUID)
 *
 * 				{79827379-F36E-4ADA-8A95-5F8D1DC92FA9} (PR:	IO_TX)
 *				{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} (CR: Device_RX)
 * 				{018EF6B5-AB94-40C6-AA53-46943E824ACF} (I:	IO_RX)
 *				{73188E44-8BBA-4EBF-8BAD-40201B8866B9} (I:	Device_TX)
 *
 *				Device (NUKI Smartlock)
 *				{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14} (Module GUID)
 *
 * 				{73188E44-8BBA-4EBF-8BAD-40201B8866B9} (PR: Device_TX)
 *				{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} (I: 	Device_RX)
 *
 * @changelog	2018-04-21, 12:30, rebuild for IP-Symcon 5.0
 * 				2017-04-19, 23:00, update to API Version 1.5 and some improvements
 * 				2017-01-18, 13:00, initial module script version 1.01
 *
 */

// Definitions
if (!defined('SMARTLOCK_MODULE_GUID')) {
    define("SMARTLOCK_MODULE_GUID", "{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}");
}

if (!defined('SERVER_SOCKET_GUID')) {
    define("SERVER_SOCKET_GUID", "{8062CF2B-600E-41D6-AD4B-1BA66C32D6ED}");
}


class NUKIBridge extends IPSModule
{

    public function Create()
    {
        parent::Create();


        // Register properties
        $this->RegisterPropertyString("BridgeIP", "");
        $this->RegisterPropertyInteger("BridgePort", "8080");
        $this->RegisterPropertyString("BridgeAPIToken", "");
        $this->RegisterPropertyInteger("SmartLockCategory", 0);
        $this->RegisterPropertyBoolean("UseCallback", false);
        $this->RegisterPropertyString("SocketIP", "");
        $this->RegisterPropertyInteger("SocketPort", "8081");
        $this->RegisterPropertyInteger("CallbackID", "0");
    }


    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $callback = false;
        if ($this->ReadPropertyBoolean("UseCallback") == true) {
            $callback = true;
            // Server socket
            $this->RequireParent(SERVER_SOCKET_GUID);
        }

        $ParentID = $this->GetParent();
        if ($ParentID > 0) {
            if (IPS_GetProperty($ParentID, 'Port') <> $this->ReadPropertyInteger('SocketPort')) {
                IPS_SetProperty($ParentID, 'Port', $this->ReadPropertyInteger('SocketPort'));
            }
            IPS_SetName($ParentID, "NUKI Socket");
            IPS_SetProperty($ParentID, 'Open', $callback);
            if (IPS_HasChanges($ParentID)) {
                IPS_ApplyChanges($ParentID);
            }
        }
        // Validate configuration
        $this->ValidateBridgeConfiguration();
    }


    public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString);
        IPS_LogMessage("ReceiveData", utf8_decode($data->Buffer));
        $data = utf8_decode($data->Buffer);
        preg_match_all("/\\{(.*?)\\}/", $data, $match);
        $smartLockData = json_encode(json_decode(implode($match[0]), true));
        $this->SetStateOfSmartLock($smartLockData, true);
    }


	########## Public API functions ##########

    /**
     *  Calling the URL https://api.nuki.io/discover/bridges returns a JSON array with all bridges
     *  which have been connected to the Nuki Servers through the same IP address than the one calling the URL within the last 30 days
     *  @return bool|mixed|null
     */
    public function DiscoverBridges()
    {
        $endpoint = "https://api.nuki.io/discover/bridges";
        $data = false;
        $cURLHandle = curl_init();
        curl_setopt_array($cURLHandle, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT, 20));
        $response = curl_exec($cURLHandle);
        if ($response !== false) {
            $data = json_decode($response, true);
        }
        curl_close($cURLHandle);
        if ($data) {
            $bridges = $data["bridges"];
            foreach ($bridges as $bridge) {
                IPS_LogMessage("NUKI Discovery", "Bridge ID: " . $bridge["bridgeId"] . " , IP-Addresse: " . $bridge["ip"] . " , Port: " . $bridge["port"] . "\n");
            }
            return $data;
        }
        return null;
    }


    /**
     *  Enables the api (if not yet enabled) and returns the api token.
     *  If no api token has yet been set, a new (random) one is generated.
     *
     *  When issuing this API-call the bridge turns on its LED for 30 seconds.
     *  The button of the bridge has to be pressed within this timeframe.
     *  Otherwise the bridge returns a negative success and no token.
     *
     *  @return bool|mixed|null
     */
    public function EnableAPI()
    {
        $endpoint = "/auth";
        $data = $this->SendDataToBridge($endpoint);
        if ($data) {
            IPS_LogMessage("NUKI API", "Token: ".$data->token);
            return ($data);
        }
        return null;

        /*
         *  {
         *      "token": “token123”,
         *      "success": true
         *  }
         */
    }

    /**
     * Enables or disables the authorization via /auth and the publication of the local IP and port to the discovery URL
     *
     * @param bool $Enable
     *
     * @return bool|mixed|null
     */
    public function ToggleConfigAuth(bool $Enable)
    {
        $endpoint = "/configAuth?enable=". $Enable . "&token=";
        $data = $this->SendDataToBridge($endpoint);
        if ($data) {
            return ($data);
        }
        return null;
    }


	/**
	 *	NUKI_GetSmartLocks(int $BridgeInstanceID)
	 *	Returns a list of all available smartlocks
     *	@return bool|mixed
     */
    public function GetSmartLocks()
    {
        $endpoint = "/list?token=";
        $data = $this->SendDataToBridge($endpoint);
        if ($data) {
            return ($data);
        }
        return null;
    }


	/**
	 *	NUKI_GetLockStateOfSmartLock(int $BridgeInstanceID, int $SmartLockUniqueID)
	 *	Returns the current lock state of a given smartlock
     *	@param int $SmartLockUniqueID
     *	@return bool|mixed
     */
    public function GetLockStateOfSmartLock(int $SmartLockUniqueID)
    {
        $endpoint = "/lockState?nukiId=" . $SmartLockUniqueID . "&token=";
        $data = $this->SendDataToBridge($endpoint);
        if ($data) {
            return $data;
        }
        return null;
        /**
         *	Response example for a locked smart lock
         *
         *  {“state”: 1, “stateName”: “locked”, “batteryCritical”: false, success: “true”}
         *
         * 	Possible state values are:
         *  0 uncalibrated
         *  1 locked
         *	2 unlocking
         *  3 unlocked
         *	4 locking
         *	5 unlatched
         *	6 unlocked (lock ‘n’ go)
         *	7 unlatching
         *	254 motor blocked
         *	255 undefined
         */
    }


	/**
	 *	NUKI_SetLockActionOfSmartLock(int $BridgeInstanceID, int $SmarLockUniqueID, int $LockAction)
	 *	Performs a lock operation on the given smartlock
     *	@param int $SmartLockUniqueID
     *	@param int $LockAction
     *	@return bool|mixed
     */
    public function SetLockActionOfSmartLock(int $SmartLockUniqueID, int $LockAction)
    {
        /**
         *	$LockAction
         *	1 unlock
         *	2 lock
         *	3 unlatch
         *	4 lock ‘n’ go
         * 	5 lock ‘n’ go with unlatch
         */
        $endpoint = "/lockAction?nukiId=" . $SmartLockUniqueID . "&action=" . $LockAction . "&token=";
        $data = $this->SendDataToBridge($endpoint);
        if ($data) {
            return $data;
        }
        return null;
        /**
         *    Response example
         *    {“success”: true, “batteryCritical”: false}
         */
    }


	/**
	 *	NUKI_UnpairSmartLockFromBridge(int $BridgeInstanceID, int $SmarLockUniqueID)
	 *  Removes the pairing with a given Smart Lock
     *	@param int $SmartLockUniqueID
     *	@return bool|mixed
     */
    public function UnpairSmartLockFromBridge(int $SmartLockUniqueID)
    {
        $endpoint = "/unpair?nukiId=" . $SmartLockUniqueID . "&token=";
        $data = $this->SendDataToBridge($endpoint);
        if ($data) {
            return $data;
        }
        return null;
    }


	/**
	 *	NUKI_GetBridgeInfo(int $BridgeInstanceID)
	 *	Returns all smartlocks in range and some device information of the bridge itself
     *	@return bool|mixed
     */
    public function GetBridgeInfo()
    {
        $endpoint = "/info?token=";
        $data = $this->SendDataToBridge($endpoint);
        if ($data) {
            return $data;
        }
        return null;
    }


	/**
	 *	NUKI_AddCallback(int $BridgeInstanceID)
	 *	Adds a callback of a bridge
     *	@return bool|mixed
     */
    public function AddCallback()
    {
        $callbackIP = $this->ReadPropertyString("SocketIP");
        $callbackPort = $this->ReadPropertyInteger("SocketPort");
        if (!empty($callbackIP) && !empty($callbackPort)) {
            $endpoint = "/callback/add?url=http%3A%2F%2F" . $callbackIP . "%3A" . $callbackPort . "&token=";
            $data = $this->SendDataToBridge($endpoint);
            if ($data) {
                return ($data);
            }
        }
        if (empty($callbackIP) || empty($callbackPort)) {
            echo "Bitte IP-Adresse des IP-Symcon Servers und Port des NUKI Server Sockets eintragen!";
        }
        return null;
    }


	/**
	 *	NUKI_ListCallback(int $BridgeInstanceID)
	 *	Lists the callbacks of a bridge
     *	@return bool|mixed
     */
    public function ListCallback()
    {
        $endpoint = "/callback/list?token=";
        $data = $this->SendDataToBridge($endpoint);
        if ($data) {
            return ($data);
        }
        return null;
    }


	/**
	 *	NUKI_DeleteCallback(int $BridgeInstanceID, int $CallbackID)
	 *	Deletes a callback of the bridge
     *	@param int $CallbackID
     *	@return bool|mixed
     */
    public function DeleteCallback(int $CallbackID)
    {
        $endpoint = "/callback/remove?id=" . $CallbackID . "&token=";
        $data = $this->SendDataToBridge($endpoint);
        if ($data) {
            return ($data);
        }
        return null;
    }


	/**
	 *	NUKI_GetBridgeLog(int $BridgeInstanceID)
	 *	Retrieves the log of the bridge
     *	@return bool|mixed
     */
    public function GetBridgeLog()
    {
        $endpoint = "/log?token=";
        $data = $this->SendDataToBridge($endpoint);
        if ($data) {
            return $data;
        }
        return null;
    }


	/**
	 *	NUKI_ClearBridgeLog(int $BridgeInstanceID)
	 *	Clears the log of the bridge
     */
    public function ClearBridgeLog()
    {
        $endpoint = "/clearlog?token=";
        $this->SendDataToBridge($endpoint);
    }


	/**
	 *	NUKI_UpdateBridgeFirmware(int $BridgeInstanceID)
	 *	Immediately checks for a new firmware update and installs it
	 */
    public function UpdateBridgeFirmware()
    {
        $endpoint = "/fwupdate?token=";
        $this->SendDataToBridge($endpoint);
    }


	/**
	 *	NUKI_RebootBridge(int $BridgeInstanceID)
	 *	Reboots the bridge
     *	@return bool|mixed
     */
    public function RebootBridge()
    {
        $endpoint = "/reboot?token=";
        $data = $this->SendDataToBridge($endpoint);
        if ($data) {
            return ($data);
        }
        return null;
    }


	/**
	 *	NUKI_FactoryResetBridge(int $BridgeInstanceID)
	 *	Performs a factory reset
     *	@return bool|mixed
     */
    public function FactoryResetBridge()
    {
        $endpoint = "factoryReset?token=";
        $data = $this->sendDataToBridge($endpoint);
        if ($data) {
            return ($data);
        }
        return null;
    }


    ########## Public functions ##########

	/**
	 *	NUKI_SyncSmartLocks(int $BridgeInstanceID)
	 *	Syncs smartlocks of the bridge
	 */
    public function SyncSmartLocks()
    {
        $categoryID = $this->ReadPropertyInteger('SmartLockCategory');
        $smartLocks = $this->GetSmartLocks();
        if ($smartLocks) {
            foreach ($smartLocks as $smartLock) {
                $uniqueID = $smartLock["nukiId"];
                $name = utf8_decode((string)$smartLock["name"]);
                $instanceID = $this->GetSmartLockInstanceIdByUniqueId($uniqueID);
                if ($instanceID == 0) {
                    $instanceID = IPS_CreateInstance(SMARTLOCK_MODULE_GUID);
                    IPS_SetProperty($instanceID, 'SmartLockUID', $uniqueID);
                }
                IPS_SetProperty($instanceID, 'SmartLockName', $name);
                IPS_SetName($instanceID, $name);
                IPS_SetParent($instanceID, $categoryID);
                if (IPS_GetInstance($instanceID)['ConnectionID'] <> $this->InstanceID) {
                    @IPS_DisconnectInstance($instanceID);
                    IPS_ConnectInstance($instanceID, $this->InstanceID);
                }
                IPS_ApplyChanges($instanceID);
            }
        }
        echo "Smart Locks wurden abgeglichen / angelegt!";
        IPS_LogMessage("SymconNUKI", "Syncronisierung der Smart Locks abgeschlossen.");
    }


	/**
	 *	NUKI_UpdateStateOfSmartLocks(int $BridgeInstanceID)
	 *	updates the state of all smartlocks of a bridge
	 */
    public function UpdateStateOfSmartLocks()
    {
        $instanceIDs = IPS_GetInstanceListByModuleID(SMARTLOCK_MODULE_GUID);
        if (!empty($instanceIDs)) {
            foreach ($instanceIDs as $instanceID) {
                $uniqueID = IPS_GetProperty($instanceID, "SmartLockUID");
                if (!empty($uniqueID)) {
                    $data = $this->GetLockStateOfSmartLock($uniqueID);
                    $data["nukiId"] = $uniqueID;
                    $data = json_encode($data);
                    $this->SetStateOfSmartLock($data, false);
                }
            }
        }
    }


	########## Protected functions ##########

    protected function GetParent()
    {
        $instance = IPS_GetInstance($this->InstanceID);
        return ($instance['ConnectionID'] > 0) ? $instance['ConnectionID'] : false;
    }


	########## Private functions ##########

    private function ValidateBridgeConfiguration()
    {
        $this->SetStatus(102);

        if ($this->ReadPropertyBoolean("UseCallback") == true) {
            if ($this->ReadPropertyString("SocketIP") == "" || $this->ReadPropertyInteger("SocketPort") == "") {
                $this->SetStatus(104);
            }
        }
        if ($this->ReadPropertyString("BridgeIP") == "" || $this->ReadPropertyInteger("BridgePort") == "" || $this->ReadPropertyString("BridgeAPIToken") == "") {
            $this->SetStatus(104);
        } else {
            $reachable = false;
            $timeout = 1000;
            if ($timeout && Sys_Ping($this->ReadPropertyString("BridgeIP"), $timeout) == true) {
                $data = $this->GetBridgeInfo();
                if ($data != false) {
                    $reachable = true;
                }
            }
            if ($reachable == false) {
                $this->SetStatus(201);
            }
        }
    }


    private function SendDataToBridge(string $Endpoint)
    {
        $bridgeIP = $this->ReadPropertyString("BridgeIP");
        $bridgePort = $this->ReadPropertyInteger("BridgePort");
        $token = $this->ReadPropertyString("BridgeAPIToken");
        $data = false;
        $cURLHandle = curl_init();
        curl_setopt_array($cURLHandle, array(
            CURLOPT_URL => "http://" . $bridgeIP . ":" . $bridgePort . $Endpoint . $token,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT, 40));
        $response = curl_exec($cURLHandle);
        if ($response !== false) {
            $data = json_decode($response, true);
        }
        curl_close($cURLHandle);
        if ($data) {
            return $data;
        }
        return null;
    }


	private function GetSmartLockInstanceIdByUniqueId($UniqueID)
	{
		$instanceIDs = IPS_GetInstanceListByModuleID(SMARTLOCK_MODULE_GUID);
		foreach($instanceIDs as $instanceID) {
			if (IPS_GetProperty($instanceID, "SmartLockUID") == $UniqueID) {
		  	return $instanceID;
			}
	 	}
        return null;
  	}


    private function SetStateOfSmartLock(string $SmartLockData, bool $ProtocolMode)
    {
        $this->SendDebug("Test", $SmartLockData, 0);
        if (!empty($SmartLockData)) {
            $data = json_decode($SmartLockData);
            $nukiID = $data->nukiId;
            $state = $data->state;
            $stateName = $this->Translate($data->stateName);
            $batteryState = $data->batteryCritical;
            switch ($state) {
                // switch off (locked) = false, switch on (unlocked) = true
                case 0:
                    // uncalibrated
                    $state = false;
                    break;
                case 1:
                    // locked
                    $state = false;
                    break;
                case 2:
                    // unlocking
                    $state = true;
                    break;
                case 3:
                    // unlocked
                    $state = true;
                    break;
                case 4:
                    // locking
                    $state = false;
                    break;
                case 5:
                    // unlatched
                    $state = true;
                    break;
                case 6:
                    // unlocked (lock ‘n’ go)
                    $state = true;
                    break;
                case 7:
                    // unlatching
                    $state = true;
                    break;
                default:
                    $state = false;
                    break;
            }
            $instanceIDs = IPS_GetInstanceListByModuleID(SMARTLOCK_MODULE_GUID);
            foreach ($instanceIDs as $instanceID) {
                $uniqueID = IPS_GetProperty($instanceID, "SmartLockUID");
                if ($nukiID == $uniqueID) {
                    $smartLockName = IPS_GetName($instanceID);
                    $switchID = IPS_GetObjectIDByIdent("SmartLockSwitch", $instanceID);
                    SetValue($switchID, $state);
                    $stateID = IPS_GetObjectIDByIdent("SmartLockStatus", $instanceID);
                    SetValue($stateID, $stateName);
                    $batteryStateID = IPS_GetObjectIDByIdent("SmartLockBatteryState", $instanceID);
                    SetValue($batteryStateID, $batteryState);
                    if ($ProtocolMode == true) {
                        if (IPS_GetProperty($instanceID, "UseProtocol") == true) {
                            $date = date("d.m.Y");
                            $time = date("H:i:s");
                            $string = "{$date}, {$time}, {$smartLockName}, UID: {$nukiID}, Status: {$stateName}.";
                            $protocolID = IPS_GetObjectIDByIdent("Protocol", $instanceID);
                            $entries = json_decode(IPS_GetConfiguration($instanceID))->ProtocolEntries;
                            if ($entries == 1) {
                                SetValue($protocolID, $string);
                            }
                            if ($entries > 1) {
                                // Get old content first
                                $content = array_merge(array_filter(explode("\n", GetValue($protocolID))));
                                $records = $entries - 1;
                                array_splice($content, $records);
                                array_unshift($content, $string);
                                $newContent = implode("\n", $content);
                                SetValue($protocolID, $newContent);
                            }
                        }
                    }
                }
            }
        }
    }

}

