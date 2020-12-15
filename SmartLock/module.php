<?php

/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

/*
 * @module      NUKI Smart Lock
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
 * @see         https://github.com/ubittner/SymconNUKI/SmartLock
 *
 * @guids		Library
 * 				{752C865A-5290-4DBE-AC30-01C7B1C3312F}
 *
 *				NUKI Smart Lock (Device)
 *				{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}
 */

declare(strict_types=1);

//Include
include_once __DIR__ . '/../libs/constants.php';

class NUKISmartLock extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterProperties();
        $this->CreateProfiles();
        $this->RegisterVariables();
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
        $this->MaintainVariables();
        $this->GetSmartLockState();
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
        $module = IPS_GetModule(NUKI_SMARTLOCK_GUID);
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
     * @throws Exception
     */
    public function ReceiveData($JSONString)
    {
        $this->SendDebug(__FUNCTION__ . ' Start', 'Incomming data', 0);
        $this->SendDebug(__FUNCTION__ . ' String', $JSONString, 0);
        $data = json_decode(utf8_decode($JSONString));
        $buffer = $data->Buffer;
        $this->SendDebug(__FUNCTION__ . ' Data', json_encode($buffer), 0);
        $nukiID = $buffer->nukiId;
        if ($this->ReadPropertyString('SmartLockUID') != $nukiID) {
            $this->SendDebug(__FUNCTION__ . ' Abort', 'Data is not for this instance.', 0);
            return;
        }
        $this->SendDebug(__FUNCTION__ . ' End', 'Data received', 0);
        $this->SetSmartLockState(json_encode($buffer));
    }

    #################### Request Action

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'SmartLockSwitch':
                $this->ToggleSmartLock($Value);
                break;
        }
    }

    #################### Public

    /**
     * Gets the actual state of the smart lock.
     *
     * @return string
     * @throws Exception
     */
    public function GetSmartLockState(): string
    {
        $nukiID = $this->ReadPropertyString('SmartLockUID');
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
        $buffer['Params'] = ['nukiId' => (int) $nukiID, 'deviceType' => 0];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = $this->SendDataToParent($data);
        $this->SendDebug(__FUNCTION__ . ' Data', json_decode($result), 0);
        if (empty($result)) {
            return '';
        }
        $this->SetSmartLockState(json_decode($result));
        return json_decode($result);
    }

    /**
     * Toggles the Smart Lock.
     *
     * @param bool $State
     * @return bool
     * @throws Exception
     */
    public function ToggleSmartLock(bool $State): bool
    {
        $lockAction = 255;
        if ($State == false) {
            $lockAction = (int) $this->ReadPropertyString('SwitchOffAction');
        }
        if ($State == true) {
            $lockAction = (int) $this->ReadPropertyString('SwitchOnAction');
        }
        //Send data to bridge
        $result = $this->SetSmartLockAction($lockAction);
        $this->SendDebug(__FUNCTION__, json_encode($result), 0);
        if ($result) {
            //Set values
            $this->SetValue('SmartLockSwitch', $State);
            //Check callback
            $parentID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
            if ($parentID != 0 && IPS_ObjectExists($parentID)) {
                $useCallback = (bool) IPS_GetProperty($parentID, 'UseCallback');
                if (!$useCallback) {
                    $stateName = [1 => 'Unlocked', 2 => 'Locked', 3 => 'Unlatched', 4 => 'Lock ‘n’ go', 5 => 'Lock ‘n’ go with unlatch', 255 => 'Undefined'];
                    $name = $stateName[$lockAction];
                    $this->SetValue('SmartLockStatus', $this->Translate($name));
                }
            }
        }
        return $result;
    }

    /**
     * Sets the lock action of the smart lock.
     *
     * @param int $Action
     * @return bool
     * @throws Exception
     */
    public function SetSmartLockAction(int $Action): bool
    {
        $success = false;
        $nukiID = $this->ReadPropertyString('SmartLockUID');
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
        $buffer['Params'] = ['nukiId' => (int) $nukiID, 'lockAction' => $Action, 'deviceType' => 0];
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
            $this->SetValue('SmartLockBatteryState', $result['batteryCritical']);
        }
        return $success;
    }

    #################### Private

    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    private function RegisterProperties()
    {
        $this->RegisterPropertyString('Note', '');
        $this->RegisterPropertyString('SmartLockUID', '');
        $this->RegisterPropertyString('SmartLockName', '');
        /*
         *  Switch Off / On Action:
         *
         * 	1 unlock
         * 	2 lock
         * 	3 unlatch
         * 	4 lock ‘n’ go
         * 	5 lock ‘n’ go with unlatch
         */
        $this->RegisterPropertyString('SwitchOffAction', '2');
        $this->RegisterPropertyString('SwitchOnAction', '1');
        $this->RegisterPropertyBoolean('HideSmartLockSwitch', false);
    }

    private function CreateProfiles()
    {
        //Smart Lock
        $profile = 'NUKI.' . $this->InstanceID . '.SmartLockSwitch';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, '');
        IPS_SetVariableProfileAssociation($profile, 0, $this->Translate('Locking'), 'LockClosed', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 1, $this->Translate('Unlocking'), 'LockOpen', 0x00FF00);
        //Battery charging
        $profile = 'NUKI.' . $this->InstanceID . '.BatteryCharging';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, '');
        IPS_SetVariableProfileAssociation($profile, 0, $this->Translate('Inactive'), 'Battery', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 1, $this->Translate('Active'), 'Battery', 0x00FF00);
        //Battery charge state
        $profile = 'NUKI.' . $this->InstanceID . '.BatteryChargeState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'Battery');
        IPS_SetVariableProfileText($profile, '', ' %');
        IPS_SetVariableProfileValues($profile, 0, 100, 1);
        //Door
        $profile = 'NUKI.' . $this->InstanceID . '.Door.Reversed';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, 'Door');
        IPS_SetVariableProfileAssociation($profile, 0, $this->Translate('Closed'), '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, $this->Translate('Opened'), '', 0xFF0000);
        //Door sensor state
        $profile = 'NUKI.' . $this->InstanceID . '.DoorSensorState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'Information');
        IPS_SetVariableProfileAssociation($profile, 0, $this->Translate('Unavailable'), '', -1);
        IPS_SetVariableProfileAssociation($profile, 1, $this->Translate('Deactivated'), '', -1);
        IPS_SetVariableProfileAssociation($profile, 2, $this->Translate('Door closed'), '', -1);
        IPS_SetVariableProfileAssociation($profile, 3, $this->Translate('Door opened'), '', -1);
        IPS_SetVariableProfileAssociation($profile, 4, $this->Translate('Door state unknown'), '', -1);
        IPS_SetVariableProfileAssociation($profile, 5, $this->Translate('Calibrating'), '', -1);
    }

    private function DeleteProfiles()
    {
        $profiles = ['SmartLockSwitch', 'BatteryCharging', 'BatteryChargeState', 'Door.Reversed', 'DoorSensorState'];
        foreach ($profiles as $profile) {
            $profileName = 'NUKI.' . $this->InstanceID . '.' . $profile;
            if (@IPS_VariableProfileExists($profileName)) {
                IPS_DeleteVariableProfile($profileName);
            }
        }
    }

    private function RegisterVariables()
    {
        //Switch
        $profile = 'NUKI.' . $this->InstanceID . '.SmartLockSwitch';
        $this->RegisterVariableBoolean('SmartLockSwitch', $this->Translate('Door lock'), $profile, 10);
        $this->EnableAction('SmartLockSwitch');
        //State
        $id = @$this->GetIDForIdent('SmartLockStatus');
        $this->RegisterVariableString('SmartLockStatus', $this->Translate('State'), '', 20);
        if ($id == false) {
            IPS_SetIcon($this->GetIDForIdent('SmartLockStatus'), 'Information');
        }
        //Mode
        $id = @$this->GetIDForIdent('SmartLockMode');
        $this->RegisterVariableString('SmartLockMode', $this->Translate('Mode'), '', 30);
        if ($id == false) {
            IPS_SetIcon($this->GetIDForIdent('SmartLockMode'), 'Information');
        }
        //Battery
        $this->RegisterVariableBoolean('SmartLockBatteryState', $this->Translate('Battery'), '~Battery', 40);
        //Battery charging
        $profile = 'NUKI.' . $this->InstanceID . '.BatteryCharging';
        $this->RegisterVariableBoolean('SmartLockBatteryCharging', $this->Translate('Battery charging'), $profile, 42);
        //Battery charge state
        $profile = 'NUKI.' . $this->InstanceID . '.BatteryChargeState';
        $this->RegisterVariableInteger('SmartLockBatteryChargeState', $this->Translate('Battery charge state'), $profile, 44);
        //Keypad battery
        $this->RegisterVariableBoolean('KeyPadBatteryCritical', $this->Translate('Keypad Battery'), '~Battery', 46);
        //Door
        $profile = 'NUKI.' . $this->InstanceID . '.Door.Reversed';
        $this->RegisterVariableBoolean('Door', $this->Translate('Door'), $profile, 50);
        //Door sensor
        $profile = 'NUKI.' . $this->InstanceID . '.DoorSensorState';
        $this->RegisterVariableInteger('DoorSensorState', $this->Translate('Door Sensor State'), $profile, 60);
    }

    private function MaintainVariables()
    {
        //Switch
        IPS_SetHidden($this->GetIDForIdent('SmartLockSwitch'), $this->ReadPropertyBoolean('HideSmartLockSwitch'));
        //Protocol (Deprecated)
        $this->MaintainVariable('Protocol', $this->Translate('Protocol'), 3, '~TextBox', 100, false);
    }

    private function SetSmartLockState(string $Data)
    {
        $this->SendDebug(__FUNCTION__ . ' Data', $Data, 0);
        if (empty($Data)) {
            return;
        }
        $result = json_decode($Data, true);
        //Mode
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
                    $modeText = $this->translate('-');
                    break;

                default:
                    $modeText = $this->translate('Unknown');
            }
            $this->SetValue('SmartLockMode', $modeText);
        }
        //State
        if (array_key_exists('state', $result)) {
            /*
             *  ID	Name
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
            switch ($result['state']) {
                case 0:
                    $stateText = $this->Translate('Uncalibrated');
                    $switchState = false;
                    break;

                case 1:
                    $stateText = $this->Translate('Locked');
                    $switchState = false;
                    break;

                case 2:
                    $stateText = $this->Translate('Unlocking');
                    $switchState = true;
                    break;

                case 3:
                    $stateText = $this->Translate('Unlocked');
                    $switchState = true;
                    break;

                case 4:
                    $stateText = $this->Translate('Locking');
                    $switchState = false;
                    break;

                case 5:
                    $stateText = $this->Translate('Unlatched');
                    $switchState = true;
                    break;

                case 6:
                    $stateText = $this->Translate('Unlocked (lock ‘n’ go)');
                    $switchState = true;
                    break;

                case 7:
                    $stateText = $this->Translate('Unlatching');
                    $switchState = true;
                    break;

                case 254:
                    $stateText = $this->Translate('Motor blocked');
                    $switchState = false;
                    break;

                case 255:
                    $stateText = $this->Translate('Undefined');
                    $switchState = false;
                    break;

                default:
                    $stateText = $this->Translate('Unknown');
                    $switchState = false;
            }
            $this->SetValue('SmartLockSwitch', $switchState);
            $this->SetValue('SmartLockStatus', $stateText);
        }
        //Battery
        if (array_key_exists('batteryCritical', $result)) {
            $this->SetValue('SmartLockBatteryState', $result['batteryCritical']);
        }
        //Battery charging
        if (array_key_exists('batteryCharging', $result)) {
            $this->SetValue('SmartLockBatteryCharging', $result['batteryCharging']);
        }
        //Battery charge state
        if (array_key_exists('batteryChargeState', $result)) {
            $this->SetValue('SmartLockBatteryChargeState', $result['batteryChargeState']);
        }
        //Keypad battery critical
        if (array_key_exists('keypadBatteryCritical', $result)) {
            $this->SetValue('KeyPadBatteryCritical', $result['keypadBatteryCritical']);
        }
        //Door sensor
        $doorState = false;
        $value = 0;
        if (array_key_exists('doorsensorState', $result)) {
            /*
             * ID	Name
             * 0	unavailable
             * 1	deactivated
             * 2	door closed
             * 3	door opened
             * 4	door state unknown
             * 5	calibrating
             */
            $value = $result['doorsensorState'];
            if ($value == 3) {
                $doorState = true;
            }
        }
        $this->SetValue('Door', $doorState);
        $this->SetValue('DoorSensorState', $value);
    }
}