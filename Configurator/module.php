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
 * @version     1.05
 * @build       1008
 * @date        2019-09-26, 18:00
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
        $form['actions'][0]['values'] = $values;
        return json_encode($form);
    }

    /**
     * Gets the devices for the configuration list.
     *
     * @return array
     */
    private function GetConfigurationList(): array
    {
        // Get already existing devices
        $smartLockDevices = IPS_GetInstanceListByModuleID('{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}');
        $openerDevices = IPS_GetInstanceListByModuleID('{057995F0-F9A9-C6F4-C882-C47A259419CE}');
        $existingDevices = array_merge($smartLockDevices, $openerDevices);
        $this->SendDebug('ExistingInstances', json_encode($existingDevices), 0);
        // Get available devices from the bridge
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
            $deviceType = (string)$device['deviceType'];
            switch ($deviceType) {
                case 0:
                    $typeDesignation = 'Smart Lock';
                    break;
                case 2:
                    $typeDesignation = 'Opener';
                    break;
                default:
                    $typeDesignation = $this->translate('Unknown');
            }
            $deviceID = (string)$device['nukiId'];
            $deviceName = (string)$device['name'];
            $moduleID = '';
            $propertyUID = '';
            $propertyName = '';
            foreach ($existingDevices as $existingDevice) {
                $moduleID = IPS_GetInstance($existingDevice)['ModuleInfo']['ModuleID'];
                $deviceUID = 0;
                // Smart Lock
                if ($moduleID == '{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}') {
                    $propertyUID = 'SmartLockUID';
                    $propertyName = 'SmartLockName';
                    $deviceUID = (string)IPS_GetProperty($existingDevice, $propertyUID);

                }
                // Opener
                if ($moduleID == '{057995F0-F9A9-C6F4-C882-C47A259419CE}') {
                    $propertyUID = 'OpenerUID';
                    $propertyName = 'OpenerName';
                    $deviceUID = (string)IPS_GetProperty($existingDevice, $propertyUID);
                }
                if (($deviceID === $deviceUID) && (IPS_GetInstance($existingDevice)['ConnectionID'] === $parentID)) {
                    $instanceID = $existingDevice;
                    $this->SendDebug('InstanceID', $instanceID, 0);
                } else {
                    $this->SendDebug('InstanceID', 'not found!', 0);
                }
            }
            $configurationList[] = [
                'instanceID' => $instanceID,
                'DeviceID' => $deviceID,
                'DeviceType' => $deviceType,
                'TypeDescription' =>  $typeDesignation,
                'DeviceName' => $deviceName,
                'create' => [
                    'moduleID' => $moduleID,
                    'configuration' => [
                        $propertyUID => $deviceID,
                        $propertyName => $deviceName],
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
