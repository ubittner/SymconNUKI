# NUKI Bridge

[![Image](../imgs/NUKI_Logo.png)](https://nuki.io/de/)  

[![Image](../imgs/NUKI_Bridge.png)]()  

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

### 1. Funktionsumfang

* Empfang von Statusinformationen der NUKI Smart Locks via Webhook
* Auf- und Zusperren der NUKI Smart Locks 

### 2. Voraussetzungen

- IP-Symcon ab Version 5.1
- NUKI Bridge
- NUKI Smart Lock  

### 3. Software-Installation

- Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Autor.
  
- Bei privater Nutzung wird das Modul über den Modul Store installiert.

- Sofern noch keine NUKI Bridge Instanz in IP-Symcon vorhanden ist, so beginnen Sie mit der Installation der NUKI Discovery Instanz.  
Hier finden Sie die [Dokumentation](../Discovery) zum NUKI Discovery.  
Alternativ können Sie die NUKI Bridge auch manuell anlegen. Lesen Sie bitte dafür diese Dokumentation weiter durch.

### 4. Einrichten der Instanzen in IP-Symcon

- In IP-Symcon an beliebiger Stelle `Instanz hinzufügen` auswählen und `NUKI Bridge` auswählen, welches unter dem Hersteller `NUKI` aufgeführt ist. Es wird eine NUKI Bridge Instanz angelegt, in der die Eigenschaften zur Steuerung des NUKI Bridge gesetzt werden können.

__Konfigurationsseite__:

Name                                | Beschreibung
----------------------------------- | ---------------------------------
(0) Instanzinformationen            | Informationen zu der NUKI Bridge Instanz
(1) Bridge                          | Eigenschaften der NUKI Bridge
(2) Callback                        | Eigenschaften zum Callback der NUKI Bridge

__Schaltflächen im Aktionsbereich__:

Name                                | Beschreibung
----------------------------------- | ---------------------------------
(1) Bridge                          | 
Info anzeigen                       | Zeigt weitere Informationen der NUKI Bridge an
Logdatei anzeigen                   | Zeigt die Logdatei der NUKI Bridge an
Logdatei löschen                    | Löscht die Logdatei der NUKI Bridge
Firmware aktualisieren              | Führt eine aktualisierung der Firmware durch
Neustart                            | Starte die NUKI Bridge neu
Werkseinstellungen                  | Setzt die NUKI Brige zurück in die Werkseinstellungen
Smart Locks anzeigen                | Zeigt die verfügbaren NUKI Smart Locks der NUKI Bridge an
(2) Callback Socket                 | 
Anlegen                             | Legt einen Callback auf der NUKI Bridge an
Anzeigen                            | Zeigt die angelegten Callbacks der NUKI Bridge an
Löschen                             | Löscht den Callback mit der definierten ID von der NUKI Bridge
Bedienungsanleitung                 | Zeigt Informationen zu diesem Modul an  

__Vorgehensweise__:

Geben Sie die IP-Adresse, den Port, den Netzwerk-Timeout, die Bridge ID und den API Token der NUKI Bridge an. 
Bei der Ersteinrichtung der NUKI Bridge mittels der Nuki iOS / Android App auf dem Smartphone wurden Ihnen die Daten angezeigt. 
Mit der Konfigurator Instanz `NUKI Configurator` können Sie die Smart Locks automatisch anlegen lassen.

__Callback__:

Für die Aktualisierung von Informationen der NUKI Smart Locks wird ein Callback genutzt.  
Geben Sie unter Punkt (3) Callback die IP-Adresse des IP-Symcon Servers ein und den Server-Socket Port.  
Übernehmen Sie eventuelle Änderungen und drücken Sie anschließend im Aktionsbereich unter Punkt (2) Callback die Schaltfläche `ANLEGEN`.  
Der Callback wird automatisch auf der NUKI Bridge eingetragen und in IP-Symcon wird autmatisch der entsprschende NUKI Socket (Server Socket) angelegt, sofern die Option `Callback benutzen` aktiviert wurde und kein bereits vorhandener Server-Socket existiert.  
Über die Schaltfläche `ANZEIGEN` unter Punkt (2) Callback im Aktionsbereich werden die registrierten Callbacks angezeigt.
Mit der Schaltfläche `LÖSCHEN` im Aktionsbereich unter Punkt (2) Callback kann mittels der definierte Callback ID aus der Instantkonfiguration) der Callback von der NUKI Bridge gelöscht werden.

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

