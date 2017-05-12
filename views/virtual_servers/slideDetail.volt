<div class="panel-group">
    <div class="row">
        <div class="col-md-8 padding-small-right">
        {{ partial("partials/ovz/virtual_servers/general.volt") }}
        </div>
        <div class="col-md-4 padding-small-left">
        {{ partial("partials/ovz/virtual_servers/hwspecs.volt") }}
        </div>
        <div class="col-md-12">
        {{ partial("partials/ovz/virtual_servers/ip_objects.volt") }}
        </div>
    {% if item.ovz == 1 and permissions.checkPermission("virtual_servers", "snapshots") %}
        <div class="col-md-12">
        {{ partial("partials/ovz/virtual_servers/snapshots.volt") }}
        </div>
    {% endif %}
    {% if item.ovz == 1 and permissions.checkPermission("virtual_servers", "replica") %}
        <div class="col-md-12">
        {{ partial("partials/ovz/virtual_servers/replica.volt") }}
        </div>
    {% endif %}
        <div class="col-md-6 col-xs-12 padding-small-right">
        {{ partial("partials/ovz/virtual_servers/monLocalJobs.volt") }}
        </div>
        <div class="col-md-6 col-xs-12 padding-small-left">
        {{ partial("partials/ovz/virtual_servers/monRemoteJobs.volt") }}
        </div>
    </div>
</div>