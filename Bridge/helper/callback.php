<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

trait NUKI_callback
{
    public function ManageCallback(): void
    {
        $useCallback = $this->ReadPropertyBoolean('UseCallback');
        if ($useCallback) {
            $this->RegisterHook('/hook/nuki/bridge/' . $this->InstanceID);
        } else {
            $this->UnregisterHook('/hook/nuki/bridge/' . $this->InstanceID);
        }
        $host = (string) $this->ReadPropertyString('SocketIP');
        $port = (string) $this->ReadPropertyInteger('SocketPort');
        $existingCallbacks = json_decode($this->ListCallback(), true);
        $this->SendDebug(__FUNCTION__, json_encode($existingCallbacks), 0);
        $add = true;
        if (!empty($existingCallbacks)) {
            if (array_key_exists('callbacks', $existingCallbacks)) {
                $callbacks = $existingCallbacks['callbacks'];
                foreach ($callbacks as $callback) {
                    if (array_key_exists('id', $callback) && array_key_exists('url', $callback)) {
                        $id = (int) $callback['id'];
                        $url = (string) $callback['url'];
                        if (!$useCallback) {
                            $add = false;
                            if (strpos($url, $host) !== false) {
                                $this->DeleteCallback($id);
                            }
                        }
                        if ($useCallback) {
                            if (strpos($url, $host) !== false) {
                                if (strpos($url, ':' . $port) === false) {
                                    $this->DeleteCallback($id);
                                }
                                if (strpos($url, ':' . $port) !== false) {
                                    $add = false;
                                }
                            }
                        }
                    }
                }
            }
        }
        if (isset($add)) {
            if ($useCallback && $add) {
                $this->AddCallback();
            }
        }
    }
}
