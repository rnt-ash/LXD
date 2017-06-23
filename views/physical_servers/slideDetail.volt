<div class="row">
    <div class="col-md-8 padding-small-right">
    {{ partial("partials/ovz/physical_servers/general.volt") }}
    </div>
    <div class="col-md-4 padding-small-left">
    {{ partial("partials/ovz/physical_servers/hwspecs.volt") }}
    </div>
</div>
<div class="panel-group">
    <div class="row">
        <div class="col-xs-12">
        {{ partial("partials/ovz/physical_servers/ip_objects.volt") }}
        </div>
        <div class="col-xs-12">
        {{ partial("partials/ovz/physical_servers/monJobs.volt") }}
        </div>
    </div>
</div>