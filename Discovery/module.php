<?php

/*
 * @module      NUKI Discovery
 *
 * @file        module.php
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2019
 * @license     CC BY-NC-SA 4.0
 *
 * @version     1.04
 * @build       1007
 * @date        2019-08-07, 10:00
 *
 * @see         https://github.com/ubittner/SymconNUKI
 *
 * @guids		Library
 * 				{752C865A-5290-4DBE-AC30-01C7B1C3312F}
 *
 *              NUKI Discovery
 *              {29B22B4B-2BBE-4A48-AC96-65AB80EC0CD5}
 *
 */

declare(strict_types=1);

class NUKIDiscovery extends IPSModule
{
    public function Create()
    {
        parent::Create();
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    public function GetConfigurationForm()
    {
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $bridges = $this->DiscoverBridges();
        $values = [];
        foreach ($bridges as $bridgeID => $bridge) {
            $instanceID = $this->GetBridgeInstances($bridge['bridgeIP']);
            $addValue = ['BridgeID' => $bridge['bridgeID'], 'BridgeIP' => $bridge['bridgeIP'], 'BridgePort' => $bridge['bridgePort'], 'instanceID' => $instanceID];
            $addValue['create'] = [['moduleID' => '{B41AE29B-39C1-4144-878F-94C0F7EEC725}', 'configuration' => ['BridgeIP' => $bridge['BridgeIP'], 'BridgePort' => $bridge['BridgePort']]]];
            $values[] = $addValue;
        }
        $form['actions'][0]['values'] = $values;
        return json_encode($form);
    }

    /**
     * Discovers NUKI bridges and returns the values as an array.
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
            CURLOPT_HEADER         => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT, 15]);
        $response = curl_exec($ch);
        if ($response) {
            $result = json_decode($response);
            if (property_exists($result, 'code') == 404) {
                return $discoveredBridges;
            }
            if (property_exists($result, 'bridges')) {
                $bridges = $result->bridges;
                foreach ($bridges as $bridge) {
                    if (array_key_exists('bridgeId', $bridge) && array_key_exists('ip', $bridge) && array_key_exists('port', $bridge)) {
                        $discoveredBridges[$bridge->bridgeId][] = ['bridgeID' => $bridge->bridgeId, 'bridgeIP' => $bridge->ip, 'bridgePort' => $bridge->port];
                    }
                }
            }
        }
        return $discoveredBridges;
    }

    /**
     * Gets an existing NUKI Bridge splitter instance and returns the object id.
     *
     * @param string $BridgeIP
     *
     * @return int
     */
    private function GetBridgeInstances(string $BridgeIP): int
    {
        $instances = IPS_GetInstanceListByModuleID('{B41AE29B-39C1-4144-878F-94C0F7EEC725}');
        foreach ($instances as $instance) {
            if (IPS_GetProperty($instance, 'BridgeIP') == $BridgeIP) {
                return $instance;
            }
        }
        return 0;
    }
}
