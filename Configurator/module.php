<?php

/*
 * @module      NUKI Configurator
 *
 * @file        module.php
 *
 * @prefix      NUKI
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2019
 * @license     CC BY-NC-SA 4.0
 *
 * @version     1.04
 * @build       1007
 * @date        2019-08-07, 18:00
 *
 * @see         https://github.com/ubittner/SymconNUKI
 *
  * @guids		Library
 * 				{752C865A-5290-4DBE-AC30-01C7B1C3312F}
 *
 *              NUKI Configurator
 *              {1ADAB09D-67EF-412C-B851-B2848C33F67B} (Module GUID)
 *              {73188E44-8BBA-4EBF-8BAD-40201B8866B9} (PR: Device_TX)
 *				{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} (I: 	Device_RX)
 *
 */

// Declare
declare(strict_types=1);

class NUKIConfigurator extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        // Register properties
        $this->RegisterPropertyInteger('CategoryID', 0);

        // Connect to parent (NUKI Bridge, Splitter)
        $this->ConnectParent('{B41AE29B-39C1-4144-878F-94C0F7EEC725}');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    /**
     * Creates a dynamic configuration form.
     *
     * @return string
     */
    public function GetConfigurationForm(): string
    {
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $values = $this->GetConfigurationList();
        $form['actions'][1]['values'] = $values;
        return json_encode($form);
    }

    /**
     * Gets the devices for the configuration list.
     *
     * @return array
     */
    private function GetConfigurationList(): array
    {
        // Get already existing smart lock instances
        $smartLockDevices = IPS_GetInstanceListByModuleID('{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}');
        $this->SendDebug('ExistingInstances', json_encode($smartLockDevices), 0);
        // Get available smart locks from the bridge
        $devices = [];
        $parentID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
        if ($parentID > 0) {
            $status = IPS_GetInstance($parentID)['InstanceStatus'];
            if ($status === 102) {
                $pairedDevices = NUKI_GetSmartLocks(IPS_GetInstance($this->InstanceID)['ConnectionID']);
                if (!empty($pairedDevices)) {
                    $this->SendDebug('PairedDevices', $pairedDevices, 0);
                    $devices = json_decode($pairedDevices, true);
                } else {
                    $devices = null;
                }
                if (empty($devices)) {
                    return [];
                }
            }
        } else {
            return [];
        }
        // Prepare data for configuration list
        $configurationList = [];
        foreach ($devices as $key => $device) {
            $instanceID = 0;
            $smartLockName = $device['name'];
            $smartLockID = (string) $device['nukiId'];
            $this->SendDebug('NukiID', $smartLockID, 0);
            foreach ($smartLockDevices as $smartLockDevice) {
                $this->SendDebug('Device', $smartLockDevice, 0);
                $smartLockUID = (string) IPS_GetProperty($smartLockDevice, 'SmartLockUID');
                $this->SendDebug('NukiID', $smartLockUID, 0);
                if (($smartLockID === $smartLockUID) && (IPS_GetInstance($smartLockDevice)['ConnectionID'] === $parentID)) {
                    $instanceID = $smartLockDevice;
                    $this->SendDebug('InstanceID', $instanceID, 0);
                } else {
                    $this->SendDebug('InstanceID', 'not found!', 0);
                }
            }
            $configurationList[] = [
                'instanceID'      => $instanceID,
                'SmartLockName'   => $smartLockName,
                'SmartLockID'     => $smartLockID,
                'create'          => [
                    'moduleID'      => '{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}',
                    'configuration' => [
                        'SmartLockUID'  => $smartLockID,
                        'SmartLockName' => $smartLockName],
                    'location' => $this->GetCategoryPath($this->ReadPropertyInteger('CategoryID'))]];
        }
        return $configurationList;
    }

    /**
     * Gets the path for Smart Lock category.
     *
     * @param int $CategoryID
     *
     * @return array
     */
    private function GetCategoryPath(int $CategoryID): array
    {
        if ($CategoryID === 0) {
            return [];
        }
        $path[] = IPS_GetName($CategoryID);
        $parentID = IPS_GetObject($CategoryID)['ParentID'];
        while ($parentID > 0) {
            $path[] = IPS_GetName($parentID);
            $parentID = IPS_GetObject($parentID)['ParentID'];
        }
        return array_reverse($path);
    }
}
