# Lindy_38153
Die Instanz integriert eine LINDY 38153 8x8 HDMI 4K Matrix in IP-Symcon

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

Steuerung und auslesen der LINDY 38153 8x8 HDMI 4K Matrix 

### 2. Voraussetzungen

- IP-Symcon ab Version 5.0

### 3. Software-Installation

* Über den Module Store das 'Lindy_38153'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen
https://github.com/timo-u/Symcon_Lindy

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'Lindy_38153'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ------------------
Aktualisierungsintervall  | Zeitraum, in dem periodich die Zuordnung der Ein- un Ausgänge abgefragt wird. (Die doppelte Zeitdauer gilt als Watchdog-Zeit für den Verbindungsstatus) 
    

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name   | Typ     | Beschreibung
------ | ------- | ------------
Status        | Boolean        | Verbindungsstatus 
Ausgang 1-8        | Integer     | Zustand der Ausgänge

#### Profile

Name   | Typ
------ | -------
LINDY_Inputs | Bezeichnung der Eingänge
LINDY_Online       | Verbindungsstatus

### 6. WebFront

Ändern der Zuordnung von Eingängen zu Ausgängen in der Videomatrix.

### 7. PHP-Befehlsreferenz

`boolean LINDY_SetMapping(integer $InstanzID, integer $output, integer $input);`
Zuordnung von Ausgängen und Eingängen

Beispiel:
`LINDY_SetMapping(12345, 1, 8);`
Zuordnung von Eingang 8 auf Ausgang 1 


`boolean LINDY_UpdateMapping(integer $InstanzID);`
Aktualisierung der Zuordnung von Ausgängen und Eingängen

Beispiel:
`LINDY_UpdateMapping(12345);`
