# SymconNUKI
[![Version](https://img.shields.io/badge/Symcon-Modul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Version-1.01-blue.svg)]()
[![Version](https://img.shields.io/badge/Code-php-blue.svg)]()
[![Version](https://img.shields.io/badge/License-CCBYNC4.0-green.svg)](https://creativecommons.org/licenses/by-nc/4.0/)

SymconNUKI ist ein PHP Modul für IP-Symcon ab Version 4.1 zur Einbindung und Steuerung eines NUKI Smart Locks

## Release 1.02
Version 1.02 - NUKI API 1.5 22.12.2016

Änderungen:
- Update auf API 1.5
- Callback wird jetzt schneller ausgewertet, damit dir der Status nun schneller angezeigt.

## Entwicklung
Die Entwicklung dieses Moduls verfolgt keinen kommerziellen Zweck. Ziel ist es, den Funktionsumfang von IP-Symcon zu erweitern. Die Entwicklung dieses Moduls findet in der Freizeit als Hobby statt und stellt keinen Anspruch auf Fehlerfreiheit, Weiterentwicklung oder sonstige Unterstützung dar.

## Eigenschaften
* Ab- und Aufschließen
* Statusmeldungen

## Voraussetzungen
* [IP-Symcon Version 4.1](https://www.symcon.de/shop/)
* [Nuki SmartLock](https://nuki.io/de/smart-lock/)
* [Nuki Bridge](https://nuki.io/de/bridge/)

## Hinweis
In der Version 1.01 wurden gegenüber der Version 1.00 Änderungen vorgenommen. Der Entwickler empfiehlt vor einem Update auf Version 1.01 die Version 1.00 aus IP-Symcon über die Modulverwaltung zu entfernen und anschließend das Modul in IP-Symcon wieder hinzuzufügen. Da nur wenige Konfigurationen notwendig sind, hält sich der Arbeitsaufwand in Grenzen.

## Installation
Nachfolgend wird die Installation dieses Moduls beschrieben.
Folgende Instanzen stehen dann in IP-Symcon zur Verfügung:

- [x] NUKI Bridge

- [x] NUKI SmartLock

- [x] NUKI Socket

1.	SymconNUKI Modul installieren

	Im Objektbaum von IP-Symcon die Kern-Instanzen aufrufen. Danach die [Modulverwaltung](https://www.symcon.de/service/dokumentation/modulreferenz/module-control/) aufrufen. Sie sehen nun die bereits installierten Module.
	Dort können Sie über den Button `Hinzufügen` das SymconNUKI Modul hinzufügen.
	Wählen Sie als URL:

	```
	https://github.com/ubittner/SymconNUKI
	```
	Anschließend klicken Sie auf `OK`, um das Modul zu installieren.

2.	NUKI Bridge

	Fangen Sie mit der NUKI Bridge an. Die NUKI Bridge ist eine I/O Instanz und wird für die Kommunikation mit dem Smart Lock benötigt.

	Fügen Sie in IP-Symcon eine neue Instanz hinzu `Tastenkürzel ctrl-1`. Im Schnellfilter können Sie NUKI eingeben, um schneller zur Auswahl zu gelangen.
	Wählen Sie `NUKI Bridge` aus und klicken anschließend auf `Weiter >>`. Bestätigen Sie den nächste Dialog mit `OK`.

	Im Objektbaum von IP-Symcon finden Sie unter I/O Instanzen eine neue NUKI Bridge Instanz.

3. NUKI Bridge Konfiguration

	Nach dem die NUKI Bridge Instanz erstellt wurde, müssen noch weitere Konfigurationen vorgenommen werden.

	<i>Konfigurationsbereich Nuki Bridge</i>

	Geben Sie die IP-Adresse, den Port und den API Token der NUKI Bridge ein. Bei der Ersteinrichtung der NUKI Bridge mittels der NUKI App auf dem Smartphone werden Ihnen die Daten angezeigt. Falls Sie diese vergessen haben oder Ihnen nicht mehr bekannt sind, so lernen Sie die NUKI Bridge mittels NUKI App auf dem Smartphone erneut an und notieren sich die Daten.

	Wenn Sie die Daten eingetragen haben klicken Sie im Konfigurationsformular auf `Übernehmen`, um die Konfigurationsdaten zunächst zu speichern.

	<i>Konfigurationsbereich Smart Lock</i>

	Mit `Smart Locks anzeigen` werden Ihnen alle NUKI Smart Locks die mit der NUKI Bridge verbunden sind angezeigt.

	Mit `Kategorie Wählen` können Sie festlegen, in welche Kategorie die NUKI Smart Locks aufgeführt werden sollen. Es kann auch die Hauptkategorie genutzt werden.
	Bestätigen Sie die Übernahme der Auswahl mit `Übernehmen`, um die Konfigurationsdaten zu speichern.

	Um die NUKI Smart Lock Instanz(en) anzulegen klicken Sie auf `Smart Locks abgleichen`. Es werden nun automatisch alle NUKI SmartLocks angelegt, die mit der NUKI Bridge verbunden sind.

	<i>Konfigurationsbereich NUKI Socket</i>

	Für die Aktualisierung der Informationen der NUKI Smart Locks wird ab Version 1.01 ein Callback genutzt. Der in früheren Versionen genutzte Update Timer mit Intervall entfällt somit und kann, falls noch vorhanden, vom Nutzer gelöscht werden.

	Geben Sie die IP-Adresse des IP-Symcon Servers ein und den Callback Port, den Sie nutzen möchten. Klicken sie im Konfigurationsformular auf `Änderungen übernehmen`, um die Konfigurationsdaten zu übernehmen. Anschließend klicken Sie auf den Button `Callback anlegen`. Der Callback wird automatisch auf der NUKI Bridge eingetragen und in IP-Symcon wird autmatisch der entsprschende NUKI Socket (Server Socket) angelegt.

	Über den Button `Callback anzeigen` werden die registrierten Callback angezeigt.

	Über die Auswahl der `Callback ID`, sowie der Übernahme der Auswahl durch `Änderungen übernehmen` und anschließend `Callback löschen` wird der entsprechende Callback von der NUKI Bridge wieder gelöscht.

	<i>Konfigurationsbereich NUKI Bridge Informationen</i>

	`Info anzeigen` zeigt Informationen der NUKI Bridge an.

	`Logdatei anzeigen` zeigt die Logdatei der NUKI Bridge an.

	`Logdatei löschen` löscht die Logdatei der NUKI Bridge an.

	`Update Firmware` prüft ein Firmware Update und führt dieses aus.

4. NUKI SmartLock

	Das manuelle Anlegen einer Smart Lock Instanz ist nicht zwingend erforderlich. Über die Konfigurationsseite der NUKI Bridge Instanz kann durch `SmartLocks abgleichen` das Smart Lock automatisch angelegt werden.

5. NUKI SmartLock Konfiguration

	<i>Konfigurationsbereich NUKI Smart Lock</i>

	UID ist die eindeutige ID eines NUKI Smart Locks, wird automatisch beim Anlegen über die NUKI Bridge ermittelt und muss nicht geändert werden.

	Name des NUKI Smart Locks, der bei Bedarf geändert werden kann.

	Über den Button `Status anzeigen` wird der aktuelle Status des NUKI Smart Locks angezeigt.

	<i>Konfigurationsbereich Schaltvorgänge</i>

	Hier kann festgelegt werden, welcher Schaltvorgang ausgeführten werden soll, wenn im Webfront der Schalter des NUKI SmartLocks auf aus, bzw. ein gestellt wird.

	Mögliche Schaltvorgänge (LockAction) sind:

		1 aufschließen (unlock)
		2 abschließen (lock)
		3 entriegeln (unlatch)
		4 automatisch abschließen (lock ‘n’ go)
		5 automatisch abschließen und entriegeln (lock ‘n’ go with unlatch)

	Über `Schalter verbergen` kann man den Schalter im WebFront verbergen oder anzeigen lassen.

## Bridge Callback Simulation (Entwicklungsumgebung / Develop environment)

Mit einem curl Befehl kann der Callback einer NUKI Bridge im Rahmen einer Entwicklungsumgebung simuliert werden. Für den normalen Gebrauch oder Einsatz der NUKI Bridge ist der curl Befehl nicht notwendig.

Für die Verwendung von curl über die Konsole des entsprechenden Betriebssystems informieren Sie sich bitte im Internet.

`curl  -v -A "NukiBridge_12345678" -H "Connection: Close" -H "Content-Type: application/json;charset=utf-8" -X POST -d '{"nukiId":987654321,"state":1,"stateName":"locked","batteryCritical":true}' http://127.0.0.1:8081`

"NukiBridge_12345678" ist die ID der NUKI Bridge

"nukiId":987654321 ist die ID des NUKI Smartlocks

http:<span></span>//127.0.0.1:8081 ist die IP-Adresse und Port des Server Sockets

## Funktionen innerhalb von IP-Symcon

Präfix der Funktionen in IP-Symcon: NUKI

* NUKI_getSmartLocks(int $BridgeInstanceID)

	Liefert eine Liste aller verfügbaren Smart Locks

* NUKI_getLockStateOfSmartLock(int $BridgeInstanceID, int $SmartLockUniqueID)

   Zeigt den aktuellen Status eines Smart Locks an

* NUKI_setLockActionOfSmartLock(int $BridgeInstanceID, int $SmarLockUniqueID, int $LockAction)

   Führt eine Aktion für ein Smart Lock aus 

*	NUKI_unpairSmartLockFromBridge(int $BridgeInstanceID, int $SmarLockUniqueID)

   Löscht das Smart Lock aus der Bridge

* NUKI_getBridgeInfo(int $BridgeInstanceID)

	Zeigt alle Smart Locks in der Nähe an und liefert Informationen zur Bridge

* NUKI_addCallback(int $BridgeInstanceID)

	Legt einen Callback auf der Bridge an

* NUKI_listCallback(int $BridgeInstanceID)

   Zeigt die angelegten Callbacks auf der Bridge an

* NUKI_deleteCallback(int $BridgeInstanceID, int $CallbackID)

   Löscht den Callback mit der $CallbackID auf der Bridge

* NUKI_getBridgeLog(int $BridgeInstanceID)

	Zeigt das Log der Bridge an

* NUKI_clearBridgeLog(int $BridgeInstanceID)

	Löscht das Log der Bridge

* NUKI_updateBridgeFirmware(int $BridgeInstanceID)

	Prüft auf ein neues Firmware Update der Bridge und installiert es

*	NUKI_rebootBridge(int $BridgeInstanceID)

   Starte die Brdige neu

 *	NUKI_factoryResetBridge(int $BridgeInstanceID)

   Setzt die Bridge auf Werkseinstellungen

* NUKI_syncSmartLocks(int $BridgeInstanceID)

	Gleicht alle Smart Locks der Bridge ab und legt diese in IP-Symcon an

* NUKI_updateStateOfSmartLocks(int $BridgeInstanceID)

	Aktualisiert den Status aller Smart Locks

* NUKI_showLockStateOfSmartLock($SmartLockInstanceID)

	Zeigt den Status eines Smart Locks an

## GUIDs

{752C865A-5290-4DBE-AC30-01C7B1C3312F} NUKI Library

{B41AE29B-39C1-4144-878F-94C0F7EEC725} NUKI Bridge

{73188E44-8BBA-4EBF-8BAD-40201B8866B9} NUKI Bridge (I/O) TX (I)

{3DED8598-AA95-4EC4-BB5D-5226ECD8405C} NUKI Bridge (I/O) RX (CR)

{37C54A7E-53E0-4BE9-BE26-FB8C2C6A3D14} NUKI Smart Lock

{8062CF2B-600E-41D6-AD4B-1BA66C32D6ED} NUKI Socket (Server Socket)

## Versionen

1.02, 19.04.2017, Update auf API 1.5

1.01, 31.01.2017, Erweiterung von Funktionen

1.00, 01.11.2016, Modulerstellung

## Lizenz

CC BY-NC 4.0

## Author

Ulrich Bittner
