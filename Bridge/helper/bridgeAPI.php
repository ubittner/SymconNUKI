<?php

// Declare
declare(strict_types=1);

trait bridgeAPI
{
    /**
     * Discovers the bridges.
     *
     * Calling the URL https://api.nuki.io/discover/bridges returns a JSON array with all bridges,
     * which have been connected to the Nuki Servers through the same IP address than the one calling the URL within the last 30 days.
     *
     * @return string
     */
    public function DiscoverBridges(): string
    {
        $endpoint = 'https://api.nuki.io/discover/bridges';
        $cURLHandle = curl_init();
        curl_setopt_array($cURLHandle, [
            CURLOPT_URL            => $endpoint,
            CURLOPT_HEADER         => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT, 20]);
        $response = curl_exec($cURLHandle);
        if ($response == false) {
            $response = '';
        }
        curl_close($cURLHandle);
        if (!empty($response)) {
            $data = json_decode($response, true);
            $bridges = $data['bridges'];
            foreach ($bridges as $bridge) {
                $this->SendDebug('DiscoverBridges', 'Bridge ID: ' . $bridge['bridgeId'] . ' , IP-Address: ' . $bridge['ip'] . ' , Port: ' . $bridge['port'], 0);
            }
        }
        return $response;
    }

    /**
     * Enables the API.
     *
     * Enables the api (if not yet enabled) and returns the api token.
     * If no api token has yet been set, a new (random) one is generated.
     *
     * When issuing this API-call the bridge turns on its LED for 30 seconds.
     * The button of the bridge has to be pressed within this timeframe.
     * Otherwise the bridge returns a negative success and no token.
     *
     * @return string
     */
    public function EnableAPI(): string
    {
        $endpoint = '/auth';
        $data = $this->SendDataToBridge($endpoint);
        if (!empty($data)) {
            $data = json_decode($data, true);
            if ($data['success']) {
                $token = $data['token'];
                $this->SendDebug('EnableAPI', 'Token: ' . $token, 0);
            }
        }
        return $data;
    }

    /**
     * Toggles the authorization.
     *
     * Enables or disables the authorization via /auth and the publication of the local IP and port to the discovery URL.
     *
     * @param bool $Enable
     *
     * @return string
     */
    public function ToggleConfigAuth(bool $Enable): string
    {
        $endpoint = '/configAuth?enable=' . $Enable . '&token=';
        $data = $this->SendDataToBridge($endpoint);
        return $data;
    }

    /**
     * Returns a list of all available smartlocks.
     *
     * @return string
     */
    public function GetSmartLocks(): string
    {
        $endpoint = '/list?token=';
        $data = $this->SendDataToBridge($endpoint);
        return $data;
    }

