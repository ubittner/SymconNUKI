<?php

/*
 * @module      NUKI Opener
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
 *				NUKI Opener (Device)
 *				{057995F0-F9A9-C6F4-C882-C47A259419CE} (Module GUID)
 * 				{73188E44-8BBA-4EBF-8BAD-40201B8866B9} (PR: Device_TX)
 *				{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} (I: 	Device_RX)
 *
 */

// Declare
declare(strict_types=1);

// Definitions
if (!defined('OPENER_MODULE_GUID')) {
    define('OPENER_MODULE_GUID', '{057995F0-F9A9-C6F4-C882-C47A259419CE}');
}

class NUKIOpener extends IPSModule
{
    public function Create()
    {
        parent::Create();

        // Connect to NUKI bridge or create NUKI bridge
        $this->ConnectParent('{B41AE29B-39C1-4144-878F-94C0F7EEC725}');

        // Register properties
        $this->RegisterPropertyString('OpenerUID', '');
        $this->RegisterPropertyString('OpenerName', '');

        // Register profiles
        $profile = 'NUKI.' . $this->InstanceID . '.DoorBuzzer';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileAssociation($profile, 1, $this->Translate('open'), 'LockOpen', 0x00FF00);
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
        $name = $this->ReadPropertyString('OpenerName');
        if ($name != '') {
            IPS_SetName($this->InstanceID, $name);
        }

        // Register variables
        $profile = 'NUKI.' . $this->InstanceID . '.DoorBuzzer';
        $this->MaintainVariable('DoorBuzzer', $this->Translate('Door buzzer'), 1, $profile, 1, true);

        $statusID = $this->RegisterVariableString('OpenerStatus', $this->Translate('State'), '', 2);
        IPS_SetIcon($statusID, 'Information');

        $this->RegisterVariableBoolean('OpenerBatteryState', $this->Translate('Battery'), '~Battery', 3);

        $uniqueID = $this->ReadPropertyString('OpenerUID');
        if (!empty($uniqueID)) {
            NUKI_UpdateStateOfSmartLocks($this->GetBridgeInstanceID(), false);
        }

        $this->SetStatus(102);
    }

    public function Destroy()
    {
        // Delete profiles
        $profile = 'NUKI.' . $this->InstanceID . '.DoorBuzzer';
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
            case 'DoorBuzzer':
                $this->BuzzDoor();
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
    public function ShowDeviceState(): string
    {
        $state = '';
        $bridgeID = $this->GetBridgeInstanceID();
        if ($bridgeID > 0) {
            $state = NUKI_GetLockStateOfSmartLock($bridgeID, $this->ReadPropertyString('OpenerUID'));
            NUKI_UpdateStateOfSmartLocks($bridgeID, true);
        }
        return $state;
    }

    /**
     * Removes the Smart Lock from the bridge.
     *
     * @return string
     */
    public function UnpairDevice(): string
    {
        $state = '';
        $bridgeID = $this->GetBridgeInstanceID();
        if ($bridgeID > 0) {
            $state = NUKI_UnpairSmartLockFromBridge($bridgeID, $this->ReadPropertyString('OpenerUID'));
        }
        return $state;
    }

    /**
     * Opens the door via buzzer.
     */
    public function BuzzDoor()
    {
        // Send data to bridge
        $bridgeID = $this->GetBridgeInstanceID();
        $openerUniqueID = $this->ReadPropertyString('OpenerUID');
        if ($bridgeID > 0) {
            NUKI_SetLockActionOfSmartLock($bridgeID, $openerUniqueID, $action = 0);
        }
    }
}
