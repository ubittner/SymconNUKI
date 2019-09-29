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
        IPS_SetVariableProfileIcon($profile, '');
        IPS_SetVariableProfileAssociation($profile, 0, $this->Translate('Actuate'), 'Door', 0x00FF00);
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
        $this->EnableAction('DoorBuzzer');

        $this->MaintainVariable('OpenerMode', $this->Translate('Mode'), 3, '', 2, true);
        IPS_SetIcon($this->GetIDForIdent('OpenerMode'), 'Information');

        $this->MaintainVariable('OpenerState', $this->Translate('State'), 3, '', 3, true);
        IPS_SetIcon($this->GetIDForIdent('OpenerState'), 'Information');

        $this->MaintainVariable('BatteryState', $this->Translate('Battery'), 0, '~Battery', 4, true);

        // Get actual state
        //$this->GetOpenerState();
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

    //#################### Public

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
        $data['DataID'] = '{73188E44-8BBA-4EBF-8BAD-40201B8866B9}';
        $buffer['Command'] = 'GetLockState';
        $buffer['Params'] = ['nukiId' => (int)$nukiID, 'deviceType' => 2];
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
     * Opens the door via buzzer.
     *
     * @return bool
     */
    public function BuzzDoor(): bool
    {
        $result = $this->SetLockAction(3);
        return $result;
    }

    /**
     * Toggles the ring to open function of the opener.
     *
     * @param bool $State
     * @return bool
     */
    public function ToggleRingToOpen(bool $State): bool
    {
        // Deactivate
        $lockAction = 2;
        // Activate
        if ($State) {
            $lockAction = 1;
        }
        $result = $this->SetLockAction($lockAction);
        return $result;
    }

    /**
     * Toggles the continuous mode of the opener.
     *
     * @param bool $State
     * @return bool
     */
    public function ToggleContinuousMode(bool $State): bool
    {
        // Deactivate
        $lockAction = 5;
        // Activate
        if ($State) {
            $lockAction = 4;
        }
        $result = $this->SetLockAction($lockAction);
        return $result;
    }

    //########## Private

    /**
     * Set the state of the opener.
     *
     * @param string $Data
     */
    private function SetOpenerState(string $Data)
    {
        $this->SendDebug(__FUNCTION__ . ' Data', $Data, 0);
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
        $data['DataID'] = '{73188E44-8BBA-4EBF-8BAD-40201B8866B9}';
        $buffer['Command'] = 'SetLockAction';
        $buffer['Params'] = ['nukiId' => (int)$nukiID, 'lockAction' => $LockAction, 'deviceType' => 2];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode(json_decode($this->SendDataToParent($data), true), true);
        if (array_key_exists('success', $result)) {
            $success = $result['success'];
        }
        if (array_key_exists('batteryCritical', $result)) {
            $this->SetValue('BatteryState', $result['batteryCritical']);
        }
        return $success;
    }
}
