<?php

return [
    "modelbase_rntforest_ovz_models_virtualservers" => "Virtual Servers",
    "modelbase_rntforest_ovz_models_physicalservers" => "Physical Servers",
    
    // Colocations
    "colocation_all_colocations" => "All Colocations",
    "colocations_invalid_level" => "invalid level!",
    "colocations_choose_customer" => "Please, choose a customer...",
    "colocations_customer" => "customer",
    "colocations_name" => "name",
    "colocations_colocationname" => "My colocation name",
    "colocations_description" => "Description",
    "colocations_description_info" => "some additional information to this colocation...",
    "colocations_location" => "location",
    "colocations_location_info" => "My location",
    "colocations_activ_date" => "activation date",
    "colocations_name_required" => "name required",
    "colocations_namemax" => "name is to long",
    "colocations_namemin" => "name is to short",
    "colocations_name_valid" => "Name must be alphanumeric and may contain the characters \, -, _ and space.",
    "colocations_customer_required" => "customer required",
    "colocations_customer_not_exist" => "Selected customer doesn't exist",
    "colocations_location_max" => "location is to long",
    "colocations_location_max" => "location is to short",
    "colocations_locaton_valid" => "location must be alphanumeric and may contain the characters \, -, _ and space.",
    //View
    "colocations_title" => " Colocations",
    "colocations_view_physicalserver" => "Physical Servers",
    "colocations_view_nophysicalserver" => "No physical server found...",
    "colocations_view_ipobjects" => "IP Objects",
    "colocations_view_newipobject" => "Add new IP object",
    "colocations_view_noipobjects" => "No IP objects found...",
    "colocations_view_editipobject" => "Edit IP object",
    "colocations_view_delmessage" => "Are you sure you want to delete this item ?",
    "colocations_view_delete" => "Delete IP object",
    "colocations_generalinfo" => "General Information",
    "colocations_editovz" => "Edit OVZ settings",
    "colocations_delcolocation" => "Delete colocation",
    "colocations_view_customer" => "Customer",
    "colocations_view_activdate" => "Activation date",
    "colocations_view_description" => "Description",
    "colocations_title" => " Colocations",
    "colocations_save" => "Save",
    "colocations_cancel" => "Cancel",
    "colocations_genpdf" => "Generate IP objects PDF",
    "colocations_ipobjects" => "IP objects",
    "colocations_pdf_no_ipobjects" => "No IP reservations in the colocation found", 
    "colocations_createpdf" => "Create PDF: IP overview",
    "colocations_new_colocation" => "Add new colocation",
     
    // IP Objects
    "ipobjects_address_is_now_main" => "IP Address %address% is now main.",
    "ipobjects_item_not_found" => "item was not found!",
    "ipobjects_item_not_exist" => "item does not exist!",
    "ipobjects_ip_success" => "IP Adress was updated successfully",
    "ipobjects_ip_not_found" => "IP object was not found !",
    "ipobjects_ip_conf_failed" => "Configure IP on virtual server failed: ",
    "ipobjects_ip_delete_success" => "IP Object was deleted successfully",
    "ipobjects_ip_adress" => "IP object must be an adress",
    "ipobjects_ip_assigned" => "IP Object must be assigned",
    "ipobjects_ip_update_failed" => "Update IP Object failed!",
    "ipobjects_ip" => "IP adress",
    "ipobjects_ip_addition" => "Additional IP Value",
    "ipobjects_ip_additioninfo" => "Empty | Subnetmask if IP Address | End IP Address if Range | Prefix if Subnet",
    "ipobjects_allocated" => "Allocated",
    "ipobjects_ismain" => "Is main",
    "ipobjects_isnotmain" => "Is not main",
    "ipobjects_ip_main" => "Main IP",
    "ipobjects_comment" => "comment",
    "ipobjects_commentinfo" => "Some additional information to IP Object",
    "ipobjects_dco_submit" => "No DCO submitted.",
    "ipobjects_ip_not_valid" => "Not a valid IP Address",
    "ipobjects_secont_value_valid" => "Not a valid second value",
    "ipobjects_assigned_ip" => "Assigned IPs can't be range or net",
    "ipobjects_no_reservation" => "No reservations found.",
    "ipobjects_ip_notpart_reservation" => "IP is not part of an existing reservation.",
    "ipobjects_ip_already_exists" => "IP already exists.",
    "ipobjects_ip_required" => "IP adress is required",
    "ipobjects_ip_valid" => "Wrong characters in IP Address.",
    "ipobjects_second-value_check" => "Wrong characters in second Value.",
    "ipobjects_main" => "Main can only be 0 or 1.",
    "ipobjects_allocated_value" => "Please choose a correct Allocated Value.",
    "ipobjects_comment_length" => "Comment is too long (max. 50 characters)",
    "ipobjects_unexpected_type" => "Unexpected Type!",
    //View
    "ipobjects_edit_title" => "IP Objects",
    "ipobjects_reservations" => "Reservations",
    "ipobjects_edit_cancel" => "Cancel",
    "ipobjects_edit_save" => "Save",
      
    // Physical Servers
    "physicalserver_all_physicalservers" => "All Physical Servers",
    "physicalserver_does_not_exist" => "Physical Server does not exist: ",
    "physicalserver_not_ovz_enabled" => "Server ist not OVZ enabled!",
    "physicalserver_job_failed" => "Executing the following job failed: ",
    "physicalserver_update_failed" => "Updating the server failed: ",
    "physicalserver_update_success" => "Informations successfully updated",
    "physicalserver_remove_server_first" => "Please remove virtual server first !",
    "physicalserver_not_found" => "Physical server not found !",
    "physicalserver_connection_prepare_title" => "Prepare instructions",
    "physicalserver_connection_prepare_instructions" => "Before connecting to the OpenVZ-Server there must be executed several commands. With this commands the system is updated and some needed software packages will be installed.",
    "physicalserver_connection_success" => "Connection successfully established to: ",
    "physicalserver_connection_restart" => "It's strongly recommended to restart the server after connecting!",
    "physicalserver_connection_failed" => "Connection to OVZ failed: ",
    "physicalserver_name" => "Name",
    "physicalserver_myserver" => "My Server",
    "physicalserver_fqdn" => "FQDN",
    "physicalserver_hostdomaintld" => "host.domain.tld",
    "physicalserver_choose_customer" => "Please, choose a customer...",
    "physicalserver_customer" => "Customer",
    "physicalserver_choose_colocation" => "Please, choose the colocation...",
    "physicalserver_colocation" => "Colocation",
    "physicalserver_cores" => "Cores",
    "physicalserver_cores_available" => "available cores  (e.g. 4)",
    "physicalserver_memory" => "Memory",
    "physicalserver_memory_available" => "Available memory in MB (e.g. 2048)",
    "physicalserver_space" => "Space",
    "physicalserver_space_available" => "available space in MB (e.g. 102400)",
    "physicalserver_activ_date" => "Activation date",
    "physicalserver_discription" => "Description",
    "physicalserver_discription_info" => "some additional information to this server...",
    "physicalserver_name_required" => "name is required",
    "physicalserver_messagemax" => "name too long",
    "physicalserver_messagemax" => "name too short",
    "physicalserver_name_valid" => "Name must be alphanumeric and may contain the characters \, -, _ and space.",
    "physicalserver_fqdn_required" => "FQDN is required",
    "physicalserver_fqdn_valid" => "must be a String separated by points",
    "physicalserver_customer_required" => "customer is required",
    "physicalserver_customer_not_exist" => "selected customer doesn't exist",
    "physicalserver_colocation_required" => "Colocation is required",
    "physicalserver_colocation_not_exist" => "Please select an existing Colocation",
    "physicalserver_core_required" => "core is required",
    "physicalserver_memory_required" => "memory is required",
    "physicalserver_space_required" => "space is required",
    "physicalserver_username" => "username",
    "physicalserver_root" => "root",
    "physicalserver_username_required" => "username is required",
    "physicalserver_password" => "password",
    "physicalserver_password_required" => "password is required",
    "physicalserver_permission" => "Not allowed for this Physical Server",
    "physicalserver_not_ovz_integrated" => "Physical Server is not OVZ integrated.",
    "physicalserver_job_create_failed" => "Creating Physical Server failed: ",
    "physicalserver_filter_all_customers" => "All Customers",
    "physicalserver_filter_all_colocations" => "All Colocations",
    // View 
    "physicalserver_connect_title" => "Physical Servers OVZ Connector",
    "physicalserver_connect_connectbutton" => "Connect",
    "physicalserver_title" => "Physical Server",
    "physicalserver_ip_notfound" => "No IP Objects found...",
    "physicalserver_save" => "Save",
    "physicalserver_cancel" => "Cancel",    
    "physicalserver_general_title" => "General Information",
    "physicalserver_general_editsettings" => "Edit settings",
    "physicalserver_general_update_infos" => "Update OVZ infos",
    "physicalserver_general_connectovz" => "Connect OVZ",
    "physicalserver_confirm_removeserver" => "Are you sure you want to delete this Item",
    "physicalserver_tooltip_removeserver" => "Remove this server",
    "physicalserver_general_customer" => "Customer:",
    "physicalserver_general_hosttype" => "Hosttype:",
    "physicalserver_general_colocation" => "Colocation:",
    "physicalserver_general_activdate" => "Activation date:",
    "physicalserver_general_description" => "Description:",
    "physicalserver_general_fqdn" => "FQDN:",
    "physicalserver_hw_title" => "HW Specifications",
    "physicalserver_hw_cores" => "CPU-Cores:",
    "physicalserver_hw_ram" => "Memory (RAM):",
    "physicalserver_hw_space" => "Space:",
    "physicalserver_ip_title" => "IP Objects",
    "physicalserver_ip_addobject" => "Add new IP-objects",
    "physicalserver_ip_editobject" => "Edit IP object",
    "physicalserver_ip_deleteconf" => "Are you sure you want to delete this IP object ?",
    "physicalserver_ip_delete" => "Delete IP object",
    "physicalserver_ip_primary" => "Make IP object to primary",
    "physicalserver_slide_title" => "Physical Servers",
    "physicalservers_new_physicalserver" => "Add Physical Server",
 
    // Virtual Server
    "virtualserver_all_virtualservers" => "All Virtual Servers",
    "virtualserver_does_not_exist" => "Virtual server does not exist: ",
    "virtualserver_not_ovz_integrated" => "Virtual server is not OVZ integrated",
    "virtualserver_job_failed" => "Job (ovz_modify_vs) executions failed! Error: ",
    "virtualserver_update_failed" => "Updating the virual server failed: .",
    "virtualserver_invalid_level" => "invalid level!",
    "virtualserver_server_not_ovz_enabled" => "Server is not OVZ enabled!",
    "virtualserver_job_infolist_failed" => "Job (ovz_all_info) executions failed: ",
    "virtualserver_info_success" => "Informations successfully updated",
    "virtualserver_job_create_failed" => "Create virtual server failed.",
    "virtualserver_job_start_failed" => "Job (ovz_start_vs) executions failed: ",
    "virtualserver_job_start" => "Started virtual server successfully",
    "virtualserver_job_stop_failed" => "Job (ovz_stop_vs) executions failed: ",
    "virtualserver_job_stop" => "Stopped virtual server successfully",
    "virtualserver_job_restart_failed" => "Job (ovz_restart_vs) executions failed: ",
    "virtualserver_job_restart" => "Restarted virtual server successfully",
    "virtualserver_not_found" => "Virtual server not found.",
    "virtualserver_job_destroy_failed" => "Deleting/ Destroying Virtual server failed: ",
    "virtualserver_job_destroy" => "Virtual server deleted/ destroyed sucessfully.",
    "virtualserver_job_ostemplates_failed" => "Job (ovz_get_ostemplates) executions failed!",
    "virtualserver_job_listsnapshots_failed" => "Job (ovz_list_snapshots) executions failed!",
    "virtualserver_snapshot_update" => "Snapshotlist successfully updated",
    "virtualserver_job_switchsnapshotexec_failed" => "Job (ovz_switch_snapshot) executions failed!",
    "virtualserver_job_switchsnapshot_failed" => "Switching snapshot on server failed: ",
    "virtualserver_job_createsnapshotexec_failed" => "Job (ovz_create_snapshot) executions failed!",
    "virtualserver_job_createsnapshot_failed" => "Create snapshot on server failed: ",
    "virtualserver_job_deletesnapshotexec_failed" => "Job (ovz_delete_snapshot) executions failed!",
    "virtualserver_job_createsnapshot_failed" => "Deleting snapshot on server failed: ",
    "virtualserver_IP_not_valid" => " is not a valid IP address",
    "virtualserver_min_core" => "minimum core is 1",
    "virtualserver_max_core" => "Virtual server can not have more cores than the host (host cores: ",
    "virtualserver_ram_numeric" => "RAM is nor numeric",
    "virtualserver_min_ram" => "Minimum RAM is 512 MB",
    "virtualserver_max_ram" => "Virtual Server can not have more memory than the host (host memory: ",
    "virtualserver_space_numeric" => "Space is not numeric",
    "virtualserver_min_space" => "Minimum space is 20 GB",
    "virtualserver_max_space" => "Virtual Server can not use more space than the host (host space: ",
    "virtualserver_job_modifysnapshotexec_failed" => "Job (ovz_modify_vs) executions failed: ",
    "virtualserver_job_modifyvs" => "Modifying virtual server successfully",
    "virtualserver_name" => "Name",
    "virtualserver_myserver" => "My Server",
    "virtualserver_choose_customer" => "Please choose a customer...",
    "virtualserver_customer" => "customer",
    "virtualserver_choose_physicalserver" => "Please choose a physical server...",
    "virtualserver_physicalserver" => "Physical servers",
    "virtualserver_cores" => "Cores",
    "virtualserver_cores_example" => "available cores  (e.g. 4)",
    "virtualserver_memory" => "Memory",
    "virtualserver_memory_example" => "available memory in MB (e.g. 2048)",
    "virtualserver_space" => "Space",
    "virtualserver_space_example" => "available space in MB (e.g. 102400)",
    "virtualserver_activdate" => "Activation date",
    "virtualserver_description" => "description",
    "virtualserver_description_info" => "some additional information to this server...",
    "virtualserver_rootpassword" => "Root password",
    "virtualserver_choose_ostemplate" => "Please choose a OS template",
    "virtualserver_name_required" => "name is required",
    "virtualserver_namemax" => "name is to long",
    "virtualserver_namemin" => "name is to short",
    "virtualserver_name_valid" => "Name must be alphanumeric and may contain the characters \, -, _ and space.",
    "virtualserver_fqdn_valid" => "must be a String separated by points",
    "virtualserver_customer_required" => "customer is required",
    "virtualserver_physicalserver_required" => "Physical server is required",
    "virtualserver_core_required" => "core is required",
    "virtualserver_memory_required" => "memory is required",
    "virtualserver_space_required" => "space is required",
    "virtualserver_password_required" => "password required",
    "virtualserver_passwordmin" => "Password is too short. Minumum 8 characters",
    "virtualserver_passwordmax" => "Password is too long. Maximum 12 characters",
    "virtualserver_passwordregex" => "Password may only contain numbers, characters and -_.",
    "virtualserver_ostemplate_required" => "OS Template required",
    "virtualserver_hostname" => "hostname",
    "virtualserver_hostname_valid" => "must be a string separated by points",
    "virtualserver_memory_specify" => "Only numbers and a dot are allowed. Also it has to be specified if it's GB or MB",
    "virtualserver_discspace" => "discspace",
    "virtualserver_discspace_example" => "available space in GB  (e.g. 100)",
    "virtualserver_discspace_required" => "Diskspace is required",
    "virtualserver_discspace_specify" => "Only numbers and a dot are allowed. Also it has to be specified if it's TB,GB or MB",
    "virtualserver_dnsserver" => "DNS-Server",
    "virtualserver_startonboot" => "Start on boot",
    "virtualserver_startonboot_info" => "Start on boot can either be 0 or 1",
    "virtualserver_snapshotname" => "Snapshotname",
    "virtualserver_snapshotname_replica" => "Name must not contain replica.",
    "virtualserver_snapshotname_required" => "Name must be alphanumeric and may contain the characters -_().!? and space.",
    "virtualserver_description_valid" => "Description mus not longer be than 250 characters",
    "virtualserver_modify_job_failed" => "Modifying virtual server failed: ",
    "virtualserver_change_root_password" => "Change root password",
    "virtualserver_root_password" => "New password",
    "virtualserver_confirm_root_password" => "Confirm password",
    "virtualserver_password_confirm_match" => "The passwords do not match",
    "virtualserver_change_root_password_successful" => "The root password has successfully been changed",
    "virtualserver_change_root_password_failed" => "The root password could not be changed: ",
    "virtualserver_view_support_job_message" => "Are you sure you want to execute the support job on all virtual servers?",
    "virtualserver_support_task_successful" => "Support Job executed successfully",
    //View
    "virtualserver_title" => "Virtual Servers",
    "virtualserver_view_new" => "New",
    "virtualserver_view_independentsys" => "Independent System",
    "virtualserver_view_container" => "Container (CT)",
    "virtualserver_view_vm" => "Virtual Machine (VM)",
    "virtualserver_view_vm_beta" => "(will not work in Beta!)",
    "virtualserver_snapshot" => "Snapshots",
    "virtualserver_save" => "Save",
    "virtualserver_cancel" => "Cancel",
    "virtualserver_snapshot_refresh" => "Refresh Snapshots",
    "virtualserver_snapshot_create" => "Create a new Snapshot",
    "virtualserver_snapshot_created" => "Snapshot successfully created",
    "virtualserver_snapshot_run" => "Now",
    "virtualserver_snapshot_switchinfo" => "Are you sure you want to switch to this Snapshot ?",
    "virtualserver_snapshot_switch" => "Switch to this Snapshot",
    "virtualserver_snapshot_switched" => "Successfully switched to Snapshot",
    "virtualserver_snapshot_deleteinfo" => "Are you sure you want to delete this snapshot?",
    "virtualserver_snapshot_delete" => "Delete Snapshot",
    "virtualserver_snapshot_deleted" => "Snapshot successfully deleted",
    "virtualserver_snapshot_new" => "Create new Snapshot",
    "virtualserver_ipobject" => "IP Objects",
    "virtualserver_ip_newobject" => "Add new IP object",
    "virtualserver_noipobject" => "No IP objects found...",
    "virtualserver_ip_edit" => "Edit IP object",
    "virtualserver_ip_deleteinfo" => "Are you sure you want to delete this IP object ?",
    "virtualserver_ip_delete" => "Delete IP object",
    "virtualserver_ip_primary" => "Make IP object to primary",
    "virtualserver_hwspec" => "HW Specifications",
    "virtualserver_hwspec_cpu" => "CPU-Cores: ",
    "virtualserver_hwspec_memory" => "Memory (RAM): ",
    "virtualserver_hwspec_space" => "Space",
    "virtualserver_generalinfo" => "General Information",
    "virtualserver_general_start" => "Start",
    "virtualserver_general_stop" => "Stop",
    "virtualserver_general_restart" => "Restart",
    "virtualserver_general_editovz" => "Edit OVZ settings",
    "virtualserver_general_updateovz" => "Update OVZ informations",
    "virtualserver_general_updatestats" => "Update OVZ statistics",
    "virtualserver_general_setpwd" => "Set new password",
    "virtualserver_general_deleteinfo" => "Are you sure you want to delete this item",
    "virtualserver_general_delete" => "Delete virtual server",
    "virtualserver_general_customer" => "Customer: ",
    "virtualserver_general_fqdn" => "FQDN: ",
    "virtualserver_general_uuid" => "OVZ UUID: ",
    "virtualserver_general_physicalserver" => "Physical server: ",
    "virtualserver_general_activdate" => "Activation date: ",
    "virtualserver_general_state" => "State: ",
    "virtualserver_general_description" => "Description",
    "virtualserver_filter_all_customers" => "All customers",
    "virtualserver_filter_all_physical_servers" => "All physical servers",
    "virtualserver_no_physicalserver_found" => "No Physical Server found, CTs can't be created",
    "virtualserver_save_replica_slave_failed" => "Saving repica slave failed",
    "virtualserver_job_sync_replica_failed" => "Synchronisation of replicas failed",
    "virtualserver_update_replica_master_failed" => "Updating replica master failed",
    "virtualserver_replica_sync_run_in_background" => "Replica synchronisation is running in background",
    "virtualserver_isnot_replica_master" => "Virtual server is not replica master",
    "virtualserver_replica_running_in_background" => "Replica is running in background",
    "virtualserver_replica_master_not_stopped" => "Replica master is not stopped",
    "virtualserver_replica_slave_not_stopped" => "Replica slave is not stopped",
    'virtualserver_replica_failover_success' => "Replica failover successfull",
    "virtualserver_server_not_replica_master" => "Server is not replica master",
    "virtualserver_server_not_replica_slave" => "Server is not replica slave",
    "virtualserver_replica_master_update_failed" => "Failed to update the replica master",
    "virtualserver_replica_slave_update_failed" => "Failed to update the replica slave",
    "virtualserver_replica_switched_off" => "Replica got switched off",
    "virtualserver_replica" => "Replication",
    "virtualserver_replica_tooltip_activate" => "Add replica",
    "virtualserver_replica_confirm_run" => "Confirm replica run",
    "virtualserver_replica_tooltip_run" => "Run replica",
    "virtualserver_replica_confirm_failover" => "Confirm failover",
    "virtualserver_replica_tooltip_failover" => "Failover",
    "virtualserver_replica_confirm_delete" => "Confirm delete",
    "virtualserver_replica_tooltip_delete" => "Delete",
    "virtualserver_replica_not_activated" => "Replica is not activated",
    "virtualserver_replica_status" => "Status: ",
    "virtualserver_replica_slave" => "Slave name: ",
    "virtualserver_replica_uuid" => "Slave UUID: ",
    "virtualserver_replica_host" => "Host: ",
    "virtualserver_replica_lastrun" => "Last run: ",
    "virtualserver_replica_nextrun" => "Next run: ",
    "virtualserver_replica_running_in_background" => "Replication started and running in background",
    "virtualserver_cancel" => "Cancel",
    "virtualserver_replica_" => "Start replication",
    "virtualservers_show_pdf" => "Generate PDF",
    "virtualservers_datasheet" => "Virtualserver Datasheet",
    "virtualservers_servrname" => "Servername: ",
    "virtualservers_general_info" => "General information",
    "virtualservers_activ_date" => "Activation date: ",
    "virtualservers_fqdn" => "FQDN: ",
    "virtualservers_server_type" => "Servertype: ",
    "virtualservers_pricepermonth" => "Price per month (excl MwSt): ",
    "virtualservers_system_specification" => "Systemspecification",
    "virtualservers_system" => "System: ",
    "virtualservers_os_system" => "Operatingsystem: ",
    "virtualservers_cores" => "Cores: ",
    "virtualservers_memory" => "Memory: ",
    "virtualservers_discspace" => "Discspace: ",
    "virtualservers_description" => "Description: ",
    "virtualservers_ip_adress" => "IP adress",
    "virtualservers_comment" => "Comment",
    // Replica stats PDF
    "virtualserver_replicapdf_placeholder" => "Replica stats based on date",
    "virtualservers_replicapdf" => "Replica statistics",
    "virtualserver_replicapdf_no_replicas_found" => "No replicas found for this date",
    "virtualserver_replicapdf_no_permission" => "No permission",
    "virtualserver_replicapdf_master" => "Master",
    "virtualserver_replicapdf_slave" => "Slave",
    "virtualserver_replicapdf_start" => "Start",
    "virtualserver_replicapdf_end" => "End",
    "virtualserver_replicapdf_duration" => "Duration",
    "virtualserver_replicapdf_files" => "Number of files",
    "virtualserver_replicapdf_bytes" => "Transferred Bytes",
    "virtualserver_replicapdf_no_replica" => "Replica could not be executed!",
    "virtualserver_replicapdf_no_stats" => "Statistics for this server not found",
    
    // Monitoring
    "monitoring_mon_behavior_not_implements_interface" => "MonBehavior does not implement MonBehaviorInterface.",
    "monitoring_mon_server_not_implements_interface" => "MonServer does not implement MonServerInterface.",
    "monitoring_parent_cannot_execute_jobs" => "The parent Server of this MonServer cannot execute Jobs.",
    "monitoring_healjob_failed" => "The execution of the healjob failed.",
    "monitoring_healjob_not_executed_error" => "Automatic healjob could not be sent. Maybe the host is not available. MonHealing set the job to failed so that it wont be executed later.",
    "monitoring_healing_executed" => "Healing was executed.",
    "monitoring_monuptimesgenerator_computefailed" => "Compute uptime failed: ",
    "monitoring_monlocaldailylogsgenerator_computefailed" => "Compute average failed: ",
    "monitoring_monlocaldailylogsgenerator_delete_old_daily_log" => "Had to delete MonLocalDailyLog because it will be new generated: ",
    "monitoring_monjobs_login_not_from_customer" => "Selected Login does not have the same customer as the physical server",
    "monitoring_mon_behavior_could_not_instantiate_valuestatus" => "A ValueStatus object could not be instantiated (maybe the needed infos are not available in the statistics)",
    "monitoring_allinfoupdater_mark_failed" => "The Job was marked as failed because it could not be sent immediately by the monitoring.",
    "monitoring_allinfoupdater_key_missing" => "Needed Key does not exist in retval of ovz_all_info Job.",
    // MonJobs
    "monitoring_monjobs_montype_remote_expected" => "This method only works with mon_type 'remote'.",
    "monitoring_monjobs_montype_local_expected" => "This method only works with mon_type 'local'.",
    // MonLocalJobs
    "monitoring_monlocaljobs_no_valid_unit" => "The passed unit argument is no valid unit.",
    "monitoring_monlocaljobs_end_before_start" => "End date cannot be before start date.",
    "monitoring_monlocaljobs_title" => "Local MonJobs",
    "monitoring_monlocaljobs_notfound" => "No local MonJobs found...",
    "monitoring_monlocaljobs_add" => "Add local MonJob",
    "monitoring_monlocaljobs_choose_behavior" => "Please choose a monitoring type",
    "monitoring_monlocaljobs_behavior" => "Monitoring type",
    "monitoring_monlocaljobs_period" => "Period (pause between execution)",
    "monitoring_monlocaljobs_period_placeholder" => "Quantity of minutes, e.g. 5",
    "monitoring_monlocaljobs_alarm_period" => "Alarm period (pause between notifications)",
    "monitoring_monlocaljobs_alarm_period_placeholder" => "Quantity of minutes, e.g. 15",
    "monitoring_monlocaljobs_message_contacts" => "Message-Contact",
    "monitoring_monlocaljobs_alarm_contacts" => "Alarm-Contact",
    "monitoring_monlocaljobs_add_successful" => "Local MonJob was added successfully",
    "monitoring_monlocaljobs_add_failed" => "Local MonJob could not be added: ",
    "monitoring_monlocaljobs_behavior_required" => "Monitoring type is required",
    "monitoring_monlocaljobs_period_required" => "Period is required",
    "monitoring_monlocaljobs_period_digit" => "Period must be a digit",
    "monitoring_monlocaljobs_alarm_period_required" => "Alarm period is required",
    "monitoring_monlocaljobs_alarm_period_digit" => "Alarm period must be a digit",
    "monitoring_monlocaljobs_message_contacts_required" => "At least one Message-Contact has to be selected",
    "monitoring_monlocaljobs_alarm_contacts_required" => "At least one Alarm-Contact has to be selected",
    "monitoring_monlocaljobs_statistics_timestamp_to_old" => "Statistics are to old. The job will not be executed/logged this time and so will be executed when crontab starts the task again.",
    // MonRemoteJobs
    "monitoring_monremotejobs_title" => "Remote MonJobs",
    "monitoring_monremotejobs_notfound" => "No remote MonJobs found...",
    "monitoring_monremotejobs_add" => "Add remote MonJob",
    "monitoring_monremotejobs_choose_behavior" => "Please choose a monitoring type",
    "monitoring_monremotejobs_behavior" => "Monitoring type",
    "monitoring_monremotejobs_period" => "Period (pause between execution)",
    "monitoring_monremotejobs_period_placeholder" => "Quantity of minutes, e.g. 5",
    "monitoring_monremotejobs_alarm_period" => "Alarm period (pause between notifications)",
    "monitoring_monremotejobs_alarm_period_placeholder" => "Quantity of minutes, e.g. 15",
    "monitoring_monremotejobs_message_contacts" => "Message-Contact",
    "monitoring_monremotejobs_alarm_contacts" => "Alarm-Contact",
    "monitoring_monremotejobs_add_successful" => "Remote MonJob was added successfully",
    "monitoring_monremotejobs_add_failed" => "Remote MonJob could not be added ",
    "monitoring_monremotejobs_no_uptime" => "No uptime available",
    
];
