<table class="table table-striped table-condensed">
    <thead>
        <tr>
            <th colspan="2">
                <div class="pull-left"><h5 class="panel-title pull-left">{{ _("virtualserver_snapshot") }}</h5></div>
                <div class="pull-right">
                    <div class="btn-group">
                        <a href="/virtual_servers/ovzSnapshotList/{{item.id}}"
                            class="btn btn-default btn-xs loadingScreen pending" data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_snapshot_refresh") }}">
                            <i class="fa fa-refresh"></i>
                        </a>
                    </div>

                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td id="snapshots">
            {% include "partials/ovz/virtual_servers/macros.inc.volt" %}
            
            {% if snapshots is not empty %}
                {{ render_snapshots(snapshots,item.id) }}
            {% else %}
                <ul>
                    <li class="list-group-item">
                        <div class="btn-group">
                            <a href="/virtual_servers/ovzSnapshotCreate/{{item.id}}" class="btn btn-default btn-xs pending"
                                data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_snapshot_create") }}">
                                <i class="fa fa-plus fa-lg"></i>
                            </a>
                        </div>
                        {{ _("virtualserver_snapshot_run") }}
                    </li>

                </ul>
            {% endif %}
            </td>
        </tr>
    </tbody>
</table>