<?php

// Declare
declare(strict_types=1);

trait Control
{
    /**
     * Syncs the Smart Locks of the bridge.
     */
    public function SyncSmartLocks()
    {
        $categoryID = $this->ReadPropertyInteger('SmartLockCategory');
        $smartLocks = $this->GetSmartLocks();
        if (!empty($smartLocks)) {
            $smartLocks = json_decode($smartLocks, true);
            foreach ($smartLocks as $smartLock) {
                $uniqueID = $smartLock['nukiId'];
                $name = utf8_decode((string) $smartLock['name']);
                $instanceID = $this->GetSmartLockInstanceIdByUniqueId($uniqueID);
                if ($instanceID == 0) {
                    $instanceID = IPS_CreateInstance(SMARTLOCK_MODULE_GUID);
                    IPS_SetProperty($instanceID, 'SmartLockUID', $uniqueID);
                }
                IPS_SetProperty($instanceID, 'SmartLockName', $name);
                IPS_SetName($instanceID, $name);
                IPS_SetParent($instanceID, $categoryID);
                if (IPS_GetInstance($instanceID)['ConnectionID'] != $this->InstanceID) {
                    @IPS_DisconnectInstance($instanceID);
                    IPS_ConnectInstance($instanceID, $this->InstanceID);
                }
                IPS_ApplyChanges($instanceID);
            }
            echo $this->Translate('Smart Locks have been matched / created!');
        }
    }

    /**
     * Gets the instance id of the smartlock by UID.
     *
     * @param $UniqueID
     *
     * @return int
     */
    private function GetSmartLockInstanceIdByUniqueId($UniqueID): int
    {
        $id = 0;
        $instanceIDs = IPS_GetInstanceListByModuleID(SMARTLOCK_MODULE_GUID);
        foreach ($instanceIDs as $instanceID) {
            if (IPS_GetProperty($instanceID, 'SmartLockUID') == $UniqueID) {
                $id = $instanceID;
            }
        }
        return $id;
    }

    /**
     * Sets the state of a smartlock.
     *
     * @param string $SmartLockData
     * @param bool   $ProtocolMode
     */
    private function SetStateOfSmartLock(string $SmartLockData, bool $ProtocolMode)
    {
        if (!empty($SmartLockData)) {
            IPS_LogMessage('SmartLockData', $SmartLockData);
            //$data = json_decode($SmartLockData, true);
            //IPS_LogMessage('SetStateOfSmartLock', 'Data:'.print_r($data));
            $data = json_decode($SmartLockData, true);
            $nukiID = $data['nukiId'];
            IPS_LogMessage('SetStateOfSmartLock', 'Nuki ID:'.$nukiID);
            $state = $data['state'];
            IPS_LogMessage('SetStateOfSmartLock', 'State:'.$nukiID);
            $stateName = $this->Translate($SmartLockData['stateName']);
            $batteryState = $data['batteryCritical'];
            switch ($state) {
                // switch off (locked) = false, switch on (unlocked) = true
                case 0:
                    // uncalibrated
                    $state = false;
                    break;
                case 1:
                    // locked
                    $state = false;
                    break;
                case 2:
                    // unlocking
                    $state = true;
                    break;
                case 3:
                    // unlocked
                    $state = true;
                    break;
                case 4:
                    // locking
                    $state = false;
                    break;
                case 5:
                    // unlatched
                    $state = true;
                    break;
                case 6:
                    // unlocked (lock ‘n’ go)
                    $state = true;
                    break;
                case 7:
                    // unlatching
                    $state = true;
                    break;
                default:
                    $state = false;
                    break;
            }
            $instanceIDs = IPS_GetInstanceListByModuleID(SMARTLOCK_MODULE_GUID);
            foreach ($instanceIDs as $instanceID) {
                $uniqueID = IPS_GetProperty($instanceID, 'SmartLockUID');
                if ($nukiID == $uniqueID) {
                    $smartLockName = IPS_GetName($instanceID);
                    $switchID = IPS_GetObjectIDByIdent('SmartLockSwitch', $instanceID);
                    SetValue($switchID, $state);
                    $stateID = IPS_GetObjectIDByIdent('SmartLockStatus', $instanceID);
                    SetValue($stateID, $stateName);
                    $batteryStateID = IPS_GetObjectIDByIdent('SmartLockBatteryState', $instanceID);
                    SetValue($batteryStateID, $batteryState);
                    if ($ProtocolMode == true) {
                        if (IPS_GetProperty($instanceID, 'UseProtocol') == true) {
                            $date = date('d.m.Y');
                            $time = date('H:i:s');
                            $string = "{$date}, {$time}, {$smartLockName}, UID: {$nukiID}, Status: {$stateName}.";
                            $protocolID = IPS_GetObjectIDByIdent('Protocol', $instanceID);
                            $entries = json_decode(IPS_GetConfiguration($instanceID))->ProtocolEntries;
                            if ($entries == 1) {
                                SetValue($protocolID, $string);
                            }
                            if ($entries > 1) {
                                // Get old content first
                                $content = array_merge(array_filter(explode("\n", GetValue($protocolID))));
                                $records = $entries - 1;
                                array_splice($content, $records);
                                array_unshift($content, $string);
                                $newContent = implode("\n", $content);
                                SetValue($protocolID, $newContent);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Updates the state of all smartlocks of a bridge.
     *
     * @param bool $ProtocolMode
     */
    public function UpdateStateOfSmartLocks(bool $ProtocolMode)
    {
        $instanceIDs = IPS_GetInstanceListByModuleID(SMARTLOCK_MODULE_GUID);
        if (!empty($instanceIDs)) {
            foreach ($instanceIDs as $instanceID) {
                $uniqueID = (int) IPS_GetProperty($instanceID, 'SmartLockUID');
                if (!empty($uniqueID)) {
                    $data = $this->GetLockStateOfSmartLock($uniqueID);
                    if (!empty($data)) {
                        $data = json_decode($data, true);
                        $data['nukiId'] = $uniqueID;
                        $data = json_encode($data);
                        $this->SetStateOfSmartLock($data, $ProtocolMode);
                    }
                }
            }
        }
    }
}
