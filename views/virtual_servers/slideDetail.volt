<ul class="nav nav-tabs" id="tabs">
    <li class="active"><a data-toggle="tab" href="#general{{ item.id }}">{{ _("virtualserver_generalinfo") }}</a></li>
    <li><a data-toggle="tab" href="#ipObjects{{ item.id }}">{{ _("virtualserver_ipobject") }}</a></li>
    {% if item.ovz == 1 and permissions.checkPermission("virtual_servers", "snapshots") %}
    <li><a data-toggle="tab" href="#snapshots{{ item.id }}">{{ _("virtualserver_snapshot") }}</a></li>
    {% endif %}
    {% if item.ovz == 1 and permissions.checkPermission("virtual_servers", "replica") %}
    <li><a data-toggle="tab" href="#replica{{ item.id }}">{{ _("virtualserver_replica") }}</a></li>
    {% endif %}
    <li><a data-toggle="tab" href="#monJobs{{ item.id }}">MonJobs</a></li>
</ul>
<div class="tab-content">
    <div id="general{{ item.id }}" class="row tab-pane fade in active">
        <div class="col-md-8">
        {{ partial("partials/ovz/virtual_servers/general.volt") }}
        </div>
        <div class="col-md-4">
        {{ partial("partials/ovz/virtual_servers/hwspecs.volt") }}
        </div>
    </div>
    <div id="ipObjects{{ item.id }}" class="row tab-pane fade">
        <div class="col-md-12">
        {{ partial("partials/ovz/virtual_servers/ip_objects.volt") }}
        </div>
    </div>
    {% if item.ovz == 1 and permissions.checkPermission("virtual_servers", "snapshots") %}
    <div id="snapshots{{ item.id }}" class="row tab-pane fade">
        <div class="col-md-12">
        {{ partial("partials/ovz/virtual_servers/snapshots.volt") }}
        </div>
    </div>
    {% endif %}
    {% if item.ovz == 1 and permissions.checkPermission("virtual_servers", "replica") %}
    <div id="replica{{ item.id }}" class="row tab-pane fade">
        <div class="col-md-12">
        {{ partial("partials/ovz/virtual_servers/replica.volt") }}
        </div>
    </div>
    {% endif %}
    <div id="monJobs{{ item.id }}" class="row tab-pane fade">
        <div class="col-md-6">
        {{ partial("partials/ovz/virtual_servers/monLocalJobs.volt") }}
        </div>
        <div class="col-md-6">
        {{ partial("partials/ovz/virtual_servers/monRemoteJobs.volt") }}
        </div>
    </div>
</div>