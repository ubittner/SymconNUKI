
# NUKI Bridge

[![Version](https://img.shields.io/badge/Symcon_Version-5.1>-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul_Version-1.04-blue.svg)]()
![Version](https://img.shields.io/badge/Modul_Build-1004-blue.svg)
[![Version](https://img.shields.io/badge/Code-PHP-blue.svg)]()
[![Version](https://img.shields.io/badge/API_Version-1.07-yellow.svg)](https://nuki.io/wp-content/uploads/2018/04/20180330-Bridge-API-v1.7.pdf)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)

![Image](../imgs/nuki-logo-white.png)

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
8. [GUIDs](#8-guids)
9. [Changelog](#9-changelog)



########################################################### 

# OLD to be edited


##### Hinweis !
In der Version 1.03 wurden gegenüber der Vorversion wesentliche Änderungen vorgenommen.
Der Entwickler empfiehlt vor einem Update auf Version 1.03 alte Versionsstände und das Modul zu löschen.
Hierfür sind die bereits vorhandenen NUKI Instanzen in IP-Symcon zu löschen und anschließend über die [Modulverwaltung](https://www.symcon.de/service/dokumentation/modulreferenz/module-control/) das Modul in IP-Symcon zu entfernen.
Damit die Änderungen wirksam werden muss IP-Symcon einmal neu gestartet werden.
Im Anschluß kann unter Punkt 3 [Software-Installation](#3-software-installation) das Modul wieder neu hinzugefügt werden.

Da nur wenige Konfigurationen notwendig sind, hält sich der Arbeitsaufwand in Grenzen.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Variablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)
8. [Bridge Callback Simulation](#8-bridge-callback-simulation)
9. [GUIDs](#9-guids)
10. [Changelog](#10-changelog)
11. [Lizenz](#11-lizenz)
12. [Author](#12-author)


### 1. Funktionsumfang

- Öffnen / Schließen des NUKI Smart Locks via WebFront-Button oder über Skript-Funktionen.
- Anzeige von Statusinformationen
- Protokollierung der Schließvorgänge

### 2. Voraussetzungen

- IP-Symcon ab Version 5.0

### 3. Software-Installation

Bei kommerzieller Nutzung (z.B. als Errichter oder Integrator) wenden Sie sich bitte an den Autor.

Bei privater Nutzung:

Nachfolgend wird die Installation dieses Moduls anhand der neuen Web-Console der Version 5.0 beschrieben.
Folgende Instanzen stehen dann in IP-Symcon zur Verfügung:

- [x] NUKI Bridge

- [x] NUKI SmartLock

- [x] NUKI Socket

Im Objektbaum von IP-Symcon die Kern-Instanzen aufrufen. Danach die [Modulverwaltung](https://www.symcon.de/service/dokumentation/modulreferenz/module-control/) aufrufen. Sie sehen nun die bereits installierten Module.
Fügen Sie über das `+` Symbol (unten rechts) ein neues Modul hinzu.
Wählen Sie als URL:

`https://github.com/ubittner/SymconNUKI`  

Anschließend klicken Sie auf `OK`, um das SymconNUKI Modul zu installieren.


### 4. Einrichten der Instanzen in IP-Symcon

### NUKI Bridge Instanz
Fangen Sie mit der NUKI Bridge an. Die NUKI Bridge ist eine Splitter Instanz und wird für die Kommunikation mit dem smarten NUKI Türschloss benötigt.

Klicken Sie in der Objektbaumansicht unten links auf das `+` Symbol. Wählen Sie anschließen `Instanz` aus. Geben Sie im Schnellfiler das Wort "NUKI" ein oder wählen den Hersteller "NUKI" aus. Wählen Sie aus der Ihnen angezeigten Liste "NUKI Bridge" aus und klicken Sie anschließend auf `OK`, um die NUKI Bridge Instanz zu installieren. Sie finden die Instanz unter der Rubrik "Splitter Instanzen".

Nach dem die NUKI Bridge Splitter Instanz erstellt wurde, müssen noch Konfigurationsdaten im Instanzeditor eingetragen werden.

Hier zunächst die Übersicht der Konfigurationsfelder.

__Konfigurationsseite__:

Name | Beschreibung
------------------------------- | ---------------------------------------------
(1) NUKI Bridge                 |
IP-Adresse                      | IP-Adresse der NUKI Bridge.<br>Beispiel: 192.168.1.123<br>Die IP-Adresse, kann aus der NUKI iOS / Android App entnommen werden oder<br> über den Button "Suchen" angezeigt werden.
Port                            | Port der NUKI Bridge.<br>Standard: 8080
API-Token                       | API Token, kann aus der NUKI iOS / Android App entnommen und eingetragen werden.
(2) NUKI Türschloss             |
Kategorie                       | Kategorie für die smarten NUKI Türschlösser
(3) NUKI Callback               |
Callback benutzen               | Callback funktion aktivieren / deaktivieren. <br>Falls Schaltvorgänge über die Nuki iOS / Android App erfolgen,<br>benötigt IP-Symcon eine Information über den aktuellen Zustand. Dafür wird ein Callback benötigt. <br>Wird der Callback aktiviert, so wird automatisch ein NUKI Server Socket für die Kommunikation erstellt.
IP-Adresse                      | IP-Adresse des IP-Symcon Servers.
Port                            | Port für den Callback Server Socket. Standard: 8081
CallbackID                      | Nur notwendig, falls ein auf der NUKI Bridge konfigurierter Callback gelöscht werden soll.
Buttons                         |
Button "Suchen"                 | Sucht nach vorhandenen NUKI Bridges im Netzwerk, sofern erlaubt.
Button "Info anzeigen"          | Zeigt Informationen der NUKI Bridge an.
Button "Logdatei anzeigen"      | Zeigt die Logdatei der NUKI Bridge an.
Button "Logdatei löschen"       | Löscht die Logdatei der NUKI Bridge.
Button "Update Firmware"        | Führt ein Firmwareupdate für die NUKI Bridge aus.
Button "Reboot"                 | Führt ein Neustart der NUKI Bridge durch.
Button "Factory Reset"          | Die Werkseinstellungen der NUKI Bridge werden geladen,<br>vorhandenen Konfigurationen werden von der NUKI Bridge gelöscht.
Button "Türschlösser anzeigen"  | Zeigt die mit der NUKI Bridge gekoppelten Türschlösser an.
Button "Türschlösser abgleichen"| Legt die Türschlösser in IP-Symcon an,<br>sofern noch nicht vorhanden.<br>Die Smartlock Instanzen werden unter der angegebenen Kategorie angelegt.       
Button "Callback anlegen"       | Legt einen Callback auf der NUKI Bridge an.
Button "Callback anzeigen"      | Zeigt die vorhandenen Callbacks der NUKI Bridge an.
Button Callback löschen         |  Löscht die Callback ID auf der NUKI Bridge.

Geben Sie  mindestens die IP-Adresse, den Port und den API Token der NUKI Bridge ein. Bei der Ersteinrichtung der NUKI Bridge mittels der Nuki iOS / Android App auf dem Smartphone werden Ihnen die Daten angezeigt. 

Über das Konfigurationsfeld `Kategorie` können Sie festlegen, in welche Kategorie die smarten NUKI Türschlösser installiert / angelegt werden sollen. Es kann auch die Hauptkategorie genutzt werden.

Wenn Sie die Daten eingetragen erscheint unten im Instanzeditor eine Meldung `Die Instanz hat noch ungespeicherte Änderungen`. Klicken Sie auf den Button `Änderungen übernehmen`, um die Konfigurationsdaten zu übernehmen und zu speichern.

Mit dem Button `Türschlösser anzeigen` werden Ihnen alle smarten NUKI Türschlösser die mit der NUKI Bridge verbunden sind angezeigt.

Um die smarten NUKI Türschlösser anzulegen klicken Sie auf `Türschlösser abgleichen`. Es werden nun automatisch alle smarten NUKI Türschlösser angelegt, die mit der NUKI Bridge verbunden sind.

__Callback__:

Für die Aktualisierung der Informationen des smarten NUKI Türschlosses wird ein Callback genutzt.

Geben Sie die IP-Adresse des IP-Symcon Servers ein und den Callback Port, den Sie nutzen möchten. Wenn Sie die Daten eingetragen erscheint unten im Instanzeditor eine Meldung `Die Instanz hat noch ungespeicherte Änderungen`. Klicken Sie auf den Button `Änderungen übernehmen`, um die Konfigurationsdaten zu übernehmen und zu speichern.
Anschließend klicken Sie auf den Button `Callback anlegen`. Der Callback wird automatisch auf der NUKI Bridge eingetragen und in IP-Symcon wird autmatisch der entsprschende NUKI Socket (Server Socket)angelegt, sofern die Option `Callback benutzen` aktiviert wurde!

Über den Button `Callback anzeigen` werden die registrierten Callbacks angezeigt.

Über die Auswahl der `Callback ID`, sowie Übernahme der geänderten Konfigurationsdaten kann anschließend über den Button `Callback löschen` der entsprechende Callback von der NUKI Bridge wieder gelöscht werden.

### NUKI SmartLock Instanz:

Das manuelle Anlegen einer smarten NUKI Türschloss Instanz ist nicht zwingend erforderlich. Über die Konfigurationsseite der NUKI Bridge Instanz kann durch den Button `Türschlösser abgleichen` die smarten NUKI Türschlösser automatisch angelegt werden.

__Konfigurationsseite__:

Name | Beschreibung
------------------------------- | ---------------------------------------------
(1) Allgemeine Einstellungen    |
UID                             | UID des smarten NUKI Türschlosses, wird im Regefall automatisch hinterlegt.
Bezeichnung                     | Hier kann eine Bezeichnung für das smarte NUKI Türschloss vergeben werden.
(2) Schaltvorgänge              |
Schaltvorgang AUS               | Hier kann definiert werden, welche Aktion ausgeführt werden soll, wenn <br> der Schalter im Webfront auf AUS gestellt wird.
Schaltvorgang EIN               | Hier kann definiert werden, welche Aktion ausgeführt werden soll, wenn <br> der Schalter im Webfront auf EIN gestellt wird.
Schalter verbergen              | Aus Sicherheitsgründen kann der Schalter im Webfront deaktiviert werden.
(3) Protokoll                   |
Protokoll aktiviert             | Hier kann ein Protokoll für die Schließvorgänge aktiviert werden.
Anzahl der Einträge             | Die Anzahl der letzten Schließvorgänge.
Button "Status anzeigen"        | Gibt den Status des smarten NUKI Türschlosses aus.
Button "Unpair"                 | Entfernt das smarte NUKI Türschloss von der Bridge.

### 5. Variablen und Profile

##### Variablen:

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Name            | Typ       | Beschreibung
--------------- | --------- | ----------------
NUKI Snart Lock | Boolean   | Schalter zum ver- und entriegeln des Türschlosses.
Status          | String    | Zeigt den Status des Türschlosses an.
Batterie        | Boolean   | Zeigt den Batteriezustand des Türschlosses an.
Protokoll       | String    | Zeigt die letzten Protokolleinträge an.

##### Profile:

Nachfolgende Profile werden zusätzlichen hinzugefügt:

NUKI.SmartLockSwitch

Werden alle NUKI SmartLock Instanzen gelöscht, so werden automatisch die oben aufgeführten Profile gelöscht.

### 6. WebFront

Über das WebFront kann das smarte Türschloss ver- oder entriegelt werden.
Weiherhin werden Statusinformationen über das Türschloss und ein Protokoll angezeigt.

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

`NUKI_ShowLockStateOfSmartLock(integer $SmartLockInstanceID)`

Zeigt den Status eines smarten NUKI Türschlosses an.

### 8. Bridge Callback Simulation

Mit einem curl Befehl kann der Callback einer NUKI Bridge im Rahmen einer Entwicklungsumgebung simuliert werden. Für den normalen Gebrauch oder Einsatz der NUKI Bridge ist der curl Befehl nicht notwendig.

Für die Verwendung von curl über die Konsole des entsprechenden Betriebssystems informieren Sie sich bitte im Internet.

`curl -v -A "NukiBridge_12345678" -H "Connection: Close" -H "Content-Type: application/json;charset=utf-8" -X POST -d '{"nukiId":987654321,"state":1,"stateName":"locked","batteryCritical":true}' http://127.0.0.1:8081`

"NukiBridge_12345678" ist die ID der NUKI Bridge

"nukiId":987654321 ist die ID des NUKI Smartlocks

http://127.0.0.1:8081 ist die IP-Adresse und Port des Server Sockets


### 9. GUIDs

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

### 10. Changelog

Version     | Datum      | Beschreibung
----------- | -----------| -------------------
1.03        | 27.04.2018 | Update auf API 1.7
1.02        | 19.04.2017 | Update auf API 1.5
1.01        | 31.01.2017 | Erweiterung von Funktionen
1.00        | 01.11.2016 | Modulerstellung

### 11. Lizenz

CC BY-NC 4.0

### 12. Author

Ulrich Bittner
