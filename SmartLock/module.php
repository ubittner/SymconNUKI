<?php

/*
 * @module      NUKI Smart Lock
 *
 * @file        module.php
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2019
 * @license     CC BY-NC-SA 4.0
 *
 * @version     1.04
 * @build       1004
 * @date        2019-04-21, 10:00
 *
 * @see         https://github.com/ubittner/SymconNUKI
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
 * @changelog	2019-04-21, 10:00, added changes for module store
 *              2018-04-21, 12:30, rebuild for IP-Symcon 5.0
 * 				2017-04-19, 23:00, update to API Version 1.5 and some improvements
 * 				2017-01-18, 13:00, initial module script version 1.01
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
        /*  Switch Off / On Action
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
        if (!IPS_VariableProfileExists('NUKI.SmartLockSwitch')) {
            IPS_CreateVariableProfile('NUKI.SmartLockSwitch', 0);
            IPS_SetVariableProfileIcon('NUKI.SmartLockSwitch', '');
            IPS_SetVariableProfileAssociation('NUKI.SmartLockSwitch', 0, $this->Translate('locked'), 'LockClosed', 0xFF0000);
            IPS_SetVariableProfileAssociation('NUKI.SmartLockSwitch', 1, $this->Translate('unlocked'), 'LockOpen', 0x00FF00);
        }
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
        $switchID = $this->RegisterVariableBoolean('SmartLockSwitch', 'NUKI Smart Lock', 'NUKI.SmartLockSwitch', 1);
        $this->EnableAction('SmartLockSwitch');
        $HideSmartLockSwitchState = $this->ReadPropertyBoolean('HideSmartLockSwitch');
        IPS_SetHidden($switchID, $HideSmartLockSwitchState);

        $statusID = $this->RegisterVariableString('SmartLockStatus', $this->Translate('State'), '', 2);
        IPS_SetIcon($statusID, 'Information');

        $this->RegisterVariableBoolean('SmartLockBatteryState', $this->Translate('Battery'), '~Battery', 3);

        $protocolID = $this->RegisterVariableString('Protocol', $this->Translate('Protocol'), '~TextBox', 4);
        IPS_SetHidden($protocolID, !$this->ReadPropertyBoolean('UseProtocol'));
        IPS_SetIcon($protocolID, 'Database');

        $uniqueID = $this->ReadPropertyString('SmartLockUID');
        if (!empty($uniqueID)) {
            NUKI_UpdateStateOfSmartLocks($this->GetBridgeInstanceID(), false);
        }

        $this->SetStatus(102);
    }

    public function Destroy()
    {
        $instances = count(IPS_GetInstanceListByModuleID(SMARTLOCK_MODULE_GUID));
        if ($instances === 0) {
            $profiles = [];
            $profiles[0] = 'NUKI.SmartLockSwitch';
            foreach ($profiles as $profile) {
                if (IPS_VariableProfileExists($profile)) {
                    IPS_DeleteVariableProfile($profile);
                }
            }
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

    /**
     * Gets the instance id of the related bridge.
     *
     * @return int
     */
    protected function GetBridgeInstanceID(): int
    {
        $id = (int) IPS_GetInstance($this->InstanceID)['ConnectionID'];
        return $id;
    }

    /**
     * Shows the lock state of the Smart Lock.
     *
     * @return string
     */
    public function ShowLockStateOfSmartLock(): string
    {
        $state = '';
        $bridgeID = $this->GetBridgeInstanceID();
        if ($bridgeID > 0) {
            $state = NUKI_GetLockStateOfSmartLock($bridgeID, $this->ReadPropertyString('SmartLockUID'));
            NUKI_UpdateStateOfSmartLocks($bridgeID, true);
        }
        return $state;
    }

    /**
     * Removes the Smart Lock from the bridge.
     *
     * @return string
     */
    public function UnpairSmartlock(): string
    {
        $state = '';
        $bridgeID = $this->GetBridgeInstanceID();
        if ($bridgeID > 0) {
            $state = NUKI_UnpairSmartLockFromBridge($bridgeID, $this->ReadPropertyString('SmartLockUID'));
        }
        return $state;
    }

    /**
     * Toggles the Smart Lock.
     *
     * @param bool $State
     */
    public function ToggleSmartLock(bool $State)
    {
        $action = 255;
        if ($State == false) {
            $action = $this->ReadPropertyString('SwitchOffAction');
        }
        if ($State == true) {
            $action = $this->ReadPropertyString('SwitchOnAction');
        }
        // Set values
        $this->SetValue('SmartLockSwitch', $State);
        $stateName = [ 0 => 'unlock', 1 => 'lock', 3 => 'unlatch', 4 => 'lock ‘n’ go', 5 => 'lock ‘n’ go with unlatch', 255 => 'undefined' ];
        $this->SetValue('SmartLockStatus', $this->Translate($stateName[$action]));
        // Send data to bridge
        $bridgeID = $this->GetBridgeInstanceID();
        $smartLockUniqueID = $this->ReadPropertyString('SmartLockUID');
        if ($bridgeID > 0) {
            NUKI_SetLockActionOfSmartLock($bridgeID, $smartLockUniqueID, $action);
            // Only use if no callback is set
            $useCallback = (bool)IPS_GetProperty($bridgeID, 'UseCallback');
            if (!$useCallback) {
                $this->ShowLockStateOfSmartLock();
            }
        }
    }
}