```text
API aktivieren:  

NUKI_EnableAPI(integer $InstanzID);  
Aktiviert die API Funktion, sofern über die Nuki iOS / Android App die Nutzung der API Funktion noch nicht aktiviert wurde.  
Es muss innerhalb von 30 Sekunden der Knopf auf der NUKI Bridge zur Authentifizierung gedrückt werden.  
Gibt als Rückwert den Zustand und den API Token an.  
  
$InstanzID:     Instanz ID der NUKI Bridge  

Beispiel:
$enable = NUKI_EnableAPI(12345);
```  

```text
Authorisierung de-/aktivieren:  

NUKI_ToggleConfigAuth(integer $InstanzID, bool $Status);  
Aktiviert oder deaktiviert die Authorisierung über `NUKI_EnableAPI(integer $InstanzID)` und die Veröffentlichung der lokalen IP-Adresse und des Ports zur Discovery URL.  
Gibt als Rückgabewert des Status aus.  

$InstanzID:     Instanz ID der NUKI Bridge
$Status:        false = deaktivieren, true = aktivieren  

Beispiel:  
Deaktivieren:   $deactivate = NUKI_ToggleConfigAuth(12345, false); 
Aktivieren:     $activate = NUKI_ToggleConfigAuth(12345, true); 
```  

```text
Gekoppelte Geräte (Smart Lock / Opener) anzeigen:  

NUKI_GetPairedDevices(integer InstanzID);
Gibt als Rückwert eine Liste aller verfügbaren NUKI Geräte (Smart Locks / Opener) aus.  

Beispiel:
$devices = NUKI_GetPairedDevices(12345);  

Nachfolgende Methode ist noch verfügbar, wird aber abgekündigt und zukünftig nicht mehr unterstützt:  
NUKI_GetSmartLocks(integer $InstanzID);  
````  

```text
Status eines Gerätes (Smart Locks / Opener) ermitteln:  

NUKI_GetLockState(integer InstanzID, integer $NukiID, int $DeviceType);  
Gibt den aktuellen Status eines NUKI Gerätes (Smart Lock / Opener) zurück.  

$InstanzID:     Instanz ID der NUKI Bridge  
$NukiID:        UID des Gerätes  
$DeviceType:    0 = Smart Lock, 2 = Opener  

Beispiel:  
$state = NUKI_GetLockState(12345, 987654321, 0);  

Nachfolgende Methode ist noch verfügbar, wird aber abgekündigt und zukünftig nicht mehr unterstützt:  
NUKI_GetLockStateOfSmartLock(integer $InstanzID, integer $SmartLockUniqueID);
````

```text
Gerät (Smart Lock / Opener) schalten:  

NUKI_SetLockAction(integer $InstanzID, integer $NukiID, int $LockAction, int $DeviceType);  
Achtung: Es muss die NUKI ID angegeben werden und nicht die Instanz ID des NUKI Gerätes!  

$InstanzID:     Instanz ID der NUKI Bridge
$NukiID:        UID des NUKI Gerätes
$DeviceType:    0 = Smart Lock, 2 = Opener  

$LockAction:  
Führt eine Aktion für das NUKI Gerät gemäss Tabelle aus:  

Wert | Smart Lock                   | Opener
-----|------------------------------|---------------------------
1    | unlock                       | activate rto
2    | lock                         | deactivate rto
3    | unlatch                      | electric strike actuation
4    | lock ‘n’ go                  | activate continuous mode
5    | lock ‘n’ go with unlatch     | deactivate continuous mode
  
Beispiel:  
Smart Lock zusperren:   NUKI_SetLockAction(12345, 987654321, 2, 0);  
Smart Lock aufsperren:  NUKI_SetLockAction(12345, 987654321, 1, 0);  
Türsummer betätigen:    NUKI_SetLockAction(12345, 987654321, 3, 2);  

Nachfolgende Methode ist noch verfügbar, wird aber abgekündigt und zukünftig nicht mehr unterstützt:  
NUKI_SetLockActionOfSmartLock(integer $BridgeInstanceID, integer $SmarLockUniqueID, integer $LockAction);  
```

