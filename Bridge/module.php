<?php

/*
 * @module      NUKI Bridge
 *
 * @file        module.php
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2019
 * @license     CC BY-NC-SA 4.0
 *
 * @version     1.04
 * @build       1004
 * @date        2019-04-21, 10:00
 *
 * @see         https://github.com/ubittner/SymconNUKI
 *
 * @guids		Library
 * 				{752C865A-5290-4DBE-AC30-01C7B1C3312F}
 *
 *				Virtual I/O (Server Socket NUKI Callback)
 *				{018EF6B5-AB94-40C6-AA53-46943E824ACF} (CR:	IO_RX)
 *				{79827379-F36E-4ADA-8A95-5F8D1DC92FA9} (I: 	IO_TX)
 *
 *				Spliter (NUKI Bridge)
 *				{B41AE29B-39C1-4144-878F-94C0F7EEC725} (Module GUID)
 *
 * 				{79827379-F36E-4ADA-8A95-5F8D1DC92FA9} (PR:	IO_TX)
 *				{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} (CR: Device_RX)
 * 				{018EF6B5-AB94-40C6-AA53-46943E824ACF} (I:	IO_RX)
 *				{73188E44-8BBA-4EBF-8BAD-40201B8866B9} (I:	Device_TX)
 *
 *				Device (NUKI Smartlock)
 *				{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14} (Module GUID)
 *
 * 				{73188E44-8BBA-4EBF-8BAD-40201B8866B9} (PR: Device_TX)
 *				{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} (I: 	Device_RX)
 *
 * @changelog	2019-04-21, 10:00, added changes for module store
 *              2018-04-21, 12:30, rebuild for IP-Symcon 5.0
 * 				2017-04-19, 23:00, update to API Version 1.5 and some improvements
 * 				2017-01-18, 13:00, initial module script version 1.01
 *
 */

// Declare
declare(strict_types=1);

// Include
include_once __DIR__ . '/helper/autoload.php';

class NUKIBridge extends IPSModule
{
    // Helper
    use BridgeAPI;
    use Control;

    public function Create()
    {
        parent::Create();

        // Register properties
        $this->RegisterPropertyString('BridgeIP', '');
        $this->RegisterPropertyInteger('BridgePort', 8080);
        $this->RegisterPropertyString('BridgeAPIToken', '');
        $this->RegisterPropertyInteger('SmartLockCategory', 0);
        $this->RegisterPropertyBoolean('UseCallback', false);
        $this->RegisterPropertyString('SocketIP', '');
        $this->RegisterPropertyInteger('SocketPort', 8081);
        $this->RegisterPropertyInteger('CallbackID', 0);
    }

    public function ApplyChanges()
    {
        // Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);

        parent::ApplyChanges();

        // Check kernel runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }

        $callback = false;
        if ($this->ReadPropertyBoolean('UseCallback')) {
            $callback = true;
            // Server socket
            $this->RequireParent(SERVER_SOCKET_GUID);
        }

        $parentID = $this->GetParent();
        if ($parentID > 0) {
            if (IPS_GetProperty($parentID, 'Port') != $this->ReadPropertyInteger('SocketPort')) {
                IPS_SetProperty($parentID, 'Port', $this->ReadPropertyInteger('SocketPort'));
            }
            IPS_SetName($parentID, 'NUKI Socket');
            IPS_SetProperty($parentID, 'Open', $callback);
            if (IPS_HasChanges($parentID)) {
                IPS_ApplyChanges($parentID);
            }
        }
        // Validate configuration
        $this->ValidateBridgeConfiguration();
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->SendDebug('MessageSink', 'SenderID: ' . $SenderID . ', Message: ' . $Message, 0);
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
        }
    }

    /**
     * Applies changes when the kernel is ready.
     */
    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    /**
     * Receives data from the server socket.
     *
     * @param $JSONString
     *
     * @return bool|void
     */
    public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString);
        $this->SendDebug('ReceiveData', utf8_decode($data->Buffer), 0);
        $data = utf8_decode($data->Buffer);
        preg_match_all('/\\{(.*?)\\}/', $data, $match);
        $smartLockData = json_encode(json_decode(implode($match[0]), true));
        $this->SetStateOfSmartLock($smartLockData, true);
    }

    /**
     * Gets the parent id.
     *
     * @return int
     */
    protected function GetParent(): int
    {
        $connectionID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
        return $connectionID;
    }

    /**
     * Validates the configuration form.
     */
    private function ValidateBridgeConfiguration()
    {
        $this->SetStatus(102);
        // Check callback
        if ($this->ReadPropertyBoolean('UseCallback') == true) {
            if ($this->ReadPropertyString('SocketIP') == '' || $this->ReadPropertyInteger('SocketPort') == '') {
                $this->SetStatus(104);
            }
        }
        // Check bridge data
        if ($this->ReadPropertyString('BridgeIP') == '' || $this->ReadPropertyInteger('BridgePort') == '' || $this->ReadPropertyString('BridgeAPIToken') == '') {
            $this->SetStatus(104);
        } else {
            $reachable = false;
            $timeout = 1000;
            if ($timeout && Sys_Ping($this->ReadPropertyString('BridgeIP'), $timeout) == true) {
                $data = $this->GetBridgeInfo();
                if ($data != false) {
                    $reachable = true;
                }
            }
            if ($reachable == false) {
                $this->SetStatus(201);
            }
        }
    }
}
