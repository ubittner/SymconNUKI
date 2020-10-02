<?php

/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

/*
 * @module      NUKI Opener
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
 * @see         https://github.com/ubittner/SymconNUKI/Opener
 *
 * @guids		Library
 * 				{752C865A-5290-4DBE-AC30-01C7B1C3312F}
 *
 *				NUKI Opener (Device)
 *				{057995F0-F9A9-C6F4-C882-C47A259419CE}
 */

declare(strict_types=1);

//Include
include_once __DIR__ . '/../libs/helper/autoload.php';

class NUKIOpener extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterProperties();
        $this->CreateProfiles();
        //Connect to NUKI bridge (Splitter)
        $this->ConnectParent(NUKI_BRIDGE_GUID);
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
        $this->DeleteProfiles();
    }

    public function ApplyChanges()
    {
        //Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        //Never delete this line!
        parent::ApplyChanges();
        //Check kernel runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }
        //Rename instance
        $name = $this->ReadPropertyString('OpenerName');
        if ($name != '') {
            IPS_SetName($this->InstanceID, $name);
        }
        $this->MaintainVariables();
        $this->GetOpenerState();
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
        $moduleInfo = [];
        $library = IPS_GetLibrary(NUKI_LIBRARY_GUID);
        $module = IPS_GetModule(NUKI_OPENER_GUID);
        $moduleInfo['name'] = $module['ModuleName'];
        $moduleInfo['version'] = $library['Version'] . '-' . $library['Build'];
        $moduleInfo['date'] = date('d.m.Y', $library['Date']);
        $moduleInfo['time'] = date('H:i', $library['Date']);
        $moduleInfo['developer'] = $library['Author'];
        $formData['elements'][1]['items'][1]['caption'] = "ID:\t\t\t\t" . $this->InstanceID;
        $formData['elements'][1]['items'][2]['caption'] = $this->Translate("Module:\t\t\t") . $moduleInfo['name'];
        $formData['elements'][1]['items'][3]['caption'] = "Version:\t\t\t" . $moduleInfo['version'];
        $formData['elements'][1]['items'][4]['caption'] = $this->Translate("Date:\t\t\t") . $moduleInfo['date'];
        $formData['elements'][1]['items'][5]['caption'] = $this->Translate("Time:\t\t\t") . $moduleInfo['time'];
        $formData['elements'][1]['items'][6]['caption'] = $this->Translate("Developer:\t\t") . $moduleInfo['developer'];
        $formData['elements'][1]['items'][7]['caption'] = $this->Translate("Prefix:\t\t\t") . 'NUKI';
        return json_encode($formData);
    }

    /**
     * Receives data from the NUKI Bridge (splitter).
     *
     * @param $JSONString
     * @return bool|void
     */
    public function ReceiveData($JSONString)
    {
        $this->SendDebug(__FUNCTION__ . ' Start', 'Incomming data', 0);
        $this->SendDebug(__FUNCTION__ . ' String', $JSONString, 0);
        $data = json_decode(utf8_decode($JSONString));
        $buffer = $data->Buffer;
        $this->SendDebug(__FUNCTION__ . ' Data', json_encode($buffer), 0);
        $nukiID = $buffer->nukiId;
        if ($this->ReadPropertyString('OpenerUID') != $nukiID) {
            $this->SendDebug(__FUNCTION__ . ' Abort', 'Data is not for this instance.', 0);
            return;
        }
        $this->SendDebug(__FUNCTION__ . ' End', 'Data received', 0);
        $this->SetOpenerState(json_encode($buffer));
    }

    #################### Request Action

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'DoorBuzzer':
                $this->BuzzDoor();
                break;
        }
    }

    #################### Public

    /**
     * Gets the actual state of the opener.
     *
     * @return string
     */
    public function GetOpenerState(): string
    {
        $nukiID = $this->ReadPropertyString('OpenerUID');
        if (empty($nukiID)) {
            return '';
        }
        if (!$this->HasActiveParent()) {
            return '';
        }
        $data = [];
        $buffer = [];
        $data['DataID'] = NUKI_BRIDGE_DATA_GUID;
        $buffer['Command'] = 'GetLockState';
        $buffer['Params'] = ['nukiId' => (int) $nukiID, 'deviceType' => 2];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = $this->SendDataToParent($data);
        $this->SendDebug(__FUNCTION__ . ' Data', json_decode($result), 0);
        if (empty($result)) {
            return '';
        }
        $this->SetOpenerState(json_decode($result));
        return json_decode($result);
    }

    /**
     * Toggles the ring to open function of the opener.
     *
     * @param bool $State
     * @return bool
     */
    public function ToggleRingToOpen(bool $State): bool
    {
        //Deactivate
        $lockAction = 2;
        //Activate
        if ($State) {
            $lockAction = 1;
        }
        return $this->SetLockAction($lockAction);
    }

    /**
     * Toggles the continuous mode of the opener.
     *
     * @param bool $State
     * @return bool
     */
    public function ToggleContinuousMode(bool $State): bool
    {
        //Deactivate
        $lockAction = 5;
        //Activate
        if ($State) {
            $lockAction = 4;
        }
        return $this->SetLockAction($lockAction);
    }

    /**
     * Opens the door via buzzer.
     *
     * @return bool
     */
    public function BuzzDoor(): bool
    {
        return $this->SetLockAction(3);
    }

    #################### Private

    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    private function RegisterProperties()
    {
        $this->RegisterPropertyString('Note', '');
        $this->RegisterPropertyString('OpenerUID', '');
        $this->RegisterPropertyString('OpenerName', '');
    }

    private function CreateProfiles()
    {
        $profile = 'NUKI.' . $this->InstanceID . '.DoorBuzzer';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, '');
        IPS_SetVariableProfileAssociation($profile, 0, $this->Translate('Actuate'), 'Door', 0x00FF00);
    }

    private function DeleteProfiles()
    {
        $profiles = ['DoorBuzzer'];
        foreach ($profiles as $profile) {
            $profileName = 'NUKI.' . $this->InstanceID . '.' . $profile;
            if (@IPS_VariableProfileExists($profileName)) {
                IPS_DeleteVariableProfile($profileName);
            }
        }
    }

    private function MaintainVariables()
    {
        //Buzzer
        $profile = 'NUKI.' . $this->InstanceID . '.DoorBuzzer';
        $this->MaintainVariable('DoorBuzzer', $this->Translate('Door buzzer'), 1, $profile, 10, true);
        $this->EnableAction('DoorBuzzer');
        //State
        $this->MaintainVariable('OpenerState', $this->Translate('State'), 3, '', 20, true);
        IPS_SetIcon($this->GetIDForIdent('OpenerState'), 'Information');
        //Mode
        $this->MaintainVariable('OpenerMode', $this->Translate('Mode'), 3, '', 30, true);
        IPS_SetIcon($this->GetIDForIdent('OpenerMode'), 'Information');
        //Battery
        $this->MaintainVariable('BatteryState', $this->Translate('Battery'), 0, '~Battery', 40, true);
    }

    /**
     * Set the state of the opener.
     *
     * @param string $Data
     */
    private function SetOpenerState(string $Data)
    {
        $this->SendDebug(__FUNCTION__ . ' Data', $Data, 0);
        if (empty($Data)) {
            return;
        }
        $result = json_decode($Data, true);
        if (array_key_exists('mode', $result)) {
            /*
             *  2    door mode, operation mode after complete setup
             *  3    continuous mode, ring to open permanently active
             */
            switch ($result['mode']) {
                case 2:
                    $modeText = $this->translate('Door Mode');
                    break;
                case 3:
                    $modeText = $this->translate('Continuous Mode');
                    break;
                default:
                    $modeText = $this->translate('Unknown');
            }
            $this->SetValue('OpenerMode', $modeText);
        }
        if (array_key_exists('state', $result)) {
            /*
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
             */
            switch ($result['state']) {
                case 0:
                    $stateText = $this->Translate('Untrained');
                    break;
                case 1:
                    $stateText = 'Online';
                    break;
                case 3:
                    $stateText = $this->Translate('Ring to Open active');
                    break;
                case 5:
                    $stateText = $this->Translate('Open');
                    break;
                case 7:
                    $stateText = $this->Translate('Opening');
                    break;
                case 253:
                    $stateText = 'Boot Run';
                    break;
                case 255:
                    $stateText = $this->Translate('Undefined');
                    break;
                default:
                    $stateText = $this->Translate('Unknown');
            }
            $this->SetValue('OpenerState', $stateText);
        }
        if (array_key_exists('batteryCritical', $result)) {
            $this->SetValue('BatteryState', $result['batteryCritical']);
        }
    }

    /**
     * Set the lock action of the opener.
     *
     * @param int $LockAction
     * @return bool
     */
    private function SetLockAction(int $LockAction): bool
    {
        $success = false;
        $nukiID = $this->ReadPropertyString('OpenerUID');
        if (empty($nukiID)) {
            return false;
        }
        if (!$this->HasActiveParent()) {
            return false;
        }
        $data = [];
        $buffer = [];
        $data['DataID'] = NUKI_BRIDGE_DATA_GUID;
        $buffer['Command'] = 'SetLockAction';
        $buffer['Params'] = ['nukiId' => (int) $nukiID, 'lockAction' => $LockAction, 'deviceType' => 2];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode(json_decode($this->SendDataToParent($data), true), true);
        if (empty($result)) {
            return $success;
        }
        if (array_key_exists('success', $result)) {
            $success = $result['success'];
        }
        if (array_key_exists('batteryCritical', $result)) {
            $this->SetValue('BatteryState', $result['batteryCritical']);
        }
        return $success;
    }
}