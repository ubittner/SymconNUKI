<?php

/*
 * @module      NUKI Bridge
 *
 * @file        module.php
 *
 * prefix       NUKI
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2019
 * @license     CC BY-NC-SA 4.0
 *
 * @version     1.05
 * @build       1008
 * @date        2019-09-26, 18:00
 *
 * @see         https://github.com/ubittner/SymconNUKI
 *
 * @guids		Library
 * 				{752C865A-5290-4DBE-AC30-01C7B1C3312F}
 *
 *				Server Socket (Virtual I/O NUKI Callback)
 *				*{018EF6B5-AB94-40C6-AA53-46943E824ACF} (CR:	IO_RX)
 *				{79827379-F36E-4ADA-8A95-5F8D1DC92FA9} (I: 	IO_TX)
 *
 *				NUKI Bridge (Spliter)
 *				{B41AE29B-39C1-4144-878F-94C0F7EEC725} (Module GUID)
 *
 * 				*{79827379-F36E-4ADA-8A95-5F8D1DC92FA9} (PR:	IO_TX)
 *				{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} (CR: Device_RX)
 *              *{018EF6B5-AB94-40C6-AA53-46943E824ACF} (I:	IO_RX)
 *				{73188E44-8BBA-4EBF-8BAD-40201B8866B9} (I:	Device_TX)
 *
 */

// Declare
declare(strict_types=1);

// Definitions
if (!defined('SMARTLOCK_MODULE_GUID')) {
    define('SMARTLOCK_MODULE_GUID', '{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}');
}

if (!defined('OPENER_MODULE_GUID')) {
    define('OPENER_MODULE_GUID', '{057995F0-F9A9-C6F4-C882-C47A259419CE}');
}

if (!defined('SERVER_SOCKET_GUID')) {
    define('SERVER_SOCKET_GUID', '{8062CF2B-600E-41D6-AD4B-1BA66C32D6ED}');
}

// Include
include_once __DIR__ . '/helper/autoload.php';

class NUKIBridge extends IPSModule
{
    // Helper
    use bridgeAPI;

    public function Create()
    {
        parent::Create();

        // Register properties
        $this->RegisterPropertyString('BridgeIP', '');
        $this->RegisterPropertyInteger('BridgePort', 3777);
        $this->RegisterPropertyInteger('Timeout', 5000);
        $this->RegisterPropertyString('BridgeID', '');
        $this->RegisterPropertyString('BridgeAPIToken', '');
        $this->RegisterPropertyBoolean('UseCallback', false);
        $this->RegisterPropertyString('SocketIP', '');
        $this->RegisterPropertyInteger('SocketPort', 8081);
        $this->RegisterPropertyInteger('CallbackID', 0);
    }

