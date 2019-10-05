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
     * @return false|string
     */
    public function GetConfigurationForm()
    {
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $pairedDevices = json_decode($this->GetPairedDevices(), true);
        $this->SendDebug(__FUNCTION__ . ' Paired Devices', json_encode($pairedDevices), 0);
        $values = [];
        $location = $this->GetCategoryPath($this->ReadPropertyInteger(('CategoryID')));
        // Paired devices
        if (!empty($pairedDevices)) {
            foreach ($pairedDevices as $key => $device) {
                if (array_key_exists('deviceType', $device)) {
                    $deviceType = $device['deviceType'];
                    $deviceName = $device['name'];
                    $nukiID = $device['nukiId'];
                    switch ($deviceType) {
                        // Smart Lock
                        case 0:
                            $instanceID = $this->GetDeviceInstances($nukiID, 0);
                            $this->SendDebug(__FUNCTION__ . ' NUKI Smart Lock ID', json_encode($nukiID), 0);
                            $this->SendDebug(__FUNCTION__ . ' NUKI Smart Lock Instance ID', json_encode($instanceID), 0);
                            $values[] = [
                                'DeviceID' => $nukiID,
                                'DeviceType' => $deviceType,
                                'ProductDesignation' => 'Smart Lock',
                                'DeviceName' => $deviceName,
                                'instanceID' => $instanceID,
                                'create' => [
                                    'moduleID' => '{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}',
                                    'configuration' => [
                                        'SmartLockUID' => (string)$nukiID,
                                        'SmartLockName' => (string)$deviceName
                                    ],
                                    'location' => $location
                                ]
                            ];
                            break;
                        // Opener
                        case 2:
                            $instanceID = $this->GetDeviceInstances($nukiID, 2);
                            $this->SendDebug(__FUNCTION__ . ' NUKI Opener ID', json_encode($nukiID), 0);
                            $this->SendDebug(__FUNCTION__ . ' NUKI Opener Instance ID', json_encode($instanceID), 0);
                            $values[] = [
                                'DeviceID' => $nukiID,
                                'DeviceType' => $deviceType,
                                'ProductDesignation' => 'Opener',
                                'DeviceName' => $deviceName,
                                'instanceID' => $instanceID,
                                'create' => [
                                    'moduleID' => '{057995F0-F9A9-C6F4-C882-C47A259419CE}',
                                    'configuration' => [
                                        'OpenerUID' => (string)$nukiID,
                                        'OpenerName' => (string)$deviceName
                                    ],
                                    'location' => $location
                                ]
                            ];
                            break;
                    }
                }
            }
        }
        $form['actions'][0]['values'] = $values;
        return json_encode($form);
    }

    //################### Private

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

    /**
     * Gets the paired devices of the bridge.
     *
     * @return array|mixed
     */
    private function GetPairedDevices(): string
    {
        if (!$this->HasActiveParent()) {
            return '';
        }
        $data = [];
        $buffer = [];
        $data['DataID'] = '{73188E44-8BBA-4EBF-8BAD-40201B8866B9}';
        $buffer['Command'] = 'GetPairedDevices';
        $buffer['Params'] = '';
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode($this->SendDataToParent($data), true);
        if (!$result) {
            return '';
        }
        return $result;
    }

    /**
     * Gets the instance id for an existing device.
     *
     * @param $DeviceUID
     * @param $DeviceType
     * @return int
     */
    private function GetDeviceInstances($DeviceUID, $DeviceType)
    {
        $instanceID = 0;
        switch ($DeviceType) {
            // Smart Lock
            case 0:
                $moduleID = '{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}';
                $propertyUIDName = 'SmartLockUID';
                break;
            // Opener
            case 2:
                $moduleID = '{057995F0-F9A9-C6F4-C882-C47A259419CE}';
                $propertyUIDName = 'OpenerUID';
                break;
            default:
                $moduleID = '{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}';
                $propertyUIDName = 'SmartLockUID';
        }
        $instanceIDs = IPS_GetInstanceListByModuleID($moduleID);
        foreach ($instanceIDs as $id) {
            if (IPS_GetProperty($id, $propertyUIDName) == $DeviceUID) {
                $instanceID = $id;
            }
        }
        return $instanceID;
    }
}
