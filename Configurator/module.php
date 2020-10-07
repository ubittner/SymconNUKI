<?php

/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

/*
 * @module      NUKI Configurator
 *
 * @prefix      NUKI
 *
 * @file        module.php
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2019, 2020
 * @license     CC BY-NC-SA 4.0
 *              https://creativecommons.org/licenses/by-nc-sa/4.0/
 *
 * @see         https://github.com/ubittner/SymconNUKI/Configurator
 *
 * @guids		Library
 * 				{752C865A-5290-4DBE-AC30-01C7B1C3312F}
 *
 *              NUKI Configurator
 *              {1ADAB09D-67EF-412C-B851-B2848C33F67B}
 */

declare(strict_types=1);

//Include
include_once __DIR__ . '/../libs/constants.php';

class NUKIConfigurator extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterProperties();
        //Connect to parent (NUKI Bridge, Splitter)
        $this->ConnectParent(NUKI_BRIDGE_GUID);
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        //Never delete this line!
        parent::ApplyChanges();
        //Check runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->SendDebug(__FUNCTION__, $TimeStamp . ', SenderID: ' . $SenderID . ', Message: ' . $Message . ', Data: ' . print_r($Data, true), 0);
        if (!empty($Data)) {
            foreach ($Data as $key => $value) {
                $this->SendDebug(__FUNCTION__, 'Data[' . $key . '] = ' . json_encode($value), 0);
            }
        }
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;

        }
    }

    public function GetConfigurationForm()
    {
        $formData = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $moduleInfo = [];
        $library = IPS_GetLibrary(NUKI_LIBRARY_GUID);
        $module = IPS_GetModule(NUKI_CONFIGURATOR_GUID);
        $moduleInfo['name'] = $module['ModuleName'];
        $moduleInfo['version'] = $library['Version'] . '-' . $library['Build'];
        $moduleInfo['date'] = date('d.m.Y', $library['Date']);
        $moduleInfo['time'] = date('H:i', $library['Date']);
        $moduleInfo['developer'] = $library['Author'];
        $formData['elements'][1]['items'][1]['caption'] = "ID:\t\t\t\t" . $this->InstanceID;
        $formData['elements'][1]['items'][2]['caption'] = $this->Translate("Module:\t\t\t") . $moduleInfo['name'];
        $formData['elements'][1]['items'][3]['caption'] = "Version:\t\t\t" . $moduleInfo['version'];
        $formData['elements'][1]['items'][4]['caption'] = $this->Translate("Date:\t\t\t") . $moduleInfo['date'];
        $formData['elements'][1]['items'][5]['caption'] = $this->Translate("Time:\t\t\t") . $moduleInfo['time'];
        $formData['elements'][1]['items'][6]['caption'] = $this->Translate("Developer:\t\t") . $moduleInfo['developer'];
        $pairedDevices = json_decode($this->GetPairedDevices(), true);
        $this->SendDebug(__FUNCTION__ . ' Paired Devices', json_encode($pairedDevices), 0);
        $values = [];
        $location = $this->GetCategoryPath($this->ReadPropertyInteger(('CategoryID')));
        //Paired devices
        if (!empty($pairedDevices)) {
            foreach ($pairedDevices as $key => $device) {
                if (array_key_exists('deviceType', $device)) {
                    $deviceType = $device['deviceType'];
                    $deviceName = $device['name'];
                    $nukiID = $device['nukiId'];
                    switch ($deviceType) {
                        //Smart Lock
                        case 0:
                            $instanceID = $this->GetDeviceInstances($nukiID, 0);
                            $this->SendDebug(__FUNCTION__ . ' NUKI Smart Lock ID', json_encode($nukiID), 0);
                            $this->SendDebug(__FUNCTION__ . ' NUKI Smart Lock Instance ID', json_encode($instanceID), 0);
                            $values[] = [
                                'DeviceID'           => $nukiID,
                                'DeviceType'         => $deviceType,
                                'ProductDesignation' => 'Smart Lock',
                                'name'               => $deviceName,
                                'instanceID'         => $instanceID,
                                'create'             => [
                                    'moduleID'      => NUKI_SMARTLOCK_GUID,
                                    'configuration' => [
                                        'SmartLockUID'  => (string) $nukiID,
                                        'SmartLockName' => (string) $deviceName
                                    ],
                                    'location' => $location
                                ]
                            ];
                            break;

                        //Opener
                        case 2:
                            $instanceID = $this->GetDeviceInstances($nukiID, 2);
                            $this->SendDebug(__FUNCTION__ . ' NUKI Opener ID', json_encode($nukiID), 0);
                            $this->SendDebug(__FUNCTION__ . ' NUKI Opener Instance ID', json_encode($instanceID), 0);
                            $values[] = [
                                'DeviceID'           => $nukiID,
                                'DeviceType'         => $deviceType,
                                'ProductDesignation' => 'Opener',
                                'name'               => $deviceName,
                                'instanceID'         => $instanceID,
                                'create'             => [
                                    'moduleID'      => NUKI_OPENER_GUID,
                                    'configuration' => [
                                        'OpenerUID'  => (string) $nukiID,
                                        'OpenerName' => (string) $deviceName
                                    ],
                                    'location' => $location
                                ]
                            ];
                            break;

                    }
                }
            }
        }
        $formData['actions'][0]['values'] = $values;
        return json_encode($formData);
    }

    #################### Private

    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    private function RegisterProperties()
    {
        $this->RegisterPropertyString('Note', '');
        $this->RegisterPropertyInteger('CategoryID', 0);
    }

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

    private function GetPairedDevices(): string
    {
        if (!$this->HasActiveParent()) {
            return '';
        }
        $data = [];
        $buffer = [];
        $data['DataID'] = NUKI_BRIDGE_DATA_GUID;
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

    private function GetDeviceInstances($DeviceUID, $DeviceType)
    {
        $instanceID = 0;
        switch ($DeviceType) {
            //Smart Lock
            case 0:
                $moduleID = NUKI_SMARTLOCK_GUID;
                $propertyUIDName = 'SmartLockUID';
                break;

            //Opener
            case 2:
                $moduleID = NUKI_OPENER_GUID;
                $propertyUIDName = 'OpenerUID';
                break;

            default:
                $moduleID = NUKI_SMARTLOCK_GUID;
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