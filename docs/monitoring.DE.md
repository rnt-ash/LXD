# OVZ Monitoring
---
## Allgemein
Besteht aus zwei Teilen, dem Remote- und dem Local-Monitoring. Es bietet die folgenden Kern-Features an:
- Monitoring
- automatische Heilung von VirtualServers auf connecteten Physicalservers durch Restart-Jobs (nur Remote-Monitoring)
- Alarmierung durch Mail [weitere Infos](###alarmierung)
- Platzsparende Aufbewahrung von historischen Log-Daten (älter als vom letzten Monat)

### Grundlegende Konzepte

#### Periodische Ausführung mit Cronjobs
Es ist empfohlen das Monitoring über Phalcon-Tasks mit Cronjobs zu betreiben. Es gilt den [MonitoringTask](../tasks/MonitoringTask.php) 
aufzurufen und eine entsprechende Action anzugeben. Folgende Actions werden für den Betrieb benötigt:
- Action `runJobs` jede Minute zur Ausführung von MonRemoteJobs im Status up
- Action `runCriticalJobs` jede Minute zur Ausführung von MonRemoteJobs im Status down, mit Eskalation (Heilung, Alarmierung)
- Action `runLocalJobs` jede Minute zur Ausführung von MonLocalJobs
- Action `recomputeUptimes` jede Stunde [weitere Infos](####das-feld-uptime-in-monremotejobs)
- Action `genMonUptimes` monatlich [weitere Infos](####monuptimes)
- Action `genMonLocalDailyLogs` monatlich [weitere Infos](####monlocaldailylogs)

In OVZCP werden diese Cronjobs im Installer erstellt.

#### Models
Es werden die gleichen Model-Klassen, [MonJobs](../models/MonJobs.php) und [MonLogs](../models/MonLogs.php),
für remote und local Monitoring verwendet. Dies ist ein bewusster Design-Entscheid, 
damit insbesondere die Programmierung im View einfacher fällt. Damit die einzelnen Jobs klar kategorisierbar sind, besteht 
ein Feld mon_type, welches local oder remote sein kann. Einige Felder sind nur für local und andere nur für remote. Dies 
führt zu ein bisschen mehr Komplexität bei den Validatoren.
Eine Ausnahme bilden die Klassen MonUptimes und MonLocalDailyLogs, die aufgrund der unterschiedlichen Archivierung-Konzepts
(das eine monatlich das andere täglich) getrennt sind. 

#### Interface für monitorbare Server
Das Monitoringsystem kann sowohl PhysicalServers als auch VirtualServers monitoren. Um diese beide behandeln zu können, wurde 
ein gemeinsames Interface [MonServerInterface](../interfaces/MonServerInterface.php) definiert, welches als Typ im Monitoring 
verwendet wird. Es beinhaltet lediglich die vom Monitoring benötigten Funktionen. Dies sind hauptsächlich Getter. Eine Ausnahme 
ist die updateOvzStatistics()-Methode, welche schreibend auf die Entities zugreift.

#### Aufrufpfad des Monitoring-Systems
- [MonSystem](../services/MonSystem.php) holt aus den Models MonJobs und MonJobs die benötigten Entities und führt das
Monitoring aus, verwendet bei Bedarf das [MonAlarm](../services/MonAlarm.php)
- [HealingSystem](../services/MonHealing.php) ist für die Behandlung von kritischen remote MonJobs zuständig, versucht also zu heilen und verwendet bei Bedarf das [MonAlarm](../services/MonAlarm.php)    

#### Behaviors für Monitoring
Damit alle MonJobs im selben Model gespeichert werden können, aber trotzdem unterschiedliche Instruktionen beim Monitoring 
ausführen, muss einem MonJob eine MonBehavior-Class zugewiesen werden. Dieser Klassenname wird als String in der Entity 
abgelegt. Diese Klasse muss das Interface [MonBehaviorInterface](../interfaces/MonBehaviorInterface.php) implementieren, welches 
eine execute()-Methode deklariert. Die Behaviors sind alle im Verzeichnis utilities/monbehaviors abgelegt. 

Das gleiche Prinzip gilt auch für das local Monitoring mit den MonJobs. Die angegebene Klasse muss aber das Interface 
[MonLocalBehaviorInterface](../interfaces/MonLocalBehaviorInterface.php) implementieren. Dieses Interface setzt noch eine 
genThresholdString()-Methode voraus, welche von der Alarmierung für die Generierung des Notification-Contents verwendet wird. 
Ein wichtiger Punkt im local Monitoring ist, dass zusätzlich zur Behavior-Class auch noch Behavior-Params angegeben werden, 
welche den Zugriffspfad auf das ovz_statistics Feld für den zu monitorenden Wert spezifizieren. Somit können für PhysicalServers
und VirtualServers die gleichen Behaviors verwendet werden, auch wenn der Wert an einem anderen Ort im ovz_statistics-Array liegt. 
Hinzu kommt auch, dass insbesondere bei PhysicalServers nicht nur die /-Partition, sondern auch die /vz-Partition gemonitort
werden soll. Hierfür kann einfach ein anderer Pfad in den Behavior-Params angegeben werden.

### Alarmierung
Es existieren zwei Stufen der Alarmierung, die Message und der Alarm. Pro MonJob können Kontakte für Message und Alarm angegeben
werden. Dafür wird ein kommaseparierter String mit Logins-IDs angegeben. Es macht Sinn den Alarm auf einen Kontakt zu machen, der
die Mails zeitnah liest (z.B. Push auf Handy). Für den Message Konakt kann auch einer verwendet werden, der nur zu Bürozeiten gelesen
wird (z.B. ein öffentlicher Ordner). Folgendermassen wird benachrichtigt:

Alarm:
- Remote: MonJob ist down und benötigt wahrscheinlich Eingriff eines Admins (z.B. kein Healing aktiv oder Selfhealing brachte keine Besserung)
- Remote: MonJob ist wieder up (nur wenn vorhin für diesen Job Alarm geschickt wurde)
- Local: Unter bzw. Überschreitung des Maximalvalues

Message:
- Remote: Benachrichtigung über eine Short Downtime (weniger als 1 Minute)
- Remote: Benachrichtigung über ausgeführten HealJob
- Local: Unter bzw. Überschreitung des Warnvalues

Pro MonJob kann eine alarmperiod definiert werden. Durch dies wird konfiguriert, wie oft eine erneute Warnmeldung geschickt werden
soll.
### Remote-Monitoring Ergänzungen
Monitoring vom zentralen ControlPanel Server auf über Netzwerk angebotene Services entfernter Server, 
z.B. http, ftp, ping oder ssh.
Das Remote-Monitoring kann direkt vom ControlPanel Server die Überwachung ausführen. 

Zugehörige Models:
- [MonJobs](../models/MonJobs.php)
- [MonLogs](../models/MonLogs.php)
- [MonUptimes](../models/MonUptimes.php)

In remote MonJobs werden die periodisch auszuführenden Jobs definiert. Die von den remote MonJobs generierten Logs werden in
MonLogs abgelegt. Das remote Monitoring kennt nur den Status up oder down. Deren Logs beinhalten also im Value 
lediglich 1 oder 0. Wird aufgrund einer Downtime ein Healjob ausgeführt, wird die ID des Jobs im entsprechenden 
MonLogs-Eintrag vermerkt.

#### MonUptimes
MonLogs welche älter als vom letzten Monat sind werden durch den [MonUptimesGenerator](../utilities/MonUptimesGenerator.php) 
zu MonUptimes umgerechnet. Dabei wird für einen MonJobs pro Monat eine Zeile generiert. Dies bedeutet eine Verminderung
dieser Log-Daten bei einer durchschnittlichen Periodizität von 5 Minuten etwa das 7000fache. Dieser Vorgang sollte monatlich 
durchgeführt werden.

#### Das Feld Uptime für remote MonJobs
In MonJobs gibt es das Feld Uptime, welches als JSON Informationen zu Uptimes der aktuellen Periode (solange die 
MonLogs zurückreichen), des aktuellen Jahrs und der gesamten Log-Zeit beinhalten (berechnet aus MonLogs und
MonUptimes). Das Uptime Feld sollte ca. stündlich neu berechnet werden. 

### Local-Monitoring Ergänzungen
Monitoring von lokal auf Remoteservern (PhysicalServers, VirtualServers) zu beschaffenden Informationen zur Systemauslastung 
wie z.B. cpu, memory oder diskspace. Dieses Monitoring nimmt sich zur Beschaffung dieser Informationen das Jobsystem zuhilfe.
Dabei werden die Jobs an den entsprechenden PhysicalServers geschickt. 

Zugehörige Models:
- [MonJobs](../models/MonJobs.php)
- [MonLogs](../models/MonLogs.php)
- [MonLocalDailyLogs](../models/MonLocalDailyLogs.php)

#### MonLocalDailyLogs
MonLogs welche älter als vom letzten Monat sind werden durch den 
[MonLocalDailyLogsGenerator](../utilities/MonLocalDailyLogsGenerator.php) zu MonLocalDailyLogs
umgerechnet. Dabei wird für einen MonJobs pro Tag eine Zeile generiert. Dieser Vorgang sollte monatlich durchgeführt
werden. 

## Konfiguration

### Standardkonfiguration der Kontakte
Das Erstellen von neuen MonJobs soll extrem einfach sein, man soll nur den Typ auswählen müssen.
Die meisten Werte für neue MonJobs können als Konstanten oder als berechnete Werte automatisch gesetzt werden. 
Eine Ausnahme bilden die Kontakte, da diese FKs auf die Tabelle logins sind. 
Man müsste also zumindest die Message und Alarm Contacts auswählen. 
Man kann diese aber auch in der Config definieren, dann verschwindet das Select Feld im Erstellen Form.
Um für alle MonJobs den Message Kontakt 10 und die Alarm Kontakte 20 und 21 zu setzen, kann folgendes in der config.ini angegeben werden.
```
[monitoring]
contacts = {"default":{"message":[10],"alarm":[20,21]}}
```
Möchte man für bestimmte MonJobs-Typen (Behaviors) andere Kontakte, so kann dies folgendermassen konfiguriert werden.
```
[monitoring]
contacts = {"default":{"message":[10],"alarm":[20,21]},"DiskspacefreeMonLocalBehavior":{"message":[2],"alarm":[3,4]}
```
Für jede Abweichung vom Default kann also ein weiterer Key erstellt werden.