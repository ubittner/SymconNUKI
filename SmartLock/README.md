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
* Protokollierung der Schaltvorgänge

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

Name                                | Beschreibung
----------------------------------- | ---------------------------------
(0) Instanzinformationen            | Informationen zu NUKI Smart Lock Instanz
(1) Smart Lock                      | Eigenschaften des NUKI Smart Locks
(2) Schaltvorgänge                  | Definieren der Schaltvorgänge
(3) Protokoll                       | Eigenschaften zur Protokollierung

__Schaltflächen im Aktionsbereich__:

Name                                | Beschreibung
----------------------------------- | ---------------------------------
(1) Smart Lock                      | 
Status anzeigen                     | Zeigt den Status des NUKI Smart Locks an
Unpair                              | Entfernt das Smart Lock von der Bridge
Bedienungsanleitung                 | Zeigt Informationen zu diesem Modul an

__Vorgehensweise__:  

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name                    | Typ       | Beschreibung
----------------------- | --------- | ----------------
SmartLockSwitch         | Boolean   | Schalter zum auf- und zusperren des NUKI Smart Locks
SmartLockStatus         | String    | Zeigt den Status des NUKI Smart Locks an
SmartLockBatteryState   | Boolean   | Zeigt den Batteriezustand des NUKI Smart Locks an
Protocol                | String    | Zeigt die letzten Protokolleinträge an

##### Profile:

Nachfolgende Profile werden zusätzlichen hinzugefügt:

NUKI.SmartLockSwitch

Werden alle NUKI Smart Lock Instanzen gelöscht, so werden automatisch die oben aufgeführten Profile gelöscht.

### 6. WebFront

Über das WebFront kann das NUKI Smart Lock auf- oder zugesperrt werden.
Weiherhin werden Statusinformationen über das NUKI Smart Lock und ein Protokoll angezeigt.
 
### 7. PHP-Befehlsreferenz

`NUKI_ShowLockStateOfSmartLock(integer $SmartLockInstanceID)`  
Zeigt den Status eines smarten NUKI Türschlosses an.  

`NUKI_ToggleSmartLock(integer $SmartLockInstanceID, bool $State)`  
Sperrt das NUKI Smart Lock mit `true` auf oder sperrt mit `false` das NUKI Smart Lock zu.  

`NUKI_UnpairSmartLock(integer $SmartLockInstanceID, bool $State)`  
Löscht das NUKI Smart Lock von der Bridge.