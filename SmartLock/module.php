<?

######### NUKI Smart Lock Module for IP-Symcon 5.0 ##########

/**
 * @file		module.php
 *
 * @author		Ulrich Bittner
 * @license		CCBYNC4.0
 * @copyright	(c) 2016, 2017, 2018
 * @version		1.02
 * @date:		2018-04-21, 12:30
 *
 * @see			https://github.com/ubittner/SymconNUKI
 *
 * @bridgeapi	Version 1.5, 2016-12-22
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
 * @changelog	2018-04-21, 12:30, rebuild for IP-Symcon 5.0
 * 				2017-04-19, 23:00, update to API Version 1.5 and some improvements
 * 				2017-01-18, 13:00, initial module script version 1.01
 *
 */


// Definitions
if (!defined('SMARTLOCK_MODULE_GUID')) {
    define("SMARTLOCK_MODULE_GUID", "{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}");
}


class NUKISmartLock extends IPSModule
{
	public function Create()
	{
		parent::Create();

        // Connect to NUKI bridge or create NUKI bridge
        $this->ConnectParent("{B41AE29B-39C1-4144-878F-94C0F7EEC725}");
		// Register properties
		$this->RegisterPropertyString("SmartLockUID", "");
		$this->RegisterPropertyString("SmartLockName", "");
		$this->RegisterPropertyString("SwitchOffAction", "2");
		$this->RegisterPropertyString("SwitchOnAction", "1");
        /*  Switch On / Off Action
         * 	1 unlock
         * 	2 lock
         * 	3 unlatch
         * 	4 lock ‘n’ go
         * 	5 lock ‘n’ go with unlatch
         */
        $this->RegisterPropertyBoolean("HideSmartLockSwitch", false);
        $this->RegisterPropertyBoolean("UseProtocol", false);
        $this->RegisterPropertyInteger("ProtocolEntries", 6);

        // Register profiles
        if (!IPS_VariableProfileExists("NUKI.SmartLockSwitch")) {
            IPS_CreateVariableProfile("NUKI.SmartLockSwitch", 0);
            IPS_SetVariableProfileIcon("NUKI.SmartLockSwitch", "");
            IPS_SetVariableProfileAssociation("NUKI.SmartLockSwitch", 0, $this->Translate("locked"), "LockClosed", 0xFF0000);
            IPS_SetVariableProfileAssociation("NUKI.SmartLockSwitch", 1, $this->Translate("unlocked"), "LockOpen", 0x00FF00);
        }
	}

	public function ApplyChanges()
	{
		parent::ApplyChanges();

		// Rename instance
        $name = $this->ReadPropertyString("SmartLockName");
        if ($name != "") {
            IPS_SetName($this->InstanceID, $name);
        }

        // Register variables
        $switchID = $this->RegisterVariableBoolean("SmartLockSwitch", "NUKI Smart Lock", "NUKI.SmartLockSwitch", 1);
        $this->EnableAction("SmartLockSwitch");
        $HideSmartLockSwitchState = $this->ReadPropertyBoolean("HideSmartLockSwitch");
        IPS_SetHidden($switchID, $HideSmartLockSwitchState);

        $statusID =$this->RegisterVariableString("SmartLockStatus",$this->Translate("State"), "", 2);
        IPS_SetIcon($statusID, "Information");

        $this->RegisterVariableBoolean("SmartLockBatteryState",  $this->Translate("Battery"), "~Battery", 3);

        $protocolID =$this->RegisterVariableString("Protocol", $this->Translate("Protocol"), "~TextBox", 4);
		IPS_SetHidden($protocolID, !$this->ReadPropertyBoolean("UseProtocol"));
        IPS_SetIcon($protocolID, "Database");

		$uniqueID = $this->ReadPropertyString("SmartLockUID");
		if (!empty($uniqueID)) {
		    NUKI_UpdateStateOfSmartLocks($this->GetBridgeInstanceID(), false);
		}

		$this->SetStatus(102);
	}


    public function Destroy()
    {
        $this->DeleteProfiles();
    }


	########## Public functions ##########

    /**
     *  NUKI_ShowLockStateOfSmartLock($SmartLockInstanceID)
     *  Shows the lock state of a smartlock
     *  @return mixed
     */
    public function ShowLockStateOfSmartLock()
    {
        $bridgeID = $this->GetBridgeInstanceID();
        $state = NUKI_GetLockStateOfSmartLock($bridgeID, $this->ReadPropertyString("SmartLockUID"));
        NUKI_UpdateStateOfSmartLocks($bridgeID, true);
        return $state;
    }


    /**
     *  NUKI_UnpairSmarLock($SmartLockInstanceID)
     *  Removes the smartlock from the bridge
     *  @return mixed
     */
    public function UnpairSmartlock()
    {
        $state = NUKI_UnpairSmartLockFromBridge($this->GetBridgeInstanceID(), $this->ReadPropertyString("SmartLockUID"));
        return $state;
    }


    /** NUKI_ToggleSmaertLock($SmartLockInstanceID, $State)
     *  Toggles the smartlock
     *  @param bool $State  true / false
     */
    public function ToggleSmartLock(bool $State)
    {
        $switchState = GetValue($this->GetIDForIdent("SmartLockSwitch"));
        SetValue($this->GetIDForIdent("SmartLockSwitch"), $State);
        if ($State != $switchState) {
            $smartLockUniqueID = $this->ReadPropertyString("SmartLockUID");
            $action = false;
            if ($State == false) {
                $action = $this->ReadPropertyString("SwitchOffAction");
            }
            if ($State == true) {
                $action = $this->ReadPropertyString("SwitchOnAction");
            }
            NUKI_SetLockActionOfSmartLock($this->GetBridgeInstanceID(), $smartLockUniqueID, $action);
            $this->ShowLockStateOfSmartLock();
        }
    }


    ########## Action handler ##########

    public function RequestAction($Ident, $Value)
    {
        switch($Ident) {
            case "SmartLockSwitch":
                $this->ToggleSmartLock($Value);
                break;

            default:
                throw new Exception("Invalid ident", 1);
        }
    }


	########## Protected functions ##########

    /**
     *  Gets the instance id of the related bridge
     *  @return bool
     */
    protected function GetBridgeInstanceID()
    {
        $bridgeID = IPS_GetInstance($this->InstanceID);
        return ($bridgeID['ConnectionID'] > 0) ? $bridgeID['ConnectionID'] : false;
    }

    ########## Private functions ##########

    private function DeleteProfiles()
    {
        // Delete the profiles if no alarm zone instance exists anymore
        $instances = count(IPS_GetInstanceListByModuleID(SMARTLOCK_MODULE_GUID));
        if ($instances === 0) {
            $profiles = array();
            $profiles[0] = "NUKI.SmartLockSwitch";
            foreach ($profiles as $profile) {
                if (IPS_VariableProfileExists($profile)) {
                    IPS_DeleteVariableProfile($profile);
                }
            }
        }
    }

}
