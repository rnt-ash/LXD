{% include "partials/core/partials/slideSectionState.volt" %}
<div class="row">
    <div class="col-md-8 padding-small-right">
    {{ partial("partials/lxd/physical_servers/general.volt") }}
    </div>
    <div class="col-md-4 padding-small-left">
    {{ partial("partials/lxd/physical_servers/hwspecs.volt") }}
    </div>
</div>
<div class="panel-group">
    <div class="row">
        <div class="col-xs-12">
        {{ partial("partials/lxd/physical_servers/ip_objects.volt") }}
        </div>
    </div>
</div>