    public function ApplyChanges()
    {
        // Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);

        parent::ApplyChanges();

        // Check kernel runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }

        // Callback
        if ($this->ReadPropertyBoolean('UseCallback')) {
            $this->RegisterHook('/hook/nuki/bridge/' . $this->InstanceID);
        } else {
            $this->UnregisterHook('/hook/nuki/bridge/' . $this->InstanceID);
        }

        // Validate configuration
        $this->ValidateBridgeConfiguration();
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->SendDebug('MessageSink', 'SenderID: ' . $SenderID . ', Message: ' . $Message, 0);
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
        }
    }

    /**
     * Applies changes when the kernel is ready.
     */
    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    /**
     * Receives data from the server socket and forwards the data to the children.
     *
     * @param $JSONString
     * @return bool|void
     */
    public function ReceiveData($JSONString)
    {
        $this->SendDebug(__FUNCTION__ . ' Start', 'Incomming data', 0);
        $this->SendDebug(__FUNCTION__ . ' String', $JSONString, 0);
        $data = json_decode($JSONString);
        $data = utf8_decode($data->Buffer);
        preg_match_all('/{(.*?)}/', $data, $match);
        $receivedData = json_encode(json_decode(implode($match[0]), true));
        $this->SendDebug(__FUNCTION__ . ' Data', $receivedData, 0);
        $this->SendDebug(__FUNCTION__ . ' End', 'Data received', 0);
        if (empty($Data)) {
            return;
        }
        $forwardData = [];
        $forwardData['DataID'] = '{3DED8598-AA95-4EC4-BB5D-5226ECD8405C}';
        $forwardData['Buffer'] = json_decode($receivedData);
        $forwardData = json_encode($forwardData);
        // Send data to all children
        $this->SendDataToChildren($forwardData);
    }

    /**
     * Receives data from the children and sends the result to the children.
     *
     * @param $JSONString
     * @return false|string
     */
    public function ForwardData($JSONString)
    {
        $this->SendDebug(__FUNCTION__, $JSONString, 0);
        $data = json_decode($JSONString);
        switch ($data->Buffer->Command) {
            case 'GetPairedDevices':
                $result = $this->GetPairedDevices();
                break;
            case 'GetLockState':
                $params = (array) $data->Buffer->Params;
                $result = $this->GetLockState($params['nukiId'], $params['deviceType']);
                break;
            case 'SetLockAction':
                $params = (array) $data->Buffer->Params;
                $result = $this->SetLockAction($params['nukiId'], $params['lockAction'], $params['deviceType']);
                break;
            default:
                $this->SendDebug(__FUNCTION__, 'Invalid Command: ' . $data->Buffer->Command, 0);
                $result = '';
                break;
        }
        $this->SendDebug(__FUNCTION__, json_encode($result), 0);
        return json_encode($result);
    }

    //#################### Private

    /**
     * Registers the webhook to the WebHook instance.
     *
     * @param $WebHook
     */
    private function RegisterHook($WebHook)
    {
        $ids = IPS_GetInstanceListByModuleID('{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}');
        if (count($ids) > 0) {
            $hooks = json_decode(IPS_GetProperty($ids[0], 'Hooks'), true);
            $found = false;
            foreach ($hooks as $index => $hook) {
                if ($hook['Hook'] == $WebHook) {
                    if ($hook['TargetID'] == $this->InstanceID) {
                        return;
                    }
                    $hooks[$index]['TargetID'] = $this->InstanceID;
                    $found = true;
                }
            }
            if (!$found) {
                $hooks[] = ['Hook' => $WebHook, 'TargetID' => $this->InstanceID];
            }
            IPS_SetProperty($ids[0], 'Hooks', json_encode($hooks));
            IPS_ApplyChanges($ids[0]);
        }
    }

    /**
     * Unregisters the webhook from the WebHook instance.
     *
     * @param $WebHook
     */
    private function UnregisterHook($WebHook)
    {
        $ids = IPS_GetInstanceListByModuleID('{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}');
        if (count($ids) > 0) {
            $hooks = json_decode(IPS_GetProperty($ids[0], 'Hooks'), true);
            $found = false;
            $index = null;
            foreach ($hooks as $key => $hook) {
                if ($hook['Hook'] == $WebHook) {
                    $found = true;
                    $index = $key;
                    break;
                }
            }
            if ($found === true && !is_null($index)) {
                array_splice($hooks, $index, 1);
                IPS_SetProperty($ids[0], 'Hooks', json_encode($hooks));
                IPS_ApplyChanges($ids[0]);
            }
        }
    }

    /**
     * This function will be called by the hook control. Visibility should be protected!
     */
    protected function ProcessHookData()
    {
        // Get incomming data from server
        $this->SendDebug(__FUNCTION__ .' Incomming Data', print_r($_SERVER, true), 0);
        // Get webhook content
        $data = file_get_contents('php://input');
        $this->SendDebug(__FUNCTION__ .' Data', $data, 0);
        $forwardData = [];
        $forwardData['DataID'] = '{3DED8598-AA95-4EC4-BB5D-5226ECD8405C}';
        $forwardData['Buffer'] = json_decode($data);
        $forwardData = json_encode($forwardData);
        // Send data to all children
        $this->SendDataToChildren($forwardData);
        $this->SendDebug(__FUNCTION__ .' Forward Data', $forwardData, 0);
    }

    /**
     * Validates the configuration.
     */
    private function ValidateBridgeConfiguration()
    {
        $this->SetStatus(102);
        // Check callback
        if ($this->ReadPropertyBoolean('UseCallback') == true) {
            if ($this->ReadPropertyString('SocketIP') == '' || $this->ReadPropertyInteger('SocketPort') == '') {
                $this->SetStatus(104);
            }
        }
        // Check bridge data
        if ($this->ReadPropertyString('BridgeIP') == '' || $this->ReadPropertyInteger('BridgePort') == '' || $this->ReadPropertyString('BridgeAPIToken') == '') {
            $this->SetStatus(104);
        } else {
            $reachable = false;
            $timeout = 1000;
            if ($timeout && Sys_Ping($this->ReadPropertyString('BridgeIP'), $timeout) == true) {
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
}