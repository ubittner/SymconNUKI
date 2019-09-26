# NUKI Configurator

[![Image](../imgs/NUKI_Logo.png)](https://nuki.io/de/)  

[![Image](../imgs/NUKI_SmartLock.png)]()  

Dieses Modul listet die mit der NUKI Bridge gekoppelten Geräte (Smart Locks / Opener) auf und der Nutzer kann die ausgewählten Geräte automatisch anlegen lassen.

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

* Listet die verfügbaren NUKI Geräte (Smart Locks / Opener) der zugewiesenen NUKI Bridge auf
* Automatisches Anlegen des ausgewählten NUKI Gerätes

### 2. Voraussetzungen

- IP-Symcon ab Version 5.1
- NUKI Bridge
- NUKI Smart Lock oder 
- NUKI Opener

### 3. Software-Installation

- Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Autor.
  
- Bei privater Nutzung wird das Modul über den Modul Store installiert.

### 4. Einrichten der Instanzen in IP-Symcon

- In IP-Symcon an beliebiger Stelle `Instanz hinzufügen` auswählen und `NUKI Configurator` auswählen, welches unter dem Hersteller `NUKI` aufgeführt ist. Es wird eine NUKI Configurator Instanz unter der Kategorie `Konfigurator Instanzen` angelegt.  

__Konfigurationsseite__:

Name        | Beschreibung
----------- | ---------------------------------
Kategorie   | Auswahl der Kategorie für die NUKI Geräte
NUKI Geräte | Liste der verfügbaren NUKI Geräte

__Schaltflächen__:

Name            | Beschreibung
--------------- | ---------------------------------
Alle erstellen  | Erstellt für alle aufgelisteten NUKI Geräte jeweils eine Instanz
Erstellen       | Erstellt für das ausgewählte NUKI Gerät eine Instanz        

__Vorgehensweise__:

Über die Schaltfläche `AKTUALISIEREN` können Sie im NUKI Configurator die Liste der verfügbaren NUKI Geräte jederzeit aktualisieren.  
Wählen Sie `ALLE ERSTELLEN` oder wählen Sie ein NUKI Gerät aus der Liste aus und drücken dann die Schaltfläche `ERSTELLEN`, um das NUKI Gerät automatisch anzulegen.
Sofern noch keine NUKI Bridge installiert wurde, muss einmalig beim Erstellen der NUKI Configurator Instanz die Konfiguration der NUKI Bridge vorgenommen werden.  
Geben Sie die IP-Adresse, den Port, den Netzwerk-Timeout, die Bridge ID und den API Token der NUKI Bridge an. 
Bei der Ersteinrichtung der NUKI Bridge mittels der Nuki iOS / Android App auf dem Smartphone werden Ihnen die Daten angezeigt.  
Wählen Sie anschließend `WEITER` aus.  

Sofern Sie mehrere NUKI Bridges verwenden, können Sie in der Instanzkonfiguration unter `GATEWAY ÄNDERN` die entsprechende NUKI Bridge auswählen. Die NUKI Bridge Instanz muss dafür bereits vorhanden sein.  

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es werden keine Statusvariablen angelegt.

##### Profile:

Nachfolgende Profile werden zusätzlichen hinzugefügt:

Es werden keine neuen Profile angelegt.

### 6. WebFront

Der NUKI Configurator ist im WebFront nicht nutzbar.  

### 7. PHP-Befehlsreferenz

Es ist keine Befehlsreferenz verfügbar.