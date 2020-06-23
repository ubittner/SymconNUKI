<?php

/*
 * @module      NUKI Bridge
 *
 * @prefix      NUKI
 *
 * @file        module.php
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2019, 2020
 * @license     CC BY-NC-SA 4.0
 *              https://creativecommons.org/licenses/by-nc-sa/4.0/
 *
 * @see         https://github.com/ubittner/SymconNUKI/Bridge
 *
 * @guids		Library
 * 				{752C865A-5290-4DBE-AC30-01C7B1C3312F}
 *
 *				NUKI Bridge (Spliter)
 *				{B41AE29B-39C1-4144-878F-94C0F7EEC725}
 */

declare(strict_types=1);

// Include
include_once __DIR__ . '/../libs/helper/autoload.php';
include_once __DIR__ . '/helper/autoload.php';

class NUKIBridge extends IPSModule
{
    // Helper
    use libs_helper_getModuleInfo;
    use NUKI_bridgeAPI;
    use NUKI_webHook;

    public function Create()
    {
        // Never delete this line!
        parent::Create();
        $this->RegisterProperties();
    }

    public function Destroy()
    {
        // Unregister WebHook
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterHook('/hook/nuki/bridge/' . $this->InstanceID);
        }
        // Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        // Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        // Never delete this line!
        parent::ApplyChanges();
        // Check runlevel
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
        $this->SendDebug(__FUNCTION__, $TimeStamp . ', SenderID: ' . $SenderID . ', Message: ' . $Message . ', Data: ' . print_r($Data, true), 0);
        if (!empty($Data)) {
            foreach ($Data as $key => $value) {
                $this->SendDebug(__FUNCTION__, 'Data[' . $key . '] = ' . json_encode($value), 0);
            }
        }
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;

        }
    }

    public function GetConfigurationForm()
    {
        $formData = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $moduleInfo = $this->GetModuleInfo(NUKI_BRIDGE_GUID);
        $formData['elements'][1]['items'][1]['caption'] = $this->Translate("Instance ID:\t\t") . $this->InstanceID;
        $formData['elements'][1]['items'][2]['caption'] = $this->Translate("Module:\t\t\t") . $moduleInfo['name'];
        $formData['elements'][1]['items'][3]['caption'] = "Version:\t\t\t" . $moduleInfo['version'];
        $formData['elements'][1]['items'][4]['caption'] = $this->Translate("Date:\t\t\t") . $moduleInfo['date'];
        $formData['elements'][1]['items'][5]['caption'] = $this->Translate("Time:\t\t\t") . $moduleInfo['time'];
        $formData['elements'][1]['items'][6]['caption'] = $this->Translate("Developer:\t\t") . $moduleInfo['developer'];
        $formData['elements'][1]['items'][7]['caption'] = "API Version:\t\t" . $this->apiVersion;
        return json_encode($formData);
    }

    /**
     * Receives data from a child and sends the result back to the child.
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

    #################### Private

    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    private function RegisterProperties()
    {
        $this->RegisterPropertyString('Note', '');
        $this->RegisterPropertyString('BridgeIP', '');
        $this->RegisterPropertyInteger('BridgePort', 8080);
        $this->RegisterPropertyInteger('Timeout', 5000);
        $this->RegisterPropertyString('BridgeAPIToken', '');
        $this->RegisterPropertyBoolean('UseCallback', false);
        $this->RegisterPropertyString('SocketIP', '');
        $this->RegisterPropertyInteger('SocketPort', 3777);
        $this->RegisterPropertyInteger('CallbackID', 0);
    }

    private function ValidateBridgeConfiguration()
    {
        $status = 102;
        // Check callback
        if ($this->ReadPropertyBoolean('UseCallback')) {
            if (empty($this->ReadPropertyString('SocketIP')) || empty($this->ReadPropertyInteger('SocketPort'))) {
                $status = 104;
            }
        }
        // Check bridge data
        if (empty($this->ReadPropertyString('BridgeIP')) || empty($this->ReadPropertyInteger('BridgePort')) || empty($this->ReadPropertyString('BridgeAPIToken'))) {
            $status = 104;
        } else {
            $reachable = false;
            $timeout = 1000;
            if ($timeout && Sys_Ping($this->ReadPropertyString('BridgeIP'), $timeout)) {
                $data = $this->GetBridgeInfo();
                if ($data) {
                    $reachable = true;
                }
            }
            if (!$reachable) {
                $status = 201;
            }
        }
        $this->SetStatus($status);
    }
}