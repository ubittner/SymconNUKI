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
        $smartLocks = json_decode($this->GetSmartLocks());
        $this->SendDebug(__FUNCTION__ . ' Smart Locks', json_encode($smartLocks), 0);
        $openers = json_decode($this->GetOpeners());
        $this->SendDebug(__FUNCTION__ . ' Openers', json_encode($openers), 0);
        $values = [];
        $location = $this->GetCategoryPath($this->ReadPropertyInteger(('CategoryID')));
        // Smart Locks
        if (!empty($smartLocks)) {
            foreach ($smartLocks as $smartLock) {
                if (array_key_exists('nukiId', $smartLock)) {
                    $instanceID = $this->GetDeviceInstances($smartLock->nukiId, 0);
                    $this->SendDebug(__FUNCTION__ . ' NUKI Smart Lock UID', json_encode($smartLock->nukiId), 0);
                    $this->SendDebug(__FUNCTION__ . ' NUKI Smart Lock InstanceID', json_encode($instanceID), 0);
                    $addValueSmartLocks = [
                        'DeviceID' => $smartLock->nukiId,
                        'DeviceType' => $smartLock->deviceType,
                        'TypeDesignation' => 'Smart Lock',
                        'DeviceName' => $smartLock->name,
                        'instanceID' => $instanceID
                    ];
                    $addValueSmartLocks['create'] = [
                        'moduleID' => '{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}',
                        'configuration' => [
                            'SmartLockUID' => $smartLock->nukiId,
                            'SmartLockName' => $smartLock->name
                        ],
                        'location' => $location
                    ];
                    $values[] = $addValueSmartLocks;
                }
            }
        }
        // Openers
        if (!empty($openers)) {
            foreach ($openers as $opener) {
                if (array_key_exists('nukiId', $opener)) {
                    $instanceID = $this->GetDeviceInstances($opener->nukiId, 2);
                    $this->SendDebug(__FUNCTION__ . ' NUKI Opener UID', json_encode($opener->nukiId), 0);
                    $this->SendDebug(__FUNCTION__ . ' NUKI Opener InstanceID', json_encode($instanceID), 0);
                    $addValueOpeners = [
                        'DeviceID' => $opener->nukiId,
                        'DeviceType' => $opener->deviceType,
                        'TypeDesignation' => 'Opener',
                        'DeviceName' => $opener->name,
                        'instanceID' => $instanceID
                    ];
                    $addValueOpeners['create'] = [
                        'moduleID' => '{057995F0-F9A9-C6F4-C882-C47A259419CE}',
                        'configuration' => [
                            'OpenerUID' => $opener->nukiId,
                            'OpenerName' => $opener->name
                        ],
                        'location' => $location
                    ];
                    $values[] = $addValueOpeners;
                }
            }
        }
        $form['actions'][0]['values'] = $values;
        return json_encode($form);
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

    /**
     * Gets the smart locks, paired to the bridge.
     *
     * @return array|mixed
     */
    private function GetSmartLocks()
    {
        $data = [];
        $buffer = [];
        $data['DataID'] = '{73188E44-8BBA-4EBF-8BAD-40201B8866B9}';
        $buffer['Command'] = 'GetPairedSmartLocks';
        $buffer['Params'] = '';
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode($this->SendDataToParent($data), true);
        if (!$result) {
            return [];
        }
        return $result;
    }

    /**
     * Gets the openers, paired to the bridge.
     *
     * @return array|mixed
     */
    private function GetOpeners()
    {
        $data = [];
        $buffer = [];
        $data['DataID'] = '{73188E44-8BBA-4EBF-8BAD-40201B8866B9}';
        $buffer['Command'] = 'GetPairedOpeners';
        $buffer['Params'] = '';
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode($this->SendDataToParent($data), true);
        if (!$result) {
            return [];
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