```text
Gerät entkoppeln:  

UnpairDevice(integer $InstanzID, integer $NukiID, integer $DeviceType);
Entkoppelt ein NUKI Gerät (Smart Lock / Opener) von der NUKI Bridge.

$InstanzID:     Instanz ID der NUKI Bridge
$NukiID:        UID des NUKI Gerätes
$DeviceType:    0 = Smart Lock, 2 = Opener  

Beispiel:  
$unpair = NUKI_UnpairDevice(12345, 987654321, 0);  

Nachfolgende Methode ist noch verfügbar, wird aber abgekündigt und zukünftig nicht mehr unterstützt:  
NUKI_UnpairSmartLockFromBridge(integer $BridgeInstanceID, integer $SmarLockUniqueID)
```

```text
Bridge Informationen:  

NUKI_GetBridgeInfo(integer $InstanzID);    
Zeigt alle NUKI Geräte (Smart Locks / Opener) in der Nähe an und liefert Informationen zur NUKI Bridge.  

$InstanzID:     Instanz ID der NUKI Bridge  

Beispiel:  
$info = NUKI_GetBridgeInfo(12345);  
```  

```text
Callback anlegen:  

NUKI_AddCallback(integer $InstanzID);    
Legt einen Callback auf der NUKI Bridge an.  

$InstanzID:     Instanz ID der NUKI Bridge  

Beispiel:  
$add = NUKI_AddCallback(12345);
```

```text
Callback anzeigen:  

NUKI_ListCallback(integer $InstanzID); 
Zeigt die angelegten Callbacks auf der Bridge an.  

$InstanzID:     Instanz ID der NUKI Bridge  

Beispiel:  
$add = NUKI_ListCallback(12345);
```  

```text
Callback löschen:  

NUKI_DeleteCallback(integer $InstanzID, integer $CallbackID);    
Löscht den Callback mit der $CallbackID auf der Bridge.  

$InstanzID:     Instanz ID der NUKI Bridge
$CallbackID:    ID des Callbacks  

Beispiel:  
$delete = NUKI_DeleteCallback(12345, 0);
```

```text
Log anzeigen:  

NUKI_GetBridgeLog(integer $InstanzID);    
Zeigt das Log der NUKI Bridge an.  

$InstanzID:     Instanz ID der NUKI Bridge

Beispiel:  
$log = NUKI_GetBridgeLog(12345);
```  

```text
Log löschen:  

NUKI_ClearBridgeLog(integer $InstanzID);    
Löscht das Log der Bridge.  

$InstanzID:     Instanz ID der NUKI Bridge

Beispiel:  
$clear = NUKI_ClearBridgeLog(12345);
```  

```text
Firmware aktualisieren:  

NUKI_UpdateBridgeFirmware(integer $InstanzID);    
Prüft auf ein neues Firmware Update der Bridge und installiert es.

$InstanzID:     Instanz ID der NUKI Bridge

Beispiel:  
$update = NUKI_UpdateBridgeFirmware(12345);
```  

```text
NUKI Bridge neu starten:  

NUKI_RebootBridge(integer $InstanzID);    
Starte die NUKI Bridge neu.  

$InstanzID:     Instanz ID der NUKI Bridge

Beispiel:  
$update = NUKI_RebootBridge(12345);
```  

```text
Werkseinstellungen laden:  

NUKI_FactoryResetBridge(integer $InstanzID);    
Setzt die NUKI Bridge auf Werkseinstellungen.   

$InstanzID:     Instanz ID der NUKI Bridge

Beispiel:  
$update = NUKI_FactoryResetBridge(12345);
```  

### 8. Bridge Callback Simulation

Mit einem curl Befehl kann der Callback einer NUKI Bridge im Rahmen einer Entwicklungsumgebung simuliert werden. Für den normalen Gebrauch oder Einsatz der NUKI Bridge ist der curl Befehl nicht notwendig.  
Für die Verwendung von curl über die Konsole des entsprechenden Betriebssystems informieren Sie sich bitte im Internet.  
```Text
curl -v -A "NukiBridge_12345678" -H "Connection: Close" -H "Content-Type: application/json;charset=utf-8" -X POST -d '{"nukiId": 987654321, "state": 1, "stateName": "locked", "batteryCritical": false}' http://127.0.0.1:8081
```  

* `NukiBridge_12345678` ist die ID der NUKI Bridge  
* `nukiId: 987654321` ist die ID des NUKI Smart Locks  
* `http://127.0.0.1:8081` ist die IP-Adresse und Port des Server Sockets