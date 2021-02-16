<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

trait NUKI_bridgeAPI
{
    private $apiVersion = '1.12';

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
            $result = json_decode($data, true);
            if (array_key_exists('token', $result)) {
                $token = $result['token'];
                $this->SendDebug(__FUNCTION__, 'Token: ' . $token, 0);
                IPS_SetProperty($this->InstanceID, 'BridgeAPIToken', $token);
                if (IPS_HasChanges($this->InstanceID)) {
                    IPS_ApplyChanges($this->InstanceID);
                }
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
        $endpoint = '/configAuth?enable=' . intval($Enable) . '&';
        return $this->SendDataToBridge($endpoint);
    }

    /**
     * Returns a list of all paired devices.
     *
     * @return string
     */
    public function GetPairedDevices(): string
    {
        $endpoint = '/list?';
        return $this->SendDataToBridge($endpoint);
    }

    /**
     * Gets the state of the device.
     *
     * @param int $NukiID
     * @param int $DeviceType
     * @return string
     */
    public function GetLockState(int $NukiID, int $DeviceType = 0): string
    {
        /*
         * Device type:
         *
         *  0   Smart lock
         *  2   Opener
         *
         */

        $endpoint = '/lockState?nukiId=' . $NukiID . '&deviceType=' . $DeviceType . '&';
        return $this->SendDataToBridge($endpoint);

        /*
         *  Response example for a locked smart lock
         *
         *  {“deviceType”: 0, “mode”: 2,“state”: 1, “stateName”: “locked”, “batteryCritical”: false, “success”: true}
         *
         */

        /*
         *  State values for a smart lock are:
         *
         *  0   uncalibrated
         *  1   locked
         *	2   unlocking
         *  3   unlocked
         *	4   locking
         *	5   unlatched
         *	6   unlocked (lock ‘n’ go)
         *	7   unlatching
         *  253 -
         *  254 motor blocked
         *  255 undefined
         *
         */

        /*
         * 	State values for an opener are:
         *
         *  0   untrained
         *  1   online
         *	2   -
         *  3   rto active
         *	4   -
         *	5   open
         *	6   -
         *	7   opening
         *  253 boot run
         *  254 -
         *  255 undefined
         *
         */
    }

    /**
     * Set the lock action of a device.
     *
     * @param int $NukiID
     * @param int $DeviceType
     * @param int $LockAction
     * @return string
     */
    public function SetLockAction(int $NukiID, int $LockAction, int $DeviceType = 0): string
    {
        /*
         *  Lock actions for a smart lock are:
         *
         *	1   unlock
         *	2   lock
         *	3   unlatch
         *	4   lock ‘n’ go
         * 	5   lock ‘n’ go with unlatch
         *
         */

        /*
         *  Lock actions for an opener are:
         *
         *	1   activate rto
         *	2   deactivate rto
         *	3   electric strike actuation
         *	4   activate continuous mode
         * 	5   deactivate continuous mode
         *
         */

        /*
         * Device type:
         *
         *  0   Smart lock
         *  2   Opener
         *
         */

        $endpoint = '/lockAction?nukiId=' . $NukiID . '&action=' . $LockAction . '&deviceType=' . $DeviceType . '&';
        return $this->SendDataToBridge($endpoint);

        /*
         *    Response example:
         *
         *    {“success”: true, “batteryCritical”: false}
         */
    }

    /**
     * Unpairs a device from the bridge.
     *
     * @param int $NukiID
     * @param int $DeviceType
     * @return string
     */
    public function UnpairDevice(int $NukiID, int $DeviceType = 0): string
    {
        $endpoint = '/unpair?nukiId=' . $NukiID . '&deviceType=' . $DeviceType . '&';
        return $this->SendDataToBridge($endpoint);
    }

