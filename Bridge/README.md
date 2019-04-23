# NUKI Bridge

[![Version](https://img.shields.io/badge/Symcon_Version-5.1>-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul_Version-1.04-blue.svg)]()
![Version](https://img.shields.io/badge/Modul_Build-1004-blue.svg)
[![Version](https://img.shields.io/badge/Code-PHP-blue.svg)]()
[![Version](https://img.shields.io/badge/API_Version-1.07-yellow.svg)](https://nuki.io/wp-content/uploads/2018/04/20180330-Bridge-API-v1.7.pdf)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://github.styleci.io/repos/71931303/shield?branch=master&style=flat)](https://github.styleci.io/repos/71931303)

![Image](../imgs/nuki-logo-black.png)

Dieses Modul integriert die [NUKI Bridge](https://nuki.io/de/bridge/) in [IP-Symcon](https://www.symcon.de).

Für dieses Modul besteht kein Anspruch auf Fehlerfreiheit, Weiterentwicklung, sonstige Unterstützung oder Support.

Bevor das Modul installiert wird, sollte unbedingt ein Backup von IP-Symcon durchgeführt werden.

Der Entwickler haftet nicht für eventuell auftretende Datenverluste oder sonstige Schäden.

Der Nutzer stimmt den o.a. Bedingungen, sowie den Lizenzbedingungen ausdrücklich zu.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)
8. [Bridge Callback Simulation](#8-bridge-callback-simulation)
9. [GUIDs](#9-guids)
10. [Changelog](#10-changelog)

### 1. Funktionsumfang

* Ermitteln und anlegen der verfügbaren Smart Locks
* Empfang von Statusinformationen der Samrt Locks via Webhook
* Öffnen und schließen des Smart Locks 

### 2. Voraussetzungen

- IP-Symcon ab Version 5.1
- NUKI Bridge
- NUKI Smart Lock

Hier finden Sie die [Dokumentation](../SmartLock) zum NUKI Smart Lock.  

### 3. Software-Installation

- Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Autor.
  
- Bei privater Nutzung wird das Modul über den Modul Store installiert.

### 4. Einrichten der Instanzen in IP-Symcon

- In IP-Symcon an beliebiger Stelle `Instanz hinzufügen` auswählen und `NUKI Bridge` auswählen, welches unter dem Hersteller `NUKI` aufgeführt ist. Es wird eine NUKI Bridge Instanz angelegt, in der die Eigenschaften zur Steuerung des NUKI Bridge gesetzt werden können.

__Konfigurationsseite__:

Name                                | Beschreibung
----------------------------------- | ---------------------------------
(0) Instanzinformationen            | Informationen zu der Instanz
(1) Bridge                          | Eigenschaften der NUKI Bridge
(2) Smart Lock                      | Kategorie für die Smart Locks
(3) Callback Socket                 | Eigenschaften zum Callback Socket

__Schaltflächen__:

Name                                | Beschreibung
----------------------------------- | ---------------------------------
(0) Instanzinformationen            |
Bedienungsanleitung                 | Zeigt Informationen zu diesem Modul an
(1) Bridge                          | 
Suchen                              | Sucht die Bridge im Netzwerk und zeigt Informationen zur Bridge an
Info anzeigen                       | Zeigt weitere Informationen der Bridge an
Logdatei anzeigen                   | Zeigt die Logdatei der Bridge an
Logdatei löschen                    | Löscht die Logdatei der Bridge
Update Firmware                     | Führt eine aktualisierung der Firmware durch
Neustart                            | Starte die Bridge neu
Werkseinstellungen                  | Setzt die Brige zurück in die Werkseinstellungen
(2) Smart Locks                     | 
Anzeigen                            | Zeigt die verfügbaren Smart Locks der Bridge an
Abgleichen                          | Legt die Smart Locks automatisch in IP-Symcon an
(3) Callback Socket                 | 
Anlegen                             | Legt den Callback an
Anzeigen                            | Zeigt die angelegten Callbacks an
Löschen                             | Löscht den Callback mit der definierten ID

__Vorgehensweise__:

Geben Sie die IP-Adresse, den Port und den API Token der NUKI Bridge an. 
Bei der Ersteinrichtung der NUKI Bridge mittels der Nuki iOS / Android App auf dem Smartphone werden Ihnen die Daten angezeigt. 
Über das Konfigurationsfeld `Kategorie` können Sie festlegen, in welche Kategorie die Smart Locks angelegt werden sollen. Es kann auch die Hauptkategorie genutzt werden.  
Übernehmen Sie die Änderungen und drücken Sie unter Punkt (2) Smart Locks die Schaltfläche `ABGLEICHEN`.  
Die Smart Locks werden dann automatisch angelegt.

__Callback__:

Für die Aktualisierung von Informationen der Smart Locks wird ein Callback genutzt.  
Geben Sie unter Punkt (3) Callback Sockets die IP-Adresse des IP-Symcon Servers ein und einen freien Port.  
Übernehmen Sie die Änderungen und drücken Sie anschließend unter Punkt (3) Callback Sockets die Schaltfläche `ANLEGEN`.  
Der Callback wird automatisch auf der NUKI Bridge eingetragen und in IP-Symcon wird autmatisch der entsprschende NUKI Socket (Server Socket) angelegt, sofern die Option `Callback benutzen` aktiviert wurde.  
Über die Schaltfläche `ANZEIGEN` unter Punkt (3) Callback Socket werden die registrierten Callbacks angezeigt.
Unter Punkt (3) Callback Socket kann mittels der Schaltfläche `LÖSCHEN` der entsprechende Callback (definierte Callback ID aus der Instantkonfiguration) von der NUKI Bridge wieder gelöscht werden.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es werden keine Statusvariablen angelegt.

##### Profile:

Nachfolgende Profile werden zusätzlichen hinzugefügt:

Es werden keine neuen Profile angelegt.

### 6. WebFront

Die NUKI Bridge ist im WebFront nicht nutzbar.  

### 7. PHP-Befehlsreferenz

`NUKI_DiscoverBridges(integer $BridgeInstanceID);`  

Sucht im Netzwerk nach vorhandenen NUKI Bridges. 
Gibt als Rückwert die gefundenen Bridges / Informationen aus.

`NUNKI_EnableAPI(integer $BridgeInstanceID);`

Aktiviert die API Funktion, sofern über die Nuki iOS / Android App die Nutzung der API Funktion noch nicht aktiviert wurde.
Es muss innerhalb von 30 Sekunden der Knopf auf der NUKI Bridge zur Authentifizierung gedrückt werden.
Gibt als Rückwert den Zustand und den API Token an.

`NUKI_ToggleConfigAuth(integer $BridgeInstanceID, bool $Enable)`

Aktiviert oder deaktiviert die Autorisierung über `NUKI_EnableAPI(integer $InstanzID)` und die Veröffentlichung der lokalen IP und des Ports zur Discovery URL.
Gibt als Rückgabewert des Status aus.

`NUKI_GetSmartLocks(integer $BridgeInstanceID)`

Gibt als Rückwert eine Liste aller verfügbaren smarten NUKI Türschlösser aus.

`NUKI_GetLockStateOfSmartLock(integer $BridgeInstanceID, integer $SmartLockUniqueID)`

Zeigt den aktuellen Status eines samrten NUKI Türschlosses an.

`NUKI_SetLockActionOfSmartLock(integer $BridgeInstanceID, integer $SmarLockUniqueID, integer $LockAction)`

Führt eine Aktion für ein smartes NUKI Türschloss gemäss LockAction Tabelle aus:

Wert | Bezeichnung
-----|----------------------------------
1    | unlock 
2    | lock 
3    | unlatch 
4    | lock ‘n’ go 
5    | lock ‘n’ go with unlatch

`NUKI_UnpairSmartLockFromBridge(integer $BridgeInstanceID, integer $SmarLockUniqueID)`

Löscht ein smartes NUKI Türschloss von der Bridge.

`NUKI_GetBridgeInfo(integer $BridgeInstanceID)`

Zeigt alle smarten NUKI Türschlösser in der Nähe an und liefert Informationen zur Bridge.

`NUKI_AddCallback(integer $BridgeInstanceID)`

Legt einen Callback auf der Bridge an.

`NUKI_ListCallback(integer $BridgeInstanceID)`

Zeigt die angelegten Callbacks auf der Bridge an.

`NUKI_DeleteCallback(integer $BridgeInstanceID, integer $CallbackID)`

Löscht den Callback mit der $CallbackID auf der Bridge.

`NUKI_GetBridgeLog(integer $BridgeInstanceID)`

Zeigt das Log der Bridge an.

`NUKI_ClearBridgeLog(integer $BridgeInstanceID)`

Löscht das Log der Bridge.

`NUKI_UpdateBridgeFirmware(integer $BridgeInstanceID)`

Prüft auf ein neues Firmware Update der Bridge und installiert es.

`NUKI_RebootBridge(integer $BridgeInstanceID)`

Starte die Brdige neu.

`NUKI_FactoryResetBridge(integer $BridgeInstanceID)`

Setzt die Bridge auf Werkseinstellungen.

`NUKI_SyncSmartLocks(integer $BridgeInstanceID)`

Gleicht alle smarten NUKI Türschlösser der Bridge ab und legt diese in IP-Symcon automatisch an.

`NUKI_UpdateStateOfSmartLocks(integer $BridgeInstanceID, bool $ProtocolMode)`

Aktualisiert den Status aller smarten NUKI Türschlösser.

### 8. Bridge Callback Simulation

Mit einem curl Befehl kann der Callback einer NUKI Bridge im Rahmen einer Entwicklungsumgebung simuliert werden. Für den normalen Gebrauch oder Einsatz der NUKI Bridge ist der curl Befehl nicht notwendig.  
Für die Verwendung von curl über die Konsole des entsprechenden Betriebssystems informieren Sie sich bitte im Internet.  
```Text
curl -v -A "NukiBridge_12345678" -H "Connection: Close" -H "Content-Type: application/json;charset=utf-8" -X POST -d '{"nukiId": 987654321, "state": 1, "stateName": "locked", "batteryCritical": false}' http://127.0.0.1:8081
```  

* `NukiBridge_12345678` ist die ID der NUKI Bridge  
* `nukiId: 987654321` ist die ID des NUKI Smartlocks  
* `http://127.0.0.1:8081` ist die IP-Adresse und Port des Server Sockets

### 9. GUIDs

Bezeichnung                                 | GUID
--------------------------------------------| --------------------------------------
Bibliothek                                  | {752C865A-5290-4DBE-AC30-01C7B1C3312F}      
Virtual I/O (Server Socket NUKI Callback)   |
CR: IO_RX                                   | {018EF6B5-AB94-40C6-AA53-46943E824ACF}
I: IO_TX                                    | {79827379-F36E-4ADA-8A95-5F8D1DC92FA9}     
Spliter (NUKI Bridge)                       |
Module GUID                                 | {B41AE29B-39C1-4144-878F-94C0F7EEC725}      
PR: IO_TX                                   | {79827379-F36E-4ADA-8A95-5F8D1DC92FA9}      
CR: Device_RX                               | {3DED8598-AA95-4EC4-BB5D-5226ECD8405C}      
I: IO_RX                                    | {018EF6B5-AB94-40C6-AA53-46943E824ACF}     
I: Device_TX                                | {73188E44-8BBA-4EBF-8BAD-40201B8866B9}      
Device (NUKI Smartlock)                     |
Module GUID                                 | {37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14}      
PR: Device_TX                               | {73188E44-8BBA-4EBF-8BAD-40201B8866B9}      
I: Device_RX                                | {3DED8598-AA95-4EC4-BB5D-5226ECD8405C}      

### 10. Changelog

Version     | Datum      | Beschreibung
----------- | -----------| -------------------
1.04-1004   | 21.04.2019 | Version für Module-Store
1.03        | 27.04.2018 | Update auf API 1.7
1.02        | 19.04.2017 | Update auf API 1.5
1.01        | 31.01.2017 | Erweiterung von Funktionen
1.00        | 01.11.2016 | Modulerstellung

