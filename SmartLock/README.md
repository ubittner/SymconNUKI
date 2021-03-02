# NUKI Smart Lock

[![Image](../imgs/NUKI_Logo.png)](https://nuki.io/de/)  

[![Image](../imgs/NUKI_SmartLock.png)]()  

Dieses Modul integriert das elektronische Türschloss [NUKI Smart Lock](https://nuki.io/de/smart-lock/) in [IP-Symcon](https://www.symcon.de).  

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

### 1. Funktionsumfang

* Auf- und Zusperren des NUKI Smart Locks
* Anzeige von Statusinformationen

### 2. Voraussetzungen

- IP-Symcon ab Version 5.1
- NUKI Bridge
- NUKI Smart Lock

### 3. Software-Installation

- Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Autor.
  
- Bei privater Nutzung wird das Modul über den Modul Store installiert.

- Sofern noch keine NUKI Bridge Instanz in IP-Symcon vorhanden ist, so beginnen Sie mit der Installation der NUKI Discovery Instanz.  
Hier finden Sie die [Dokumentation](../Discovery) zum NUKI Discovery.  
Alternativ können Sie die NUKI Bridge auch manuell anlegen. Hier finden Sie die [Dokumentation](../Bridge) zur NUKI Bridge.

- Sofern noch keine NUKI Configurator Instanz in IP-Symcon vorhanden ist, so beginnen Sie mit der Installation und Konfiguration der NUKI Configurator Instanz.  
Hier finden Sie die [Dokumentation](../Configurator) zum NUKI Configurator.  
Alternativ könenn Sie das NUKI Smart Lock auch manuell anlegen. Lesen Sie bitte dafür diese Dokumentation weiter durch.

### 4. Einrichten der Instanzen in IP-Symcon

- In IP-Symcon an beliebiger Stelle `Instanz hinzufügen` auswählen und `NUKI Smart Lock` auswählen, welches unter dem Hersteller `NUKI` aufgeführt ist. Es wird eine NUKI Smart Lock Instanz angelegt, in der die Eigenschaften zur Steuerung des NUKI Smart Locks gesetzt werden können.

__Konfigurationsseite__:

Name                    | Beschreibung
------------------------| ---------------------------------
NUKI ID                 | NUKI ID des Smart Locks
Bezeichnung             | Bezeichnung des Smart Locks
Aktion für Zusperren    | Aktion die ausgeführt werden soll
Aktion für Aufsperren   | Aktion die ausgeführt werden soll

__Schaltflächen im Aktionsbereich__:

Name                    | Beschreibung
----------------------- | ---------------------------------
Status aktualisieren    | Aktualisiert den Status

__Vorgehensweise__:  

Geben Sie bei manueller Konfiguration die NUKI ID und eine Bezeichnung an.  

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name                        | Typ       | Beschreibung
--------------------------- | --------- | ----------------
SmartLockSwitch             | Boolean   | Schalter zum auf- und zusperren des NUKI Smart Locks
SmartLockStatus             | String    | Status des NUKI Smart Locks
SmartLockMode               | String    | Modus des NUKI Smart Locks
SmartLockBatteryState       | Boolean   | Batteriezustand des NUKI Smart Locks
SmartLockBatteryCharging    | Boolean   | Zeigt an, ob die Batterie / Akku geladen wird
SmartLockBatteryChargeState | Integer   | Zeigt die Batterie- / Akkuladung in % an
KeyPadBatteryCritical       | Boolean   | Batteriezustand des zugeordneten KeyPads
Door                        | Boolean   | Status der Türe (geschlossen/geöffnet)
DoorSensorState             | Integer   | Türsensorstatus 

##### Profile:

Nachfolgende Profile werden zusätzlichen hinzugefügt:

NUKI.InstanzID.SmartLockSwitch
NUKI.InstanzID.BatteryCharging
NUKI.InstanzID.BatteryChargeState
NUKI.InstanzID.Door.Reversed
NUKI.InstanzID.DoorSensorState

Wird die NUKI Smart Lock Instanz gelöscht, so werden automatisch die oben aufgeführten Profile gelöscht.

### 6. WebFront

Über das WebFront kann das NUKI Smart Lock zu- oder aufgesperrt werden. Informationen über den Modus, Status und den Batteriestatus werden angezeigt.  
 
### 7. PHP-Befehlsreferenz

```text
Status aktualisieren:  

NUKI_GetSmartLockState(integer $InstanzID);  
Fragt den aktuellen Status des NUKI Smart Locks ab und aktualisiert die Werte der entsprechenden Variablen.  
Rückgabewert: Die aktuellen Werte als String  

Beispiel:  
$state = NUKI_GetSmartLockState(12345);  

Nachfolgende Methode ist jetzt abgekündigt und wird nicht mehr unterstützt:   
NUKI_ShowLockStateOfSmartLock(integer $InstanceID);
```  

```text
Türschloß zu und aufsperren:  

NUKI_ToggleSmartLock(integer $InstanzID, boolean $Status);  
$Status: false = Funktion gemäss Konfiguration (i.d.R. zusperren), true = Funktion gemäss Konfiguration (i.d.R. aufsperren)    
Rückgabewert: Gibt true oder false zurück  

Beispiel:  
Zusperren:      $toggle = NUKI_ToggleSmartLock(12345, false);
Aufsperren:     $toggle = NUKI_ToggleSmartLock(12345, true);
```  

```text
Smart Lock schalten:  

NUKI_SetSmartLockAction(integer $InstanzID, int $LockAction);  

$InstanzID:     Instanz ID des NUKI Smart Locks
$NukiID:        UID des NUKI Gerätes
$LockAction:  
Führt eine Aktion für das NUKI Smart Lock gemäss Tabelle aus:  

Wert | Smart Lock                   
-----|------------------------------------------------------------------------------------------
1    | unlock                       | aufsperren
2    | lock                         | zusperren
3    | unlatch                      | entriegeln
4    | lock ‘n’ go                  | automatisch aufsperren und wieder zusperren
5    | lock ‘n’ go with unlatch     | automatisch aufsperren mit entriegeln und wieder zusperren
  
Beispiel:  
Smart Lock zusperren:   NUKI_SetLockAction(12345, 2);  
Smart Lock aufsperren:  NUKI_SetLockAction(12345, 1);
```