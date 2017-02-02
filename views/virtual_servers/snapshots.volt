<table class="table">
    <thead>
        <tr>
            <th colspan="2">
                <div class="pull-left"><h5 class="panel-title pull-left">Snapshots</h5></div>
                <div class="pull-right">
                    <div class="btn-group">
                        <a href="/virtual_servers/ovzListSnapshots/{{item.id}}"
                            class="btn btn-default btn-xs" data-toggle="tooltip" data-placement="top" title="Refresh snapshot">
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
                            <a href="/virtual_servers/snapshotForm/{{item.id}}" class="btn btn-default btn-xs"
                                data-toggle="tooltip" data-placement="top" title="Create a new snapshot">
                                <i class="fa fa-plus fa-lg"></i>
                            </a>
                        </div>
                        Current run
                    </li>

                </ul>
            {% endif %}
            </td>
        </tr>
    </tbody>
</table>