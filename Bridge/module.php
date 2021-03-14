<?php

/*
 * @author      Ulrich Bittner
 * @copyright   (c) 2020, 2021
 * @license    	CC BY-NC-SA 4.0
 * @see         https://github.com/ubittner/SymconNUKI
 */

/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

declare(strict_types=1);

include_once __DIR__ . '/../libs/constants.php';
include_once __DIR__ . '/helper/autoload.php';

class NUKIBridge extends IPSModule
{
    //Helper
    use NUKI_bridgeAPI;
    use NUKI_callback;
    use NUKI_webHook;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterProperties();
        //New Bridge API Token attribute
        $this->RegisterAttributeString('BridgeAPIToken', '');
    }

    public function Destroy()
    {
        //Unregister WebHook
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterHook('/hook/nuki/bridge/' . $this->InstanceID);
        }
        // Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        //Never delete this line!
        parent::ApplyChanges();
        //Check runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }
        //Move bridge API token from property to attribute
        $token = @$this->ReadPropertyString('BridgeAPIToken');
        if (is_string($token)) {
            if (!empty($token)) {
                $this->WriteAttributeString('BridgeAPIToken', $token);
                IPS_SetProperty($this->InstanceID, 'BridgeAPIToken', '');
                IPS_ApplyChanges($this->InstanceID);
                return;
            }
        }
        //Check ip address and convert to new version
        if ($this->ReadPropertyString('SocketIP') == '' && count(Sys_GetNetworkInfo()) > 0) {
            @IPS_SetProperty($this->InstanceID, 'SocketIP', (count(Sys_GetNetworkInfo()) > 0) ? Sys_GetNetworkInfo()[0]['IP'] : '');
            IPS_ApplyChanges($this->InstanceID);
            return;
        }
        //Validate configuration
        if ($this->ValidateBridgeConfiguration()) {
            $this->ManageCallback();
        }
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
        //Host
        $options = [];
        $networkInfo = Sys_GetNetworkInfo();
        for ($i = 0; $i < count($networkInfo); $i++) {
            $options[] = [
                'caption' => $networkInfo[$i]['IP'],
                'value'   => $networkInfo[$i]['IP']
            ];
        }
        $formData['elements'][9] = [
            'type'    => 'Select',
            'name'    => 'SocketIP',
            'caption' => 'Host IP-Address (IP-Symcon)',
            'options' => $options
        ];
        $bridgeIP = $this->ReadPropertyString('BridgeIP');
        $bridgePort = $this->ReadPropertyInteger('BridgePort');
        $enabled = true;
        if (empty($bridgeIP) || $bridgePort == 0) {
            $enabled = false;
        }
        $formData['elements'][4]['enabled'] = $enabled;
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

    public function UpdateToken(string $NewToken): void
    {
        if (!empty($NewToken)) {
            $this->WriteAttributeString('BridgeAPIToken', $NewToken);
            $this->ReloadForm();
            $this->ValidateBridgeConfiguration();
        }
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
        //Bridge API token only used for moving an existing refresh token to the new attribute
        $this->RegisterPropertyString('BridgeAPIToken', '');
        $this->RegisterPropertyBoolean('UseEncryption', false);
        $this->RegisterPropertyString('BridgeID', '');
        $this->RegisterPropertyInteger('Timeout', 5000);
        $this->RegisterPropertyBoolean('UseCallback', false);
        $this->RegisterPropertyString('SocketIP', (count(Sys_GetNetworkInfo()) > 0) ? Sys_GetNetworkInfo()[0]['IP'] : '');
        $this->RegisterPropertyInteger('SocketPort', 3777);
        $this->RegisterPropertyInteger('CallbackID', 0);
    }

    private function ValidateBridgeConfiguration(): bool
    {
        $status = 102;
        $result = true;
        //Check token
        if (empty($this->ReadAttributeString('BridgeAPIToken'))) {
            $status = 104;
            $result = false;
        }
        //Check bridge data
        if (empty($this->ReadPropertyString('BridgeIP')) || $this->ReadPropertyInteger('BridgePort') == 0) {
            $status = 201;
            $result = false;
        } else {
            $reachable = false;
            $timeout = 1000;
            if ($timeout && Sys_Ping($this->ReadPropertyString('BridgeIP'), $timeout)) {
                if (!empty($this->ReadAttributeString('BridgeAPIToken'))) {
                    $data = $this->GetBridgeInfo();
                    if ($data) {
                        $reachable = true;
                    }
                }
            }
            if (!$reachable && !empty($this->ReadAttributeString('BridgeAPIToken'))) {
                $status = 201;
                $result = false;
            }
        }
        $this->SetStatus($status);
        return $result;
    }
}