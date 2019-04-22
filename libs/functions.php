<?php

//#################### NUKI Bridge

function NUKI_UpdateStateOfSmartLocks(int $bridgeInstanceID, bool $State) {}
function NUKI_UnpairSmartLockFromBridge(int $bridgeInstanceID, string $SmartLockUID) {return '';}
function NUKI_GetLockStateOfSmartLock(int $bridgeInstanceID, string $SmartLockUID) {return '';}
function NUKI_SetLockActionOfSmartLock(int $bridgeInstanceID, string $SmartLockUID, string $Action) {}