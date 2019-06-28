# NUKI Configurator

[![Version](https://img.shields.io/badge/Symcon_Version-5.1>-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul_Version-1.04-blue.svg)]()
![Version](https://img.shields.io/badge/Modul_Build-1006-blue.svg)
[![Version](https://img.shields.io/badge/Code-PHP-blue.svg)]()
[![Version](https://img.shields.io/badge/API_Version-1.07-yellow.svg)](https://nuki.io/wp-content/uploads/2018/04/20180330-Bridge-API-v1.7.pdf)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://github.styleci.io/repos/71931303/shield?branch=master&style=flat)](https://github.styleci.io/repos/71931303)

![Image](../imgs/nuki-logo-black.png)

Dieses Modul listet die mit der NUKI Bridge gekoppelten Smart Locks auf und der Nutzer kann die ausgewählten Smart Locks automatisch anlegen lassen.

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

* Listet die verfügbaren Smart Locks auf
* Automatisches Anlegen des ausgewählten Smart Locks

### 2. Voraussetzungen

- IP-Symcon ab Version 5.1
- NUKI Bridge
- NUKI Smart Lock

### 3. Software-Installation

- Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Autor.
  
- Bei privater Nutzung wird das Modul über den Modul Store installiert.

### 4. Einrichten der Instanzen in IP-Symcon

- In IP-Symcon an beliebiger Stelle `Instanz hinzufügen` auswählen und `NUKI Configurator` auswählen, welches unter dem Hersteller `NUKI` aufgeführt ist. Es wird eine NUKI Configurator Instanz unter der Kategorie `Konfigurator Instanzen` angelegt.  

__Konfigurationsseite__:

Name        | Beschreibung
----------- | ---------------------------------
Kategorie   | Auswahl der Kategorie für die Smart Locks
Smart Locks | Liste der verfügbaren Smart Locks

__Schaltflächen__:

Name            | Beschreibung
--------------- | ---------------------------------
Alle erstellen  | Erstellt für alle aufgelisteten Smart Locks jeweils eine Instanz
Erstellen       | Erstellt für das ausgewählte Smart Lock eine Instanz        

__Vorgehensweise__:

Sofern noch keine NUKI Bridge installiert wurde, muss einmalig beim Erstellen der NUKI Configurator Instanz die Konfiguration der NUKI Bridge vorgenommen werden.  
Geben Sie die IP-Adresse, den Port und den API Token der NUKI Bridge an. 
Bei der Ersteinrichtung der NUKI Bridge mittels der Nuki iOS / Android App auf dem Smartphone werden Ihnen die Daten angezeigt.  
Wählen Sie anschließend `WEITER` aus.  
Über die Schaltfläche `AKTUALISIEREN` können Sie im NUKI Configurator die Liste der verfügbaren Smart Locks jederzeit aktualisieren.  
Wählen Sie `ALLE ERSTELLEN` oder wählen Sie ein Smart Lock aus der Liste aus und drücken dann die Schaltfläche `ERSTELEN`, um die Smart Locks automatisch anzulegen.

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

### 8. GUIDs

Bezeichnung                                 | GUID
--------------------------------------------| --------------------------------------
Bibliothek                                  | {752C865A-5290-4DBE-AC30-01C7B1C3312F}      
NUKI Configurator                           | {1ADAB09D-67EF-412C-B851-B2848C33F67B}      

### 9. Changelog

Version     | Datum      | Beschreibung
----------- | -----------| -------------------
1.04-1005   | 21.04.2019 | Version für Module-Store
1.03        | 27.04.2018 | Update auf API 1.7
1.02        | 19.04.2017 | Update auf API 1.5
1.01        | 31.01.2017 | Erweiterung von Funktionen
1.00        | 01.11.2016 | Modulerstellung