    /**
     * Returns the current lock state of a given Smart Lock.
     *
     * @param int $SmartLockUniqueID
     *
     * @return string
     */
    public function GetLockStateOfSmartLock(int $SmartLockUniqueID): string
    {
        $endpoint = '/lockState?nukiId=' . $SmartLockUniqueID . '&token=';
        $data = $this->SendDataToBridge($endpoint);
        return $data;
        /*
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
     * Performs a lock operation on the given Smart Lock.
     *
     * @param int $SmartLockUniqueID
     * @param int $LockAction
     *
     * @return string
     */
    public function SetLockActionOfSmartLock(int $SmartLockUniqueID, int $LockAction): string
    {
        /*
         *	$LockAction
         *	1 unlock
         *	2 lock
         *	3 unlatch
         *	4 lock ‘n’ go
         * 	5 lock ‘n’ go with unlatch
         */
        $endpoint = '/lockAction?nukiId=' . $SmartLockUniqueID . '&action=' . $LockAction . '&token=';
        $data = $this->SendDataToBridge($endpoint);
        return $data;
        /*
         *    Response example
         *    {“success”: true, “batteryCritical”: false}
         */
    }

    /**
     * Removes the pairing with a given Smart Lock.
     *
     * @param int $SmartLockUniqueID
     *
     * @return string
     */
    public function UnpairSmartLockFromBridge(int $SmartLockUniqueID): string
    {
        $endpoint = '/unpair?nukiId=' . $SmartLockUniqueID . '&token=';
        $data = $this->SendDataToBridge($endpoint);
        return $data;
    }

    /**
     * Gets information of the bridge.
     *
     * Returns all Smart Locks in range and some device information of the bridge itself.
     *
     * @return string
     */
    public function GetBridgeInfo(): string
    {
        $endpoint = '/info?token=';
        $data = $this->SendDataToBridge($endpoint);
        return $data;
    }

    /**
     * Registers a new callback url.
     *
     * @return string
     */
    public function AddCallback(): string
    {
        $data = '';
        $callbackIP = $this->ReadPropertyString('SocketIP');
        $callbackPort = $this->ReadPropertyInteger('SocketPort');
        if (!empty($callbackIP) && !empty($callbackPort)) {
            $endpoint = '/callback/add?url=http%3A%2F%2F' . $callbackIP . '%3A' . $callbackPort . '&token=';
            $data = $this->SendDataToBridge($endpoint);
        }
        if (empty($callbackIP) || empty($callbackPort)) {
            echo $this->Translate('Please enter the IP address of the IP-Symcon server and the port of the NUKI server socket!');
        }
        return $data;
    }

    /**
     * Returns all registered url callbacks.
     *
     * @return string
     */
    public function ListCallback(): string
    {
        $endpoint = '/callback/list?token=';
        $data = $this->SendDataToBridge($endpoint);
        return $data;
    }

    /**
     * Removes a previously added callback.
     *
     * @param int $CallbackID
     *
     * @return string
     */
    public function DeleteCallback(int $CallbackID): string
    {
        $endpoint = '/callback/remove?id=' . $CallbackID . '&token=';
        $data = $this->SendDataToBridge($endpoint);
        return $data;
    }

    /**
     * Retrieves the log of the bridge.
     *
     * @return string
     */
    public function GetBridgeLog(): string
    {
        $endpoint = '/log?token=';
        $data = $this->SendDataToBridge($endpoint);
        return $data;
    }

    /**
     * Clears the log of the bridge.
     */
    public function ClearBridgeLog()
    {
        $endpoint = '/clearlog?token=';
        $this->SendDataToBridge($endpoint);
    }

    /**
     * Immediately checks for a new firmware update and installs it.
     */
    public function UpdateBridgeFirmware()
    {
        $endpoint = '/fwupdate?token=';
        $this->SendDataToBridge($endpoint);
    }

    /**
     * Reboots the bridge.
     */
    public function RebootBridge()
    {
        $endpoint = '/reboot?token=';
        $this->SendDataToBridge($endpoint);
    }

    /**
     * Performs a factory reset.
     */
    public function FactoryResetBridge()
    {
        $endpoint = 'factoryReset?token=';
        $this->sendDataToBridge($endpoint);
    }

    /**
     * Sends data to the bridge.
     *
     * @param string $Endpoint
     *
     * @return string
     */
    private function SendDataToBridge(string $Endpoint): string
    {
        $bridgeIP = $this->ReadPropertyString('BridgeIP');
        $bridgePort = $this->ReadPropertyInteger('BridgePort');
        $token = $this->ReadPropertyString('BridgeAPIToken');
        $cURLHandle = curl_init();
        curl_setopt_array($cURLHandle, [
            CURLOPT_URL            => 'http://' . $bridgeIP . ':' . $bridgePort . $Endpoint . $token,
            CURLOPT_HEADER         => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT, 40]);
        $response = curl_exec($cURLHandle);
        if ($response == false) {
            $response = '';
        }
        curl_close($cURLHandle);
        return $response;
    }
}
