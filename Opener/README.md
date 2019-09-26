# NUKI Opener

[![Image](../imgs/NUKI_Logo.png)](https://nuki.io/de/)  

[![Image](../imgs/NUKI_Opener.png)]()  

Dieses Modul integriert den [NUKI Opener](https://nuki.io/de/opener) in [IP-Symcon](https://www.symcon.de).

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

* Öffnen der Tür, bzw. Betätigen des Türsummers

### 2. Voraussetzungen

- IP-Symcon ab Version 5.1
- NUKI Bridge
- NUKI Opener

### 3. Software-Installation

- Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Autor.
  
- Bei privater Nutzung wird das Modul über den Modul Store installiert.

- Sofern noch keine NUKI Bridge Instanz in IP-Symcon vorhanden ist, so beginnen Sie mit der Installation der NUKI Discovery Instanz.  
Hier finden Sie die [Dokumentation](../Discovery) zum NUKI Discovery.  
Alternativ können Sie die NUKI Bridge auch manuell anlegen. Hier finden Sie die [Dokumentation](../Bridge) zur NUKI Bridge.

- Sofern noch keine NUKI Configurator Instanz in IP-Symcon vorhanden ist, so beginnen Sie mit der Installation und Konfiguration der NUKI Configurator Instanz.  
Hier finden Sie die [Dokumentation](../Configurator) zum NUKI Configurator.  
Alternativ könenn Sie den NUKI Opener auch manuell anlegen. Lesen Sie bitte dafür diese Dokumentation weiter durch.

### 4. Einrichten der Instanzen in IP-Symcon

- In IP-Symcon an beliebiger Stelle `Instanz hinzufügen` auswählen und `NUKI Opener` auswählen, welches unter dem Hersteller `NUKI` aufgeführt ist. Es wird eine NUKI Opener Instanz angelegt, in der die Eigenschaften zur Steuerung des NUKI Openers gesetzt werden können.

__Konfigurationsseite__:

Name                                | Beschreibung
----------------------------------- | ---------------------------------
(0) Instanzinformationen            | Informationen zum NUKI Opener
(1) Opener                          | Eigenschaften des NUKI Openers

__Schaltflächen im Aktionsbereich__:

Name                                | Beschreibung
----------------------------------- | ---------------------------------
(1) Opener                          | 
Status anzeigen                     | Zeigt den Status des NUKI Openers an
Unpair                              | Entfernt den NUKI Opener von der NUKI Bridge
Bedienungsanleitung                 | Zeigt Informationen zu diesem Modul an

__Vorgehensweise__:  

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name                    | Typ       | Beschreibung
----------------------- | --------- | ----------------


##### Profile:

Nachfolgende Profile werden zusätzlichen hinzugefügt:

NUKI.Opener

Werden alle NUKI Opener Instanzen gelöscht, so werden automatisch die oben aufgeführten Profile gelöscht.

### 6. WebFront

Über das WebFront kann der NUKI Opener den Türsummer zum Öffnen der Tür betätigen.
 
### 7. PHP-Befehlsreferenz
