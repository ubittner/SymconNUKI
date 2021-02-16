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

class NUKIDiscovery extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
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
        // Never delete this line!
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
        $bridges = $this->DiscoverBridges();
        if (empty($bridges)) {
            $formData['actions'][] = [
                'type'  => 'PopupAlert',
                'popup' => [
                    'items' => [[
                        'type'    => 'Label',
                        'caption' => 'No bridge found! Please enable the HTTP API function of the bridge in the NUKI app (iOS / Android).'
                    ]]
                ]
            ];
        } else {
            $values = [];
            foreach ($bridges as $bridgeID => $bridge) {
                $instanceID = $this->GetBridgeInstances($bridge['bridgeIP']);
                $addValue = ['BridgeID' => $bridge['bridgeID'], 'BridgeIP' => $bridge['bridgeIP'], 'BridgePort' => $bridge['bridgePort'], 'instanceID' => $instanceID];
                $addValue['create'] = [['moduleID' => NUKI_BRIDGE_GUID, 'configuration' => ['BridgeIP' => (string) $bridge['bridgeIP'], 'BridgePort' => (int) $bridge['bridgePort'], 'BridgeID' => (string) $bridge['bridgeID']]]];
                $values[] = $addValue;
            }
            $formData['actions'][1]['values'] = $values;
        }
        return json_encode($formData);
    }

    /**
     * Discovers the NUKI bridges and returns the values as an array.
     *
     * @return array
     */
    public function DiscoverBridges(): array
    {
        /*
         * Calling the URL https://api.nuki.io/discover/bridges
         * returns a JSON array with all bridges which have been connected to the Nuki Servers through the same IP address
         * than the one calling the URL within the last 30 days.
         * The array contains the local IP address, port, the ID of each bridge and the date of the last change of the entry in the JSON array.
         */
        $discoveredBridges = [];
        $endpoint = 'https://api.nuki.io/discover/bridges';
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $endpoint,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT, 15]);
        $response = curl_exec($ch);
        if ($response) {
            $result = json_decode($response);
            $this->LogMessage($response, 10206);
            $this->SendDebug('DiscoverBridges', $response, 0);
            if (property_exists($result, 'code') == 404) {
                return $discoveredBridges;
            }
            if (property_exists($result, 'bridges')) {
                $bridges = $result->bridges;
                foreach ($bridges as $bridge) {
                    if (property_exists($bridge, 'bridgeId') && property_exists($bridge, 'ip') && property_exists($bridge, 'port')) {
                        $discoveredBridges[$bridge->bridgeId] = ['bridgeID' => $bridge->bridgeId, 'bridgeIP' => $bridge->ip, 'bridgePort' => $bridge->port];
                    }
                }
            }
        }
        return $discoveredBridges;
    }

    #################### Private

    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    private function GetBridgeInstances(string $BridgeIP): int
    {
        $instances = IPS_GetInstanceListByModuleID(NUKI_BRIDGE_GUID);
        foreach ($instances as $instance) {
            if (IPS_GetProperty($instance, 'BridgeIP') == $BridgeIP) {
                return $instance;
            }
        }
        return 0;
    }
}