    /**
     * Gets information of the bridge.
     *
     * Returns all smart locks and openers in range and some device information of the bridge itself.
     *
     * @return string
     */
    public function GetBridgeInfo(): string
    {
        $endpoint = '/info?';
        return $this->SendDataToBridge($endpoint);
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
            $endpoint = '/callback/add?url=http%3A%2F%2F' . $callbackIP . '%3A' . $callbackPort . '%2Fhook%2Fnuki%2Fbridge%2F' . $this->InstanceID . '%2F&';
            $data = $this->SendDataToBridge($endpoint);
        }
        if (empty($callbackIP) || empty($callbackPort)) {
            echo $this->Translate('Please enter the IP address of the IP-Symcon server and the port for the Webhook!');
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
        $endpoint = '/callback/list?';
        return $this->SendDataToBridge($endpoint);
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
        $endpoint = '/callback/remove?id=' . $CallbackID . '&';
        return $this->SendDataToBridge($endpoint);
    }

    /**
     * Retrieves the log of the bridge.
     *
     * @return string
     */
    public function GetBridgeLog(): string
    {
        $endpoint = '/log?';
        return $this->SendDataToBridge($endpoint);
    }

    /**
     * Clears the log of the bridge.
     */
    public function ClearBridgeLog(): string
    {
        $endpoint = '/clearlog?';
        return $this->SendDataToBridge($endpoint);
    }

    /**
     * Immediately checks for a new firmware update and installs it.
     */
    public function UpdateBridgeFirmware(): string
    {
        $endpoint = '/fwupdate?';
        return $this->SendDataToBridge($endpoint);
    }

    /**
     * Reboots the bridge.
     */
    public function RebootBridge(): string
    {
        $endpoint = '/reboot?';
        return $this->SendDataToBridge($endpoint);
    }

    /**
     * Performs a factory reset.
     */
    public function FactoryResetBridge(): string
    {
        $endpoint = 'factoryReset?';
        return $this->sendDataToBridge($endpoint);
    }

    //########## Send data

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
        switch ($Endpoint) {
            case '/auth':
                $url = 'http://' . $bridgeIP . ':' . $bridgePort . $Endpoint;
                break;

            default:
                if (empty($token)) {
                    $this->SendDebug(__FUNCTION__, $this->Translate('Please enter the API Token of the NUKI Bridge'), 0);
                    return '';
                }
                $url = 'http://' . $bridgeIP . ':' . $bridgePort . $Endpoint . 'token=' . $token;
                if ($this->ReadPropertyBoolean('UseEncryption')) {
                    $timestamp = gmdate("Y-m-d\TH:i:s\Z");
                    $randomNumber = random_int(0, 65535);
                    $data = (string) $timestamp . ',' . $randomNumber . ',' . $token;
                    $hash = hash('sha256', $data);
                    $token = 'ts=' . $timestamp . '&rnr=' . $randomNumber . '&hash=' . $hash;
                    $url = 'http://' . $bridgeIP . ':' . $bridgePort . $Endpoint . $token;
                    $this->SendDebug(__FUNCTION__, $url, 0);
                }
        }
        $this->SendDebug(__FUNCTION__, $url, 0);
        $body = '';
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL               => $url,
            CURLOPT_HEADER            => true,
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_FAILONERROR       => true,
            CURLOPT_CONNECTTIMEOUT_MS => $this->ReadPropertyInteger('Timeout')]);
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:  # OK
                    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                    $header = substr($response, 0, $header_size);
                    $body = substr($response, $header_size);
                    $this->SendDebug(__FUNCTION__, 'Header: ' . $header, 0);
                    $this->SendDebug(__FUNCTION__, 'Body: ' . $body, 0);
                    break;

                default:
                    $this->SendDebug(__FUNCTION__, 'HTTP Code: ' . $http_code, 0);
            }
        } else {
            $error_msg = curl_error($ch);
            $this->SendDebug(__FUNCTION__, 'An error has occurred: ' . json_encode($error_msg), 0);
        }
        curl_close($ch);
        return $body;
        /*
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }
        if ($response == false) {
            $response = '';
        } else {
            $this->SendDebug(__FUNCTION__, $response, 0);
        }
        curl_close($ch);
        if (isset($error_msg)) {
            $response = '';
            $this->SendDebug(__FUNCTION__, 'An error has occurred: ' . json_encode($error_msg), 0);
        }
        return $response;
         */
    }
}
