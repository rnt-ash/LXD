<?php

return [

    // Colocations
    "colocation_all_colocations" => "Alle Colocations",
    "colocations_invalid_level" => "ungültiger Level!",
    "colocations_choose_customer" => " wählen Sie einen Kunden aus.",
    "colocations_customer" => "Kunde",
    "colocations_name" => "Name",
    "colocations_colocationname" => "Mein Colocation Name",
    "colocations_description" => "Beschreibung",
    "colocations_description_info" => "Zusätzliche Informationen zu dieser Colocation",
    "colocations_location" => "Location",
    "colocations_location_info" => "Meine Colocation",
    "colocations_activ_date" => "Aktivierungsdatum",
    "colocations_name_required" => "Benötigt Namen",
    "colocations_namemax" => "Name ist zu lang",
    "colocations_namemin" => "Name ist zu kurz",
    "colocations_name_valid" => "Name muss alphanumeric sein und darf folgende Charakter beinhalten: \, -, _ and space.",
    "colocations_customer_required" => "Benötige Kunden",
    "colocations_customer_not_exist" => "Ausgewählter Kunde existiert nicht",
    "colocations_location_max" => "Location ist zu lang",
    "colocations_location_max" => "Location ist zu kurz",
    "colocations_locaton_valid" => "Location muss alphanumeric sein und darf folgende Charakter beinhalten: \, -, _ and space.",
    //View
    "colocations_title" => " Colocations",
    "colocations_view_physicalserver" => "Physikalische Server",
    "colocations_view_nophysicalserver" => "Keine physikalische Serve gefunden...",
    "colocations_view_ipobjects" => "IP Objekte",
    "colocations_view_newipobject" => "IP-Objekt hinzufügen",
    "colocations_view_noipobjects" => "Keine IP-Objekte gefunden...",
    "colocations_view_editipobject" => "IP-Objekt bearbeiten",
    "colocations_view_delmessage" => "Sind Sie sicher, dass Sie das Item löschen wollen ?",
    "colocations_view_delete" => "IP-Objekt löschen",
    "colocations_generalinfo" => "Allgemeine Informationen",
    "colocations_editovz" => "OVZ Einstellungen bearbeiten",
    "colocations_delcolocation" => "Colocation löschen",
    "colocations_view_customer" => "Kunde: ",
    "colocations_view_activdate" => "Aktivierungsdatum: ",
    "colocations_view_description" => "Beschreibnung: ",
    "colocations_title" => " Colocations",
    "colocations_save" => "Speichern",
    "colocations_cancel" => "Abbrechen",
    "colocations_genpdf" => "Generiere PDF zu IP Objekten",
    "colocations_ipobjects" => "IP Objekte",
    "colocations_pdf_no_ipobjects" => "Keine IP-Reservationen in der Colocation",
    "colocations_createpdf" => "PDF zur IP-Übersicht erstellen",
    "colocations_new_colocation" => "Neue Colocation erstellen",
    
    // IP Objects
    "ipobjects_address_is_now_main" => "IP Adresse %address% ist nun die Hauptadresse.",
    "ipobjects_item_not_found" => "Item wurde nicht gefunden!",
    "ipobjects_item_not_exist" => "Das Item existiert nicht!",
    "ipobjects_ip_success" => "IP Adresse wurde erfolgreich geändert",
    "ipobjects_ip_not_found" => "IP-Objekt wurde nicht gefunden !",
    "ipobjects_ip_conf_failed" => "Konfiguration der IP Adresse auf dem virtuellen Server fehlgeschlagen: ",
    "ipobjects_ip_delete_success" => "IP-Objekt wurde erfolgreich gelöscht",
    "ipobjects_ip_adress" => "IP-Objekt muss eine Adresse sein",
    "ipobjects_ip_assigned" => "IP-Objekt muss zugewiesen sein",
    "ipobjects_ip_update_failed" => "Aktualisieren des IP-Objektes fehlgeschlagen!",
    "ipobjects_ip" => "IP Adresse",
    "ipobjects_ip_addition" => "Zusätzlicher IP Wert",
    "ipobjects_ip_additioninfo" => "Leer | Subnetmaske wenn IP Address | End-IP-Address wenn Range | Prefix wenn Subnet",
    "ipobjects_allocated" => "Zugeteilt",
    "ipobjects_ismain" => "Ist Hauptadresse",
    "ipobjects_isnotmain" => "Ist nicht Hauptadresse",
    "ipobjects_ip_main" => "Haupt IP Adresse",
    "ipobjects_comment" => "Kommentar",
    "ipobjects_commentinfo" => "Zusätzliche Information zum IP Objekt",
    "ipobjects_dco_submit" => "Kein Datacenter Objekt übermittelt",
    "ipobjects_ip_not_valid" => "Keine gültige IP Adresse",
    "ipobjects_secont_value_valid" => "Kein gültiger zweiter Wert",
    "ipobjects_assigned_ip" => "Zugewiesene IPs können keine Range sein",
    "ipobjects_no_reservation" => "Keine passsende Reservation gefunden.",
    "ipobjects_ip_notpart_reservation" => "Diese IP ist nicht Teil einer Reservation.",
    "ipobjects_ip_already_exists" => "IP existiert bereits.",
    "ipobjects_ip_required" => "Benötige IP-Adresse",
    "ipobjects_ip_valid" => "Ungültige Zeichen in der IP Address.",
    "ipobjects_second-value_check" => "Ungültige Zeichen im zweiten Wert.",
    "ipobjects_main" => "Hauptadresse kann nur 0 oder 1 sein.",
    "ipobjects_allocated_value" => "Bitte wählen Sie einen korrekten zugewiesenen Wert",
    "ipobjects_comment_length" => "Kommentar ist zu lang (max. 50 Zeichen)",
    "ipobjects_unexpected_type" => "Unerwarteter Typ!",
    //View
    "ipobjects_edit_title" => "IP Objekte",
    "ipobjects_reservations" => "Reservationen",
    "ipobjects_edit_cancel" => "Abbrechen",
    "ipobjects_edit_save" => "Speichern",
      
    // Physical Servers
    "physicalserver_all_physicalservers" => "Alle Physical Server",
    "physicalserver_does_not_exist" => "Der Physical Server existiert nicht: ",
    "physicalserver_does_not_exist" => "Der Physical Server existiert nicht: ",
    "physicalserver_not_ovz_enabled" => "Server ist nicht OVZ aktiviert!",
    "physicalserver_job_failed" => "Ausführen des Jobs nicht erfolgreich: ",
    "physicalserver_update_failed" => "Update des Servers fehlgeschlagen: ",
    "physicalserver_update_success" => "Informationen erfolgreich gespeichert",
    "physicalserver_remove_server_first" => "Bitte löschen Sie zuerst den virtuellen Server !",
    "physicalserver_not_found" => "Physischen Server nicht gefunden !",
    "physicalserver_connection_prepare_title" => "Vorbereitende Schritte",
    "physicalserver_connection_prepare_instructions" => "Bevor die Verbindung mit dem OpenVZ-Server aufgebaut werden kann, müssen auf darauf folgende Befehle ausgeführt werden. Damit wird das System aktualisiert und es werden nötige Software-Pakete installiert.",
    "physicalserver_connection_success" => "Verbindung erfolgreich aufgebaut zu: ",
    "physicalserver_connection_restart" => "Es wird dringenst empfohlen den OpenVZ-Server neuzustarten nachdem die Verbindung aufgebaut wurde!",
    "physicalserver_connection_failed" => "Verbindung zum OpenVZ-Server fehlgeschlagen: ",
    "physicalserver_name" => "Name",
    "physicalserver_myserver" => "Mein Server",
    "physicalserver_fqdn" => "FQDN",
    "physicalserver_hostdomaintld" => "host.domain.tld",
    "physicalserver_choose_customer" => "Bitte wählen Sie einen Kunden aus.",
    "physicalserver_customer" => "Kunde",
    "physicalserver_choose_colocation" => "Bitte wählen Sie eine Colocation aus.",
    "physicalserver_colocation" => "Colocation",
    "physicalserver_cores" => "Kerne",
    "physicalserver_cores_available" => "Verfügbare Kerne  (z.B. 4)",
    "physicalserver_memory" => "Memory",
    "physicalserver_memory_available" => "Verfügbare Memory in MB (z.B. 2048)",
    "physicalserver_space" => "Speicher",
    "physicalserver_space_available" => "Verfügbarer Speicher in MB (z.B. 102400)",
    "physicalserver_activ_date" => "Aktivierungsdatum",
    "physicalserver_discription" => "Beschreibung",
    "physicalserver_discription_info" => "Zusätzliche Beschreibung zu diesem Server",
    "physicalserver_name_required" => "Name benötigt",
    "physicalserver_messagemax" => "Name ist zu lang",
    "physicalserver_messagemax" => "Name ist zu kurz",
    "physicalserver_name_valid" => "Name muss alphanumeric sein und darf folgende Charakter beinhalten: \, -, _ and space.",
    "physicalserver_fqdn_required" => "FQDN benötigt",
    "physicalserver_fqdn_valid" => "Muss ein String sein der mit Punkten getrennt ist",
    "physicalserver_customer_required" => "Benötige Kunde",
    "physicalserver_customer_not_exist" => "Ausgewählter Kunde existiert nicht",
    "physicalserver_colocation_required" => "Colocation wird benötigt",
    "physicalserver_colocation_not_exist" => "Bitte wählen Sie eine gültige Colocation aus",
    "physicalserver_core_required" => "Benötige Kern",
    "physicalserver_memory_required" => "Benötige Memory",
    "physicalserver_space_required" => "Benötige Speicherplatz",
    "physicalserver_username" => "Benutzername",
    "physicalserver_root" => "Administrator",
    "physicalserver_username_required" => "Benötige Benutzernamen",
    "physicalserver_password" => "Passwort",
    "physicalserver_password_required" => "Benötige Passwort",
    "physicalserver_permission" => "Nicht erlaubt für diesen Physical Server",
    "physicalserver_not_ovz_integrated" => "Der Physical Server ist nicht im OVZ integriert",
    "physicalserver_job_create_failed" => "Erstellen der Physical Servers fehlgeschlagen: ",
    "physicalserver_filter_all_customers" => "Alle Kunden",
    "physicalserver_filter_all_colocations" => "Alle Colocations",
    
    // View 
    "physicalserver_connect_title" => "Physical Servers OVZ Connector",
    "physicalserver_connect_connectbutton" => "Verbinden",
    "physicalserver_title" => "Physikalische Server",
    "physicalserver_ip_notfound" => "Keine IP-Objekte gefunden...",
    "physicalserver_save" => "Speichern",
    "physicalserver_cancel" => "Abbrechen",
    "physicalserver_general_title" => "Allgemeine Informationen",
    "physicalserver_general_editsettings" => "Bearbeite Einstellungen",
    "physicalserver_general_update_infos" => "Aktualisiere OVZ Infos",
    "physicalserver_general_connectovz" => "Verbinde OVZ",
    "physicalserver_confirm_removeserver" => "Sind Sie sicher, dass Sie diesen Physical Server Ilöschen wollen?",
    "physicalserver_tooltip_removeserver" => "Diesen Server entfernen",
    "physicalserver_general_customer" => "Kunde:",
    "physicalserver_general_hosttype" => "Hosttyp:",
    "physicalserver_general_colocation" => "Colocation:",
    "physicalserver_general_activdate" => "Aktivierungsdatum:",
    "physicalserver_general_description" => "Beschreibung:",
    "physicalserver_general_fqdn" => "FQDN:",
    "physicalserver_hw_title" => "HW Spezifikation",
    "physicalserver_hw_cores" => "CPU-Kerne:",
    "physicalserver_hw_ram" => "Memory (RAM):",
    "physicalserver_hw_space" => "Speicher:",
    "physicalserver_ip_title" => "IP Objekte",
    "physicalserver_ip_addobject" => "Neues IP-Objekt hinzufügen",
    "physicalserver_ip_editobject" => "IP-Objekt bearbeiten",
    "physicalserver_ip_deleteconf" => "Sind Sie sicher, dass Sie das IP-Objekt löschen wollen ?",
    "physicalserver_ip_delete" => "IP-Objekt löschen",
    "physicalserver_ip_primary" => "zum Hauptobjekt festlegen",
    "physicalserver_slide_title" => "Physikalische Server",
    "physicalservers_new_physicalserver" => "Physikalischen Server hinzufügen",
            
    // Virtual Server
    "virtualserver_all_virtualservers" => "Alle Virtual Servers",
    "virtualserver_does_not_exist" => "Der virtuelle Server existiert nicht: ",
    "virtualserver_not_ovz_integrated" => "der virtuelle Server ist nicht im OVZ integriert",
    "virtualserver_job_failed" => "Ausführen des Jobs (ovz_modify_vs) fehlgeschlagen! Fehler: ",
    "virtualserver_update_failed" => "Aktualisieren des virtuellen Servers fehlgeschlagen: .",
    "virtualserver_invalid_level" => "Ungültiger Level!",
    "virtualserver_server_not_ovz_enabled" => "Server ist nicht im OVZ aktiviert",
    "virtualserver_job_infolist_failed" => "Ausführen des Jobs (ovz_all_info) fehlgeschlagen: ",
    "virtualserver_info_success" => "Informationen erfolgreich aktualisiert",
    "virtualserver_job_create_failed" => "Erstellen des virtuellen Servers fehgeschlagen.",
    "virtualserver_job_start_failed" => "Ausführen des Jobs (ovz_start_vs) fehlgeschlagen: ",
    "virtualserver_job_start" => "Virtueller Server wurde erfolgreich gestartet",
    "virtualserver_job_stop_failed" => "Ausführen des Jobs (ovz_stop_vs) fehlgeschlagen: ",
    "virtualserver_job_stop" => "Virtueller Server wurde erfolgreich angehalten",
    "virtualserver_job_restart_failed" => "Ausführen des Jobs (ovz_restart_vs) fehlgeschlagen: ",
    "virtualserver_job_restart" => "Virtueller Server wurde erfolgreich neugestartet",
    "virtualserver_not_found" => "Virtueller Server wurde nicht gefunden.",
    "virtualserver_job_destroy_failed" => "Löschen/ Zerstören des virtuellen Servers fehlgeschlagen: ",
    "virtualserver_job_destroy" => "Virtueller Server wurde erfolgreich gelöscht/ zerstört",
    "virtualserver_job_ostemplates_failed" => "Ausführen des Jobs (ovz_get_ostemplates) fehlgeschlagen!",
    "virtualserver_job_listsnapshots_failed" => "Ausführen des Jobs (ovz_list_snapshots) fehlgeschlagen!",
    "virtualserver_snapshot_update" => "Snapshot Liste wurde erfolgreich aktualisiert",
    "virtualserver_job_switchsnapshotexec_failed" => "Ausführen des Jobs (ovz_switch_snapshot) fehlgeschlagen!",
    "virtualserver_job_switchsnapshot_failed" => "Wechseln des Snapshots auf den Server fehlgeschlagen: ",
    "virtualserver_job_createsnapshotexec_failed" => "Ausführen des Jobs (ovz_create_snapshot) fehlgeschlagen!",
    "virtualserver_job_createsnapshot_failed" => "Erstellen des Snapshots fehlgeschlagen: ",
    "virtualserver_job_deletesnapshotexec_failed" => "Ausführen des Jobs (ovz_delete_snapshot) fehlgeschlagen!",
    "virtualserver_job_createsnapshot_failed" => "Löschen des Snapshots fehlgeschlagen: ",
    "virtualserver_IP_not_valid" => "ist keine gültige IP-Adresse",
    "virtualserver_min_core" => "minimale Anzahl der Kerne ist 1",
    "virtualserver_max_core" => "Der virtuelle Server kann nicht mehr Kerne als der Host haben (Host Kerne: ",
    "virtualserver_ram_numeric" => "RAM ist nicht numerisch",
    "virtualserver_min_ram" => "Minimum RAM ist 512 MB",
    "virtualserver_max_ram" => "Der virtuelle Server kann nicht mehr RAM haben als der Host (Host Memory: ",
    "virtualserver_space_numeric" => "Speicher ist nicht numerisch",
    "virtualserver_min_space" => "Minimum Speicher ist 20 GB",
    "virtualserver_max_space" => "Der virtuelle Server kann nicht mehr Speicher als der Host (Host Speicher: ",
    "virtualserver_job_modifysnapshotexec_failed" => "Ausführen des Jobs (ovz_modify_vs) fehlgeschlagen: ",
    "virtualserver_job_modifyvs" => "Änderung am virtuellen Server erfolgreich",
    "virtualserver_name" => "Name",
    "virtualserver_myserver" => "Mein Server",
    "virtualserver_choose_customer" => "Bitte wählen Sie einen Kunden aus",
    "virtualserver_customer" => "Kunde",
    "virtualserver_choose_physicalserver" => "Bitte wählen Sie einen physischen Server aus",
    "virtualserver_physicalserver" => "Physical Server",
    "virtualserver_cores" => "Kerne",
    "virtualserver_cores_example" => "Verfügbare Kerne  (z.B. 4)",
    "virtualserver_memory" => "Memory",
    "virtualserver_memory_example" => "Verfügbare Memory in MB (z.B. 2048)",
    "virtualserver_space" => "Speicher",
    "virtualserver_space_example" => "Verfügbarer Speicher in MB (e.g. 102400)",
    "virtualserver_activdate" => "Aktivierungsdatum",
    "virtualserver_description" => "Beschreibung",
    "virtualserver_description_info" => "Zusätzliche Informationen zu diesem Server",
    "virtualserver_rootpassword" => "Administrator Passwort",
    "virtualserver_choose_ostemplate" => "Bitte wählen Sie ein OS Template aus",
    "virtualserver_name_required" => "Benötige Namen",
    "virtualserver_namemax" => "Name ist zu lang",
    "virtualserver_namemin" => "Name ist zu kurz",
    "virtualserver_name_valid" => "Name muss numerisch sein und darf folgende Zeichen enthalten \, -, _ and space.",
    "virtualserver_fqdn_valid" => "Muss ein String sein, durch Punkte getrennt",
    "virtualserver_customer_required" => "Benötige Kunden",
    "virtualserver_customer_not_exist" => "Ausgewählter Kunde existiert nicht",
    "virtualserver_physicalserver_required" => "Benötige physischen Server",
    "virtualserver_core_required" => "Benötige Kerne",
    "virtualserver_memory_required" => "Benötige Memory",
    "virtualserver_space_required" => "Benötige Speicher",
    "virtualserver_password_required" => "Benötige Passwort",
    "virtualserver_passwordmin" => "Passwort ist zu kurz. Minumum 8 Zeichen",
    "virtualserver_passwordmax" => "Password ist zu lang. Maximum 12 Zeichen",
    "virtualserver_passwordregex" => "Password darf nur Zahlen, Buchstaben und diese Zeichen beinhalten -_.",
    "virtualserver_ostemplate_required" => "Benötige OS Template",
    "virtualserver_hostname" => "Hostname",
    "virtualserver_hostname_valid" => "Muss ein String sein, durch Punkte getrennt",
    "virtualserver_memory_specify" => "Bitte deklarieren Sie ob es GB oder MB sind",
    "virtualserver_discspace" => "Discspeicher",
    "virtualserver_discspace_example" => "Verfügbarer Speicher in GB  (e.g. 100)",
    "virtualserver_discspace_required" => "Benötige Diskspeicher",
    "virtualserver_discspace_specify" => "Bitte deklarieren Sie ob es TB,GB or MB sind",
    "virtualserver_dnsserver" => "DNS-Server",
    "virtualserver_startonboot" => "Start on boot",
    "virtualserver_startonboot_info" => "Start on boot kann 0 or 1 sein",
    "virtualserver_snapshotname" => "Snapshotname",
    "virtualserver_snapshotname_replica" => "Der Name darf replica nicht enthalten.",
    "virtualserver_snapshotname_required" => "Name muss numerisch sein und darf folgende Zechen enthalten -_().!? inklusive Leerschlag",
    "virtualserver_description_valid" => "Beschreibung darf nicht länder als 250 Zeichen sein",
    "virtualserver_modify_job_failed" => "Modifizieren des Virtuellen Servers fehlgeschlagen: ",
    "virtualserver_change_root_password" => "Root Passwort ändern",
    "virtualserver_root_password" => "Neues Passwort",
    "virtualserver_confirm_root_password" => "Passwort bestätigen",
    "virtualserver_password_confirm_match" => "Die Passwörter stimmen nicht überein",
    "virtualserver_change_root_password_successful" => "Das Root Passwort wurde erfolgreich geändert",
    "virtualserver_change_root_password_failed" => "Das Root Passwort konnte nicht geändert werden: ",
    "virtualserver_view_support_job_message" => "Sind Sie sicher, dass Sie den Support Job auf allen Virtual Server ausführen wollen?",
    "virtualserver_support_task_successful" => "Support Job erfolgreich ausgeführt",
    //View
    "virtualserver_title" => " Virtuelle Server",
    "virtualserver_view_new" => "Neu",
    "virtualserver_view_independentsys" => "Unabhängiges System",
    "virtualserver_view_container" => "Kontainer (CT)",
    "virtualserver_view_vm" => "Virtuelle Maschine (VM)",
    "virtualserver_view_vm_beta" => "(funktioniert nicht in der Beta!)",
    "virtualserver_snapshot" => "Snapshots",
    "virtualserver_save" => "Speichern",
    "virtualserver_cancel" => "Abbrechen",
    "virtualserver_snapshot_refresh" => "Snapshots aktualisieren",
    "virtualserver_snapshot_create" => "Neuen Snapshot erstellen",
    "virtualserver_snapshot_created" => "Snapshot wurde erfolgreich erstellt",
    "virtualserver_snapshot_run" => "Jetzt",
    "virtualserver_snapshot_switchinfo" => "Sind Sie sicher, dass Sie zu diesem Snapshot wechseln wollen ?",
    "virtualserver_snapshot_switch" => "Zu diesem Snapshot wechseln",
    "virtualserver_snapshot_switched" => "Erfolgreich zu Snapshot gewechselt",
    "virtualserver_snapshot_deleteinfo" => "Sind Sie sicher, dass Sie diesen Snapshot löschen wollen?",
    "virtualserver_snapshot_delete" => "Snapshot löschen",
    "virtualserver_snapshot_deleted" => "Snapshot wurde erfolgreich gelöscht",
    "virtualserver_snapshot_new" => "Neuen Snapshot erstellen",
    "virtualserver_ipobject" => "IP-Objekte",
    "virtualserver_ip_newobject" => "Neues IP-Objekt hinzufügen",
    "virtualserver_noipobject" => "Keine IP-Objekte gefunden...",
    "virtualserver_ip_edit" => "IP-Objekt bearbeiten",
    "virtualserver_ip_deleteinfo" => "Sind Sie sicher, dass Sie das IP-Objekt löschen wollen ?",
    "virtualserver_ip_delete" => "IP-Objekt löschen",
    "virtualserver_ip_primary" => "IP-Objekt als Hauptadresse setzen",
    "virtualserver_hwspec" => "HW Spezifikation",
    "virtualserver_hwspec_cpu" => "CPU-Kerne: ",
    "virtualserver_hwspec_memory" => "Memory (RAM): ",
    "virtualserver_hwspec_space" => "Speicher",
    "virtualserver_generalinfo" => "Allgemeine Information",
    "virtualserver_general_start" => "Start",
    "virtualserver_general_stop" => "Stop",
    "virtualserver_general_restart" => "Neustart",
    "virtualserver_general_editovz" => "OVZ Einstellungen bearbeiten",
    "virtualserver_general_updateovz" => "OVZ Informationen aktualisieren",
    "virtualserver_general_updatestats" => "OVZ Statistik aktialisieren",
    "virtualserver_general_setpwd" => "Neues Passwort setzen",
    "virtualserver_general_deleteinfo" => "Sind Sie sicher, dass Sie das Item löschen wollen ?",
    "virtualserver_general_delete" => "Virtuellen Server löschen",
    "virtualserver_general_customer" => "Kunde: ",
    "virtualserver_general_fqdn" => "FQDN: ",
    "virtualserver_general_uuid" => "OVZ UUID: ",
    "virtualserver_general_physicalserver" => "Physikalischer Server: ",
    "virtualserver_general_activdate" => "Aktivierungsdatum: ",
    "virtualserver_general_state" => "Status: ",
    "virtualserver_general_description" => "Beschreibung: ",
    "virtualserver_filter_all_customers" => "Alle Kunden",
    "virtualserver_filter_all_physical_servers" => "Alle physische Server",
    "virtualserver_no_physicalserver_found" => "Keinen Physikalischen Server gefunden, CTs können nicht erstellt werden",
    "virtualserver_save_replica_slave_failed" => "Speichern des Replika-Slaves fehlgeschlagen",
    "virtualserver_job_sync_replica_failed" => "Synchronisation der Replikas fehlgeschlagen",
    "virtualserver_update_replica_master_failed" => "Aktualisieren des Replika-Masters fehlgeschlagen",
    "virtualserver_replica_sync_run_in_background" => "Synchronisation der Replikationen läuft im Hintergrund",
    "virtualserver_isnot_replica_master" => "Der Virtuelle Server ist nicht Replika-Master",
    "virtualserver_replica_running_in_background" => "Replikation läuft im Hintergrund",
    "virtualserver_replica_master_not_stopped" => "Replika-Master ist nicht gestoppt",
    "virtualserver_replica_slave_not_stopped" => "Replika-Slave ist nicht gestoppt",
    'virtualserver_replica_failover_success' => "Replika Failover erfolgreich",
    "virtualserver_server_not_replica_master" => "Der Server ist nicht Replika-Master",
    "virtualserver_server_not_replica_slave" => "Der Server ist nicht Replika-Slave",
    "virtualserver_replica_master_update_failed" => "Aktualisieren des Replika-Masters fehlgeschlagen",
    "virtualserver_replica_slave_update_failed" => "Aktualisieren des Replika-Slaves fehlgeschlagen",
    "virtualserver_replica_switched_off" => "Replika wurde ausgeschaltet",
    "virtualserver_replica" => "Replikation",
    "virtualserver_replica_tooltip_activate" => "Replikation hinzufügen",
    "virtualserver_replica_tooltip_run" => "Replikation ausführen",
    "virtualserver_replica_confirm_failover" => "Failover bestätigen",
    "virtualserver_replica_tooltip_failover" => "Failover",
    "virtualserver_replica_confirm_delete" => "Löschvorgang bestätigen",
    "virtualserver_replica_tooltip_delete" => "Löschen",
    "virtualserver_replica_not_activated" => "Replikation ist nicht aktiviert",
    "virtualserver_replica_status" => "Status: ",
    "virtualserver_replica_slave" => "Slave: ",
    "virtualserver_replica_host" => "Host: ",
    "virtualserver_replica_lastrun" => "Letzte Laufzeit: ",
    "virtualserver_replica_nextrun" => "Nächste Laufzeit: ",
    "virtualserver_cancel" => "Abbrechen",
    "virtualserver_replica_" => "Replikation starten",
    "virtualservers_show_pdf" => "PDF generieren",
    "virtualservers_datasheet" => "Datenblatt Virtual Server",
    "virtualservers_servrname" => "Servername: ",
    "virtualservers_general_info" => "Allgemeine Information",
    "virtualservers_activ_date" => "Einschaltdatum: ",
    "virtualservers_fqdn" => "FQDN: ",
    "virtualservers_server_type" => "Server-Type: ",
    "virtualservers_pricepermonth" => "Preis pro Monat (exkl. MwSt): ",
    "virtualservers_system_specification" => "Systemspezifikation",
    "virtualservers_system" => "System: ",
    "virtualservers_os_system" => "Betriebssystem: ",
    "virtualservers_cores" => "Kerne: ",
    "virtualservers_memory" => "Arbeitsspeicher: ",
    "virtualservers_discspace" => "Festplattenspeicher: ",
    "virtualservers_description" => "Beschreibung: ",
    "virtualservers_ip_adress" => "IP Adressen",
    "virtualservers_comment" => "Kommentar",

    // Monitoring
    "monitoring_mon_behavior_not_implements_interface" => "MonBehavior implementiert nicht das MonBehaviorInterface.",
    "monitoring_mon_server_not_implements_interface" => "MonServer implementiert nicht das MonServerInterface.",
    "monitoring_parent_cannot_execute_jobs" => "Auf dem Parent-Object ist es nicht möglich Jobs auszuführen.",
    "monitoring_healjob_failed" => "Ausführung des Healjobs fehlgeschlagen.",
    "monitoring_healjob_not_executed_error" => "Automatischer Healjob konnte nicht unverzüglich gesendet werden. Kann passieren, wenn der Host nicht erreichbar ist. Wird vom HealingSystem deaktiviert, damit er nicht später versehentlich unnötig ausgeführt wird.",
    "monitoring_healing_executed" => "Heilungsmassnahmen wurden ausgeführt.",
    "monitoring_monuptimesgenerator_computefailed" => "Berechnen der Uptime fehlgeschlagen: ",
    "monitoring_monlocaldailylogsgenerator_computefailed" => "Berechnen des Durchschnitts fehlgeschlagen: ",
    "monitoring_monlocaldailylogsgenerator_delete_old_daily_log" => "Das alte Daily Log wurde gelöscht: ",
    "monitoring_monjobs_login_not_from_customer" => "Ausgewähltes Login entspricht nicht dem Kunden des Physical Server",
    "monitoring_mon_behavior_could_not_instantiate_valuestatus" => "Es konnte kein ValueStatus Objekt instanziert werden (evt. fehlen die Infos im Statistics Array)",
    "monitoring_allinfoupdater_mark_failed" => "Der Job wurde als fehlerhaft markiert weil er nicht unverzüglich vom Monitoring gesendet werden konnte.",
    // MonJobs
    "monitoring_monjobs_montype_remote_expected" => "Diese Methode funktioniert nur für mon_type 'remote'.",
    "monitoring_monjobs_montype_local_expected" => "Diese Methode funktioniert nur für mon_type 'local'.",
    // MonLocalJobs        
    "monitoring_monlocaljobs_no_valid_unit" => "Das angegebene Einheit-Argument ist nicht erlaub.",
    "monitoring_monlocaljobs_end_before_start" => "Das angegebene End-Datum darf nicht vor dem Start-Datum sein.",
    "monitoring_monlocaljobs_title" => "Local MonJobs",
    "monitoring_monlocaljobs_notfound" => "Keine Local MonJobs gefunden...",
    "monitoring_monlocaljobs_add" => "Local MonJob hinzufügen",
    "monitoring_monlocaljobs_choose_behavior" => "Bitte wählen Sie eine Monitoring Art aus",
    "monitoring_monlocaljobs_behavior" => "Art des Monitorings",
    "monitoring_monlocaljobs_period" => "Intervall (Pause zwischen Ausführung)",
    "monitoring_monlocaljobs_period_placeholder" => "Anzahl Minuten, z.B. 5",
    "monitoring_monlocaljobs_alarm_period" => "Alarm Intervall (Pause zwischen Benachrichtigungen)",
    "monitoring_monlocaljobs_alarm_period_placeholder" => "Anzahl Minuten, z.B. 15",
    "monitoring_monlocaljobs_message_contacts" => "Benachrichtigungs-Kontakt",
    "monitoring_monlocaljobs_alarm_contacts" => "Alarmierungs-Kontakt",
    "monitoring_monlocaljobs_add_successful" => "Local MonJob wurde erfolgreich hinzugefügt",
    "monitoring_monlocaljobs_add_failed" => "Local MonJob konnte nicht hinzugefügt werden: ",
    "monitoring_monlocaljobs_behavior_required" => "Monitoring Art ist erforderlich",
    "monitoring_monlocaljobs_period_required" => "Intervall ist erforderlich",
    "monitoring_monlocaljobs_period_digit" => "Intervall muss eine Zahl sein",
    "monitoring_monlocaljobs_alarm_period_required" => "Alarm Intervall ist erforderlich",
    "monitoring_monlocaljobs_alarm_period_digit" => "Alarm Intervall muss eine Zahl sein",
    "monitoring_monlocaljobs_message_contacts_required" => "Mindestens ein Benachrichtigungs-Kontak ist erforderlich",
    "monitoring_monlocaljobs_alarm_contacts_required" => "Mindestens ein Alarmierungs-Kontakt ist erforderlich",
    "monitoring_monlocaljobs_statistics_timestamp_to_old" => "Statistiken sind zu alt. Der Job wird nicht geloggt bzw. ausgeführt in diesem Durchgang. Beim nächsten Start über Crontab wird er wieder gestartet.",
    // MonRemoteJobs
    "monitoring_monremotejobs_title" => "Remote MonJobs",
    "monitoring_monremotejobs_notfound" => "Keine Remote MonJobs gefunden...",
    "monitoring_monremotejobs_add" => "Remote MonJob hinzufügen",
    "monitoring_monremotejobs_choose_behavior" => "Bitte wählen Sie eine Monitoring Art aus",
    "monitoring_monremotejobs_behavior" => "Art des Monitorings",
    "monitoring_monremotejobs_period" => "Intervall (Pause zwischen Ausführung)",
    "monitoring_monremotejobs_period_placeholder" => "Anzahl Minuten, z.B. 5",
    "monitoring_monremotejobs_alarm_period" => "Alarm Intervall (Pause zwischen Benachrichtigungen)",
    "monitoring_monremotejobs_alarm_period_placeholder" => "Anzahl Minuten, z.B. 15",
    "monitoring_monremotejobs_message_contacts" => "Benachrichtigungs-Kontakt",
    "monitoring_monremotejobs_alarm_contacts" => "Alarmierungs-Kontakt",
    "monitoring_monremotejobs_add_successful" => "Remote MonJob wurde erfolgreich hinzugefügt",
    "monitoring_monremotejobs_add_failed" => "Remote MonJob konnte nicht hinzugefügt werden: ",
    "monitoring_monremotejobs_no_uptime" => "Keine Uptime verfügbar",

];
