# NUKI Smart Lock

[![Version](https://img.shields.io/badge/Symcon_Version-5.1>-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul_Version-1.04-blue.svg)]()
![Version](https://img.shields.io/badge/Modul_Build-1004-blue.svg)
[![Version](https://img.shields.io/badge/Code-PHP-blue.svg)]()
[![Version](https://img.shields.io/badge/API_Version-1.07-yellow.svg)](https://nuki.io/wp-content/uploads/2018/04/20180330-Bridge-API-v1.7.pdf)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://github.styleci.io/repos/71931303/shield?branch=master&style=flat)](https://github.styleci.io/repos/71931303)

![Image](../imgs/nuki-logo-black.png)

Dieses Modul integriert das [NUKI Smart Lock](https://nuki.io/de/smart-lock/) in [IP-Symcon](https://www.symcon.de).

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
8. [GUIDs](#8-guids)
9. [Changelog](#9-changelog)

### 1. Funktionsumfang

* Öffnen und Schließen des Smart Locks
* Anzeige von Statusinformationen
* Protokollierung der Schließvorgänge

### 2. Voraussetzungen

- IP-Symcon ab Version 5.1
- NUKI Bridge
- NUKI Smart Lock

### 3. Software-Installation

- Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Autor.
  
- Bei privater Nutzung wird das Modul über den Modul Store installiert.

- Sofern noch keine NUKI Bridge Instanz in IP-Symcon vorhanden ist, so beginnen Sie mit der Installation und Konfiguration der NUKI Bridge.  
Hier finden Sie die [Dokumentation](../Bridge) zur NUKI Bridge.  

### 4. Einrichten der Instanzen in IP-Symcon

- In IP-Symcon an beliebiger Stelle `Instanz hinzufügen` auswählen und `NUKI Bridge` auswählen, welches unter dem Hersteller `NUKI` aufgeführt ist. Es wird eine NUKI Bridge Instanz angelegt, in der die Eigenschaften zur Steuerung des NUKI Bridge gesetzt werden können.

__Konfigurationsseite__:

Name                                | Beschreibung
----------------------------------- | ---------------------------------
(0) Instanzinformationen            | Informationen zu der Instanz.
(1) Smart Lock                      | Eigenschaften des Smart Locks.
(2) Schaltvorgänge                  | Definieren der Schaltvorgänge.
(3) Protokoll                       | Eigenschaften zum Protokoll.

__Schaltflächen__:

Name                                | Beschreibung
----------------------------------- | ---------------------------------
(0) Instanzinformationen            |
Bedienungsanleitung                 | Zeigt Informationen zu diesem Modul an.
(1) Smart Lock                      | 
Status anzeigen                     | Zeigt den Status des Smart Locks an.
Unpair                              | Entfernt das Smart Lock von der Bridge.

__Vorgehensweise__:

Das manuelle Anlegen einer Smart Lock Instanz ist nicht zwingend erforderlich. Über die Instanzkonfiguration der NUKI Bridge Instanz können die Smart Locks automatisch angelegt werden.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name                    | Typ       | Beschreibung
----------------------- | --------- | ----------------
SmartLockSwitch         | Boolean   | Schalter zum ver- und entriegeln des Türschlosses.
SmartLockStatus         | String    | Zeigt den Status des Türschlosses an.
SmartLockBatteryState   | Boolean   | Zeigt den Batteriezustand des Türschlosses an.
Protocol                | String    | Zeigt die letzten Protokolleinträge an.

##### Profile:

Nachfolgende Profile werden zusätzlichen hinzugefügt:

NUKI.SmartLockSwitch

Werden alle NUKI SmartLock Instanzen gelöscht, so werden automatisch die oben aufgeführten Profile gelöscht.

### 6. WebFront

Über das WebFront kann das Smart Lock ver- oder entriegelt werden.
Weiherhin werden Statusinformationen über das Smart Lock und ein Protokoll angezeigt.
 
### 7. PHP-Befehlsreferenz

`NUKI_ShowLockStateOfSmartLock(integer $SmartLockInstanceID)`

Zeigt den Status eines smarten NUKI Türschlosses an.

`NUKI_ToggleSmartLock(integer $SmartLockInstanceID, bool $State)`

Öffnet `true` oder Schließt `false` das Smart Lock.

`NUKI_UnpairSmartLock(integer $SmartLockInstanceID, bool $State)`

Löscht das Smart Lock von der Bridge.

### 8. GUIDs

*       Bibliothek
        {752C865A-5290-4DBE-AC30-01C7B1C3312F}

*       Virtual I/O (Server Socket NUKI Callback)
        {018EF6B5-AB94-40C6-AA53-46943E824ACF} (CR: IO_RX)
        {79827379-F36E-4ADA-8A95-5F8D1DC92FA9} (I: IO_TX)

*       Spliter (NUKI Bridge)
        {B41AE29B-39C1-4144-878F-94C0F7EEC725} (Module GUID)
        {79827379-F36E-4ADA-8A95-5F8D1DC92FA9} (PR: IO_TX)
        {3DED8598-AA95-4EC4-BB5D-5226ECD8405C} (CR: Device_RX)
        {018EF6B5-AB94-40C6-AA53-46943E824ACF} (I: IO_RX)
        {73188E44-8BBA-4EBF-8BAD-40201B8866B9} (I: Device_TX)

*       Device (NUKI Smartlock)
        {37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14} (Module GUID)
        {73188E44-8BBA-4EBF-8BAD-40201B8866B9} (PR: Device_TX)
        {3DED8598-AA95-4EC4-BB5D-5226ECD8405C} (I: Device_RX)

### 9. Changelog

Version     | Datum      | Beschreibung
----------- | -----------| -------------------
1.04-1004   | 21.04.2019 | Version für Module-Store
1.03        | 27.04.2018 | Update auf API 1.7
1.02        | 19.04.2017 | Update auf API 1.5
1.01        | 31.01.2017 | Erweiterung von Funktionen
1.00        | 01.11.2016 | Modulerstellung