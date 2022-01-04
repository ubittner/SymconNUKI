<?php

/*
 * @author      Ulrich Bittner
 * @copyright   (c) 2020, 2021
 * @license    	CC BY-NC-SA 4.0
 * @see         https://github.com/ubittner/SymconNUKI
 */

/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

declare(strict_types=1);

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
        $pairedDevices = json_decode($this->GetPairedDevices(), true);
        $this->SendDebug(__FUNCTION__ . ' Paired Devices', json_encode($pairedDevices), 0);
        $values = [];
        $location = $this->GetCategoryPath($this->ReadPropertyInteger(('CategoryID')));
        //Paired devices
        if (!empty($pairedDevices)) {
            foreach ($pairedDevices as $device) {
                if (array_key_exists('deviceType', $device)) {
                    $deviceType = $device['deviceType'];
                    $deviceName = $device['name'];
                    $nukiID = $device['nukiId'];
                    switch ($deviceType) {
                        //Smart Lock
                        case 0: //Nuki Smart Lock 1.0/2.0
                        case 3: //Nuki Smart Door
                        case 4: //Nuki Smart Lock 3.0 (Pro)
                            switch ($deviceType) {
                                case 0:
                                    $productDesignation = 'Smart Lock 1.0/2.0';
                                    break;

                                case 3:
                                    $productDesignation = 'Smart Door ';
                                    break;

                                case 4:
                                    $productDesignation = 'Smart Lock 3.0 (Pro)';
                                    break;

                                default:
                                    $productDesignation = 'Unknown';
                            }
                            $instanceID = $this->GetDeviceInstances($nukiID, 0);
                            $this->SendDebug(__FUNCTION__ . ' NUKI Smart Lock ID', json_encode($nukiID), 0);
                            $this->SendDebug(__FUNCTION__ . ' NUKI Smart Lock Instance ID', json_encode($instanceID), 0);
                            $values[] = [
                                'DeviceID'           => $nukiID,
                                'DeviceType'         => $deviceType,
                                'ProductDesignation' => $productDesignation,
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
                        case 2: //Nuki Opener
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
            //Opener
            case 2:
                $moduleID = NUKI_OPENER_GUID;
                $propertyUIDName = 'OpenerUID';
                break;

            //Smart Lock
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