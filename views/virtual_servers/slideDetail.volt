<div class="row">
    <div class="col-md-8">
    {{ partial("partials/ovz/virtual_servers/general.volt") }}
    </div>
    <div class="col-md-4">
    {{ partial("partials/ovz/virtual_servers/hwspecs.volt") }}
    </div>
</div>
<div class="row">
    <div class="col-md-12">
    {{ partial("partials/ovz/virtual_servers/ip_objects.volt") }}
    {% if item.ovz == 1 and permissions.checkPermission("virtual_servers", "snapshots") %}
        {{ partial("partials/ovz/virtual_servers/snapshots.volt") }}
    {% endif %}
    {% if item.ovz == 1 and permissions.checkPermission("virtual_servers", "replica") %}
    {{ partial("partials/ovz/virtual_servers/replica.volt") }}
    {% endif %}
    </div>
</div>


