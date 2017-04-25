<?

######### NUKI Smart Lock Module for IP-Symcon 4.1 ##########

/**
 * @file 		module.php
 *
 * @author 		Ulrich Bittner
 * @license		CCBYNC4.0
 * @copyright  (c) 2016, 2017
 * @version 	1.02
 * @date: 		2017-04-19, 23:00
 *
 * @see        https://github.com/ubittner/SymconNUKI
 *
 * @bridgeapi	Version 1.5, 2016-12-22
 *
 * @guids 		{752C865A-5290-4DBE-AC30-01C7B1C3312F} NUKI Library
 *
 *          	{B41AE29B-39C1-4144-878F-94C0F7EEC725} NUKI Bridge
 *          	{73188E44-8BBA-4EBF-8BAD-40201B8866B9} NUKI Bridge (I/O) TX (I)
 *          	{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} NUKI Bridge (I/O) RX (CR)
 *
 *          	{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14} NUKI Smart Lock
 *
 * 				{8062CF2B-600E-41D6-AD4B-1BA66C32D6ED} NUKI Socket (Server Socket)
 *
 * @changelog	2017-04-19, 23:00, update to API Version 1.5 and some improvements
 * 				2017-01-18, 13:00, initial module script version 1.01
 *
 */


class NUKISmartLock extends IPSModule
{
	public function Create()
	{
		parent::Create();

		$this->RegisterPropertyString("NUKISmartLockUID", "");
		$this->RegisterPropertyString("NUKISmartLockName", "");

		/**
		 * 	1 unlock
		 * 	2 lock
		 * 	3 unlatch
		 * 	4 lock ‘n’ go
		 * 	5 lock ‘n’ go with unlatch
		 */

		$this->RegisterPropertyString("SwitchOffAction", "2");
		$this->RegisterPropertyString("SwitchOnAction", "1");
		$this->RegisterPropertyBoolean("HideSmartLockSwitch", false);

		$this->RegisterProfileBooleanEX("NUKI.SmartLockSwitch", "", "", "", array(
			array(
				0,
				"verriegelt",
				"LockClosed",
				0xFF0000,
			),
			array(
				1,
				"entriegelt",
				"LockOpen",
				0x00FF00,
			),
		));
	}

	public function ApplyChanges()
	{
		parent::ApplyChanges();

		$this->ConnectParent("{B41AE29B-39C1-4144-878F-94C0F7EEC725}");

		$SmartLockSwitchObjectID = $this->RegisterVariableBoolean("NUKISmartLockSwitch", "NUKI Smart Lock", "NUKI.SmartLockSwitch", 1);
		$this->EnableAction("NUKISmartLockSwitch");
		$HideSmartLockSwitchState = $this->ReadPropertyBoolean("HideSmartLockSwitch");
		IPS_SetHidden($SmartLockSwitchObjectID, $HideSmartLockSwitchState);

		$SmartLockStatusObjectID =$this->RegisterVariableString("NUKISmatLockStatus","Status", "", 2);
		IPS_SetIcon($SmartLockStatusObjectID, "Information");

		$this->RegisterVariableBoolean("NUKISmartLockBatteryState", "Batterie", "~Battery", 3);

		$SmartLockUniqueID = $this->ReadPropertyString("NUKISmartLockUID");
		if (!empty($SmartLockUniqueID)) {
			$UpdateState = NUKI_updateStateOfSmartLocks($this->getBridgeInstanceID());
		}
		$this->SetStatus(102);
	}


	#####################################################################################################################################
	## start of modul functions 																												  						  ##
	#####################################################################################################################################

	########## public functions ##########

  	/**
  	 *		NUKI_showLockStateOfSmartLock($SmartLockInstanceID)
  	 *  	Shows the lock state of a smartlock
  	 */

	public function showLockStateOfSmartLock ()
	{
		$SmartLockUniqueID = $this->ReadPropertyString("NUKISmartLockUID");
		$SmartLockState = NUKI_getLockStateOfSmartLock($this->getBridgeInstanceID(), $SmartLockUniqueID);
		$UpdateState = NUKI_updateStateOfSmartLocks($this->getBridgeInstanceID());
		return $SmartLockState;
   }

	/**
  	 *		NUKI_unpairSmarLock($SmartLockInstanceID)
  	 *		Removes the smartlock from the bridge
  	 */

	public function unpairSmartlock ()
	{
		$SmartLockUniqueID = $this->ReadPropertyString("NUKISmartLockUID");
		$UnpairState = NUKI_unpairSmartLockFromBridge($this->getBridgeInstanceID(), $SmartLockUniqueID);
		return $UnpairState;

	}

	public function RequestAction($Ident, $Value)
	{
		switch($Ident) {
			case "NUKISmartLockSwitch":
				$SmartLockUniqueID = $this->ReadPropertyString("NUKISmartLockUID");
				$Switch = SetValue($this->GetIDForIdent($Ident), $Value);
			   if ($Value == false) {
			    	$LockAction = $this->ReadPropertyString("SwitchOffAction");
			   }
			   if ($Value == true) {
			   	$LockAction = $this->ReadPropertyString("SwitchOnAction");
				}
			   $ExecuteLockAction = NUKI_setLockActionOfSmartLock($this->getBridgeInstanceID(), $SmartLockUniqueID, $LockAction);
			break;

			default:
				throw new Exception("Invalid ident", 1);
		}
	}

	########## protected functions ##########

	/**
	 *		Gets the instance id of the related bridge
	 */

	protected function getBridgeInstanceID()
	{
		$BridgeInstanceID = IPS_GetInstance($this->InstanceID);
    	return ($BridgeInstanceID['ConnectionID'] > 0) ? $BridgeInstanceID['ConnectionID'] : false;
  	}

	protected function registerProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
	{
		if (!IPS_VariableProfileExists($Name)) {
		IPS_CreateVariableProfile($Name, 0);
		}
	   else {
		$Profile = IPS_GetVariableProfile($Name);
			if ($Profile['ProfileType'] != 0)
				throw new Exception("Variable profile type does not match for profile ".$Name);
	   }
	   IPS_SetVariableProfileIcon($Name, $Icon);
		IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
	   IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
	}

	protected function registerProfileBooleanEx($Name, $Icon, $Prefix, $Suffix, $Associations)
	{
		if (sizeof($Associations) === 0 ){
		$MinValue = 0;
		  $MaxValue = 0;
	   }
	   else {
		$MinValue = $Associations[0][0];
		  $MaxValue = $Associations[sizeof($Associations)-1][0];
	   }
	   $this->registerProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
	   foreach ($Associations as $Association) {
		IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
	   }
	}

	protected function registerProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
	{
		if (!IPS_VariableProfileExists($Name)) {
		IPS_CreateVariableProfile($Name, 1);
	   }
	   else {
		$Profile = IPS_GetVariableProfile($Name);
		if ($Profile['ProfileType'] != 1)
		  throw new Exception("Variable profile type does not match for profile ".$Name);
	   }
	   IPS_SetVariableProfileIcon($Name, $Icon);
	   IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
	   IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
	}

	protected function registerProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations)
	{
		if (sizeof($Associations) === 0 ){
		$MinValue = 0;
		  $MaxValue = 0;
	   }
	   else {
		$MinValue = $Associations[0][0];
		  $MaxValue = $Associations[sizeof($Associations)-1][0];
	   }
	   $this->registerProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
	   foreach ($Associations as $Association) {
		IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
	   }
	}

}
?>
