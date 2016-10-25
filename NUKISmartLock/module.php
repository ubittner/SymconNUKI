<?

######### NUKI SmartLock Module for IP-Symcon 4.x ########## 

/**
 * @file 		module.php
 * 
 * @author 		Ulrich Bittner
 * @copyright  (c) 2016
 * @version 	1.00
 * @date: 		2016-10-25, 21:00
 *
 * @see        https://gitlab.com/ubittner/ub-nuki
 *
 * @bridgeapi	Version 1.3, 2016-10-07
 *
 * @guids 		{752C865A-5290-4DBE-AC30-01C7B1C3312F} NUKILibrary
 *          	{B41AE29B-39C1-4144-878F-94C0F7EEC725} NUKIBridge
 *          	{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14} NUKISmartLock
 *          	{73188E44-8BBA-4EBF-8BAD-40201B8866B9} NUKISmartLock (I/O) TX (PR)
 *          	{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} NUKISmartLock (I/O) RX (I)
 */


class NUKISmartLock extends IPSModule
{
	public function Create()
	{
		parent::Create();

		$this->RegisterPropertyString("NUKISmartLockName", "");
		$this->RegisterPropertyString("NUKISmartLockID", "");
		
		/**
		 * 	1 unlock
		 * 	2 lock
		 * 	3 unlatch
		 * 	4 lock ‘n’ go
		 * 	5 lock ‘n’ go with unlatch
		 */
		$this->RegisterPropertyString("SwitchOffAction", "1");
		$this->RegisterPropertyString("SwitchOnAction", "2");
		$this->RegisterPropertyBoolean("HideSmartLockSwitch", false);
	}

	public function ApplyChanges()
	{
		parent::ApplyChanges();

		$this->ConnectParent("{B41AE29B-39C1-4144-878F-94C0F7EEC725}");

		$SmartLockSwitchObjectId = $this->RegisterVariableBoolean("NUKISmartLockSwitch", "NUKI SmartLock", "~Lock", 1);
		$this->EnableAction("NUKISmartLockSwitch");
		$HideSmartLockSwitchState = $this->ReadPropertyBoolean("HideSmartLockSwitch");
		IPS_SetHidden($SmartLockSwitchObjectId, $HideSmartLockSwitchState);

		//$this->RegisterVariableString("NUKISmatLockStatus","NUKI SmartLock Status", "", 2);

		$this->SetStatus(102);
	}


	#####################################################################################################################################
	## start of modul functions 																												  						  ##
	#####################################################################################################################################
	

	########## public functions ##########
	

  	/**
  	 *		NUKI_showLockStateOfSmartLock($SmartLockInstanceId)
  	 *		shows the lock state of a smartlock
  	 */
	public function showLockStateOfSmartLock ()
	{
		$SmartLockUniqueID = $this->ReadPropertyString("NUKISmartLockID");
		$SmartLockState = NUKI_getLockStateOfSmartLock($this->getBridgeInstanceId(), $SmartLockUniqueID);	
		print_r($SmartLockState);
   }

	/**
  	 *		NUKI_showInformationOfBridge($SmartLockInstanceId)
  	 *		shows the lock state of a smartlock
  	 */
	public function showInformationOfBridge ()
	{
		$BridgeData = NUKI_getBridgeInfo($this->getBridgeInstanceId());	
		print_r($BridgeData);
   }
	

	public function RequestAction($Ident, $Value)
	{
		try {
			switch($Ident) {
			    case "NUKISmartLockSwitch":
			    	$SmartLockUniqueID = $this->ReadPropertyString("NUKISmartLockID");
			    	$Switch = SetValue($this->GetIDForIdent($Ident), $Value);
			    	if ($Value == false) {
			    		$LockAction = $this->ReadPropertyString("SwitchOffAction");
			    	}
			    	if ($Value == true) {
			    		$LockAction = $this->ReadPropertyString("SwitchOnAction");

			    	}
			    	$ExecuteLockAction = NUKI_setLockActionOfSmartLock($this->getBridgeInstanceId(), $SmartLockUniqueID, $LockAction);
				break;
				default:
				throw new Exception("Invalid ident", 1);
			}
		}
		catch (Exception $Exception) {
			$ErrorMessage = "Error, Code: ".$Exception->getCode().", ".$Exception->getMessage();
			echo $ErrorMessage."\n";
		}
	}


	########## protected functions ##########


	/**
	 *		gets the instance id of the related bridge
	 */
	protected function getBridgeInstanceId() {
    $BridgeInstanceId = IPS_GetInstance($this->InstanceID);
    return ($BridgeInstanceId['ConnectionID'] > 0) ? $BridgeInstanceId['ConnectionID'] : false;
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