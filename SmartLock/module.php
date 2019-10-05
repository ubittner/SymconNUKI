<?php

/*
 * @module      NUKI Smart Lock
 *
 * @prefix      NUKI
 *
 * @file        module.php
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
 *				NUKI Smart Lock (Device)
 *				{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14} (Module GUID)
 * 				{73188E44-8BBA-4EBF-8BAD-40201B8866B9} (PR: Device_TX)
 *				{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} (I: 	Device_RX)
 *
 */

// Declare
declare(strict_types=1);

// Definitions
if (!defined('SMARTLOCK_MODULE_GUID')) {
    define('SMARTLOCK_MODULE_GUID', '{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}');
}

class NUKISmartLock extends IPSModule
{
    public function Create()
    {
        parent::Create();

        // Connect to NUKI bridge or create NUKI bridge
        $this->ConnectParent('{B41AE29B-39C1-4144-878F-94C0F7EEC725}');

        // Register properties
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

        // Register profiles
        $profile = 'NUKI.' . $this->InstanceID . '.SmartLockSwitch';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileIcon($profile, '');
        IPS_SetVariableProfileAssociation($profile, 0, $this->Translate('Locking'), 'LockClosed', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 1, $this->Translate('Unlocking'), 'LockOpen', 0x00FF00);
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

        // Rename instance
        $name = $this->ReadPropertyString('SmartLockName');
        if ($name != '') {
            IPS_SetName($this->InstanceID, $name);
        }

        // Register variables
        $profile = 'NUKI.' . $this->InstanceID . '.SmartLockSwitch';
        $this->MaintainVariable('SmartLockSwitch', $this->Translate('Door lock'), 0, $profile, 1, true);
        $this->EnableAction('SmartLockSwitch');
        IPS_SetHidden($this->GetIDForIdent('SmartLockSwitch'), $this->ReadPropertyBoolean('HideSmartLockSwitch'));

        $this->MaintainVariable('SmartLockStatus', $this->Translate('State'), 3, '', 2, true);
        IPS_SetIcon($this->GetIDForIdent('SmartLockStatus'), 'Information');

        $this->MaintainVariable('SmartLockMode', $this->Translate('Mode'), 3, '', 3, true);
        IPS_SetIcon($this->GetIDForIdent('SmartLockMode'), 'Information');

        $this->MaintainVariable('SmartLockBatteryState', $this->Translate('Battery'), 0, '~Battery', 3, true);

        $this->MaintainVariable('Protocol', $this->Translate('Protocol'), 3, '~TextBox', 4, false);

        // Update state
        $this->GetSmartLockState();
    }

    public function Destroy()
    {
        // Delete profiles
        $profile = 'NUKI.' . $this->InstanceID . '.SmartLockSwitch';
        if (IPS_VariableProfileExists($profile)) {
            IPS_DeleteVariableProfile($profile);
        }
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
        if ($this->ReadPropertyString('SmartLockUID') != $nukiID) {
            $this->SendDebug(__FUNCTION__ . ' Abort', 'Data is not for this instance.', 0);
            return;
        }
        $this->SendDebug(__FUNCTION__ . ' End', 'Data received', 0);
        $this->SetSmartLockState(json_encode($buffer));
    }

    //#################### Request Action

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'SmartLockSwitch':
                $this->ToggleSmartLock($Value);
                break;
        }
    }

    //#################### Public

    /**
     * Gets the actual state of the smart lock.
     *
     * @return string
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
        $data['DataID'] = '{73188E44-8BBA-4EBF-8BAD-40201B8866B9}';
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
     * Deprecated !!!
     *
     * Shows the lock state of the Smart Lock.
     *
     * @return string
     */
    public function ShowLockStateOfSmartLock(): string
    {
        $state = $this->GetSmartLockState();
        return $state;
    }

    /**
     * Toggles the Smart Lock.
     *
     * @param bool $State
     * @return bool
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
        // Send data to bridge
        $result = $this->SetLockAction($lockAction);
        $this->SendDebug(__FUNCTION__, json_encode($result), 0);
        if ($result) {
            // Set values
            $this->SetValue('SmartLockSwitch', $State);
            // Check callback
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

    //#################### Private

    private function SetSmartLockState(string $Data)
    {
        $this->SendDebug(__FUNCTION__ . ' Data', $Data, 0);
        if (empty($Data)) {
            return;
        }
        $result = json_decode($Data, true);
        if (array_key_exists('mode', $result)) {
            /*
             *  2    door mode
             *  3    -
             */
            switch ($result['mode']) {
                case 2:
                    $modeText = $this->translate('Door Mode');
                    break;
                default:
                    $modeText = $this->translate('Unknown');
            }
            $this->SendDebug(__FUNCTION__ . ' Mode', $modeText, 0);
            // Not used at the moment, prepared for future, create mode variable
        }
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
        if (array_key_exists('state', $result)) {
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
        if (array_key_exists('batteryCritical', $result)) {
            $this->SetValue('SmartLockBatteryState', $result['batteryCritical']);
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
        $nukiID = $this->ReadPropertyString('SmartLockUID');
        if (empty($nukiID)) {
            return false;
        }
        if (!$this->HasActiveParent()) {
            return false;
        }
        $data = [];
        $buffer = [];
        $data['DataID'] = '{73188E44-8BBA-4EBF-8BAD-40201B8866B9}';
        $buffer['Command'] = 'SetLockAction';
        $buffer['Params'] = ['nukiId' => (int) $nukiID, 'lockAction' => $LockAction, 'deviceType' => 0];
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
}
