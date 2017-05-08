<div class="row">
    <div class="col-md-8">
    {{ partial("partials/ovz/physical_servers/general.volt") }}
    </div>
    <div class="col-md-4">
    {{ partial("partials/ovz/physical_servers/hwspecs.volt") }}
    </div>
</div>
<div class="panel-group">
    <div class="row">
        <div class="col-xs-12">
        {{ partial("partials/ovz/physical_servers/ip_objects.volt") }}
        </div>
    </div>
    <div>
        <div class="col-md-6 col-xs-12 removePadding">
        {{ partial("partials/ovz/physical_servers/monLocalJobs.volt") }}
        </div>
        <div class="col-md-6 col-xs-12 removePadding">
        {{ partial("partials/ovz/physical_servers/monRemoteJobs.volt") }}
        </div>
    </div>
</div>