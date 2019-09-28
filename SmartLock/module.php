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
        $this->RegisterPropertyBoolean('UseProtocol', false);
        $this->RegisterPropertyInteger('ProtocolEntries', 6);

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

        $this->MaintainVariable('SmartLockBatteryState', $this->Translate('Battery'), 0, '~Battery', 3, true);

        $this->MaintainVariable('Protocol', $this->Translate('Protocol'), 3, '~TextBox', 4, true);
        IPS_SetIcon($this->GetIDForIdent('Protocol'), 'Database');
        IPS_SetHidden($this->GetIDForIdent('Protocol'), !$this->ReadPropertyBoolean('UseProtocol'));

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

    public function GetSmartLockState(): array
    {
        $nukiID = $this->ReadPropertyString('SmartLockUID');
        if (empty($nukiID)) {
            return [];
        }
        if (!$this->HasActiveParent()) {
            return [];
        }
        $data = [];
        $buffer = [];
        $data['DataID'] = '{73188E44-8BBA-4EBF-8BAD-40201B8866B9}';
        $buffer['Command'] = 'GetLockState';
        $buffer['Params'] = ['nukiId' => (int)$nukiID, 'deviceType' => 0];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode(json_decode($this->SendDataToParent($data), true), true);
        if (empty($result)) {
            return [];
        }
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
                    break;
                case 1:
                    $stateText = $this->Translate('Locked');
                    break;
                case 2:
                    $stateText = $this->Translate('Unlocking');
                    break;
                case 3:
                    $stateText = $this->Translate('Unlocked');
                    break;
                case 4:
                    $stateText = $this->Translate('Locking');
                    break;
                case 5:
                    $stateText = $this->Translate('Unlatched');
                    break;
                case 6:
                    $stateText = $this->Translate('Unlocked (lock ‘n’ go)');
                    break;
                case 7:
                    $stateText = $this->Translate('Unlatching');
                    break;
                case 254:
                    $stateText = $this->Translate('Motor blocked');
                    break;
                case 255:
                    $stateText = $this->Translate('undefined');
                    break;
                default:
                    $stateText = $this->Translate('Unknown');
            }
            $this->SetValue('SmartLockStatus', $stateText);
        }
        if (array_key_exists('batteryCritical', $result)) {
            $this->SetValue('SmartLockBatteryState', $result['batteryCritical']);
        }
        return $result;
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
            $lockAction = $this->ReadPropertyString('SwitchOffAction');
        }
        if ($State == true) {
            $lockAction = $this->ReadPropertyString('SwitchOnAction');
        }
        // Set values
        $this->SetValue('SmartLockSwitch', $State);
        // Send data to bridge
        $result = $this->SetLockAction($lockAction);
        if ($result) {
            $stateName = [1 => 'Unlock', 2 => 'Lock', 3 => 'Unlatch', 4 => 'Lock ‘n’ go', 5 => 'Lock ‘n’ go with unlatch', 255 => 'Undefined'];
            $name = $stateName[$lockAction];
            $this->SetValue('SmartLockStatus', $this->Translate($name));
        } else {
            // Revert switch
            $this->SetValue('SmartLockSwitch', !$State);
        }
        return $result;
    }

    //#################### Private

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
        $buffer['Params'] = ['nukiId' => (int)$nukiID, 'lockAction' => $LockAction, 'deviceType' => 0];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode(json_decode($this->SendDataToParent($data), true), true);
        if (array_key_exists('success', $result)) {
            $success = $result['success'];
        }
        if (array_key_exists('batteryCritical', $result)) {
            $this->SetValue('SmartLockBatteryState', $result['batteryCritical']);
        }
        return $success;
    }
}
