# OVZ Monitoring
---
## Allgemein
Besteht aus zwei Teilen, dem Remote- und dem Local-Monitoring. Es bietet die folgenden Kern-Features an:
- Monitoring
- automatische Heilung von VirtualServers auf connecteten Physicalservers durch Restart-Jobs (nur Remote-Monitoring)
- Alarmierung durch Mail
- Platzsparende Aufbewahrung von historischen Log-Daten (älter als vom letzten Monat)

### Grundlegende Konzepte

#### Periodische Ausführung mit Cronjobs
Empfohlen das Monitoring über Phalcon-Tasks mit Cronjobs zu betreiben. Es gilt den [MonitoringTask](../tasks/MonitoringTask.php) 
aufzurufen und eine entsprechende Action anzugeben. Folgende Actions werden für den Betrieb benötigt:
- Action `runJobs` jede Minute zur Ausführung von MonRemoteJobs im Status up
- Action `runCriticalJobs` jede Minute zur Ausführung von MonRemoteJobs im Status down, mit Eskalation (Heilung, Alarmierung)
- Action `runLocalJobs` jede Minute zur Ausführung von MonLocalJobs
- Action `recomputeUptimes` jede Stunde [weitere Infos](####das-feld-uptime-in-monremotejobs)
- Action `genMonUptimes` monatlich [weitere Infos](####monuptimes)
- Action `genMonLocalDailyLogs` monatlich [weitere Infos](####monlocaldailylogs)

In OVZCP werden diese Cronjobs im Installer erstellt.

#### Interface für monitorbare Server
Das Monitoringsystem kann sowohl PhysicalServers als auch VirtualServers monitoren. Um diese beide behandeln zu können, wurde 
ein gemeinsames Interface [MonServerInterface](../interfaces/MonServerInterface.php) definiert, welches als Typ im Monitoring 
verwendet wird. Es beinhaltet lediglich die vom Monitoring benötigten Funktionen. Dies sind hauptsächlich Getter. Eine Ausnahme 
ist die updateOvzStatistics()-Methode, welche schreibend auf die Entities zugreift.

#### Aufrufpfad des Monitoring-Systems
- [MonSystem](../services/MonSystem.php) holt aus den Models MonRemoteJobs und MonLocalJobs die benötigten Entities und führt das
Monitoring aus, verwendet bei Bedarf das [MonAlarm](../services/MonAlarm.php)
- [HealingSystem](../services/MonHealing.php) ist für die Behandlung von kritischen MonRemoteJobs zuständig, versucht also zu heilen und verwendet bei Bedarf das [MonAlarm](../services/MonAlarm.php)    

#### Behaviors für Monitoring
Damit alle MonRemoteJobs im selben Model gespeichert werden können, aber trotzdem unterschiedliche Instruktionen beim Monitoring 
ausführen, muss einem MonRemoteJob eine MonBehavior-Class zugewiesen werden. Dieser Klassenname wird als String in der Entity 
abgelegt. Diese Klasse muss das Interface [MonBehaviorInterface](../interfaces/MonBehaviorInterface.php) implementieren, welches 
eine execute()-Methode deklariert. Die Behaviors sind alle im Verzeichnis utilities/monbehaviors abgelegt. 

Das gleiche Prinzip gilt auch für das Local-Monitoring mit den MonLocalJobs. Die angegebene Klasse muss aber das Interface 
[MonLocalBehaviorInterface](../interfaces/MonLocalBehaviorInterface.php) implementieren. Dieses Interface setzt noch eine 
genThresholdString()-Methode voraus, welche von der Alarmierung für die Generierung des Notification-Contents verwendet wird. 
Ein wichtiger Punkt im Local-Monitoring ist, dass für PhysicalServers andere Behaviors als für VirtualServers verwendet werden 
sollen, da die zu vorhandenen Statistik-Daten in unterschiedlichen Formaten abgelegt sind.

### Remote-Monitoring Ergänzungen
Monitoring vom zentralen ControlPanel Server auf über Netzwerk angebotene Services entfernter Server, 
z.B. http, ftp, ping oder ssh.
Das Remote-Monitoring kann direkt vom ControlPanel Server die Überwachung ausführen. 

Zugehörige Models:
- [MonRemoteJobs](../models/MonRemoteJobs.php)
- [MonRemoteLogs](../models/MonRemoteLogs.php)
- [MonUptimes](../models/MonUptimes.php)

In MonRemoteJobs werden die periodisch auszuführenden Jobs definiert. Die von den MonRemoteJobs generierten Logs werden in
MonRemoteLogs abgelegt. Das Remote-Monitoring kennt nur den Status up oder down. Deren Logs beinhalten also im Value 
lediglich 1 oder 0. Wird aufgrund einer Downtime ein Healjob ausgeführt, wird die ID des Jobs im entsprechenden 
MonRemoteLogs-Eintrag vermerkt.

#### MonUptimes
MonRemoteLogs welche älter als vom letzten Monat sind werden durch den [MonUptimesGenerator](../utilities/MonUptimesGenerator.php) 
zu MonUptimes umgerechnet. Dabei wird für einen MonRemoteJobs pro Monat eine Zeile generiert. Dies bedeutet eine Verminderung
dieser Log-Daten bei einer durchschnittlichen Periodizität von 5 Minuten etwa das 7000fache. Dieser Vorgang sollte monatlich 
durchgeführt werden.

#### Das Feld Uptime in MonRemoteJobs
In MonRemoteJobs gibt es das Feld Uptime, welches als JSON Informationen zu Uptimes der aktuellen Periode (solange die 
MonRemoteLogs zurückreichen), des aktuellen Jahrs und der gesamten Log-Zeit beinhalten (berechnet aus MonRemoteLogs und
MonUptimes). Das Uptime Feld sollte ca. stündlich neu berechnet werden. 

### Local-Monitoring Ergänzungen
Monitoring von lokal auf Remoteservern (PhysicalServers, VirtualServers) zu beschaffenden Informationen zur Systemauslastung 
wie z.B. cpu, memory oder diskspace. Dieses Monitoring nimmt sich zur Beschaffung dieser Informationen das Jobsystem zuhilfe.
Dabei werden die Jobs an den entsprechenden PhysicalServers geschickt. 

Zugehörige Models:
- [MonLocalJobs](../models/MonLocalJobs.php)
- [MonLocalLogs](../models/MonLocalLogs.php)
- [MonLocalDailyLogs](../models/MonLocalDailyLogs.php)

#### MonLocalDailyLogs
MonLocalLogs welche älter als vom letzten Monat sind werden durch den 
[MonLocalDailyLogsGenerator](../utilities/MonLocalDailyLogsGenerator.php) zu MonLocalDailyLogs
umgerechnet. Dabei wird für einen MonLocalJobs pro Tag eine Zeile generiert. Dieser Vorgang sollte monatlich durchgeführt
werden. 

