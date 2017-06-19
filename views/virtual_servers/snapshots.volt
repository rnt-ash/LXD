<div class="clearfix panel panel-default sub-panel">
    <div class="panel-heading">
        <span role="button" data-target="#snapshots{{item.id}}" onclick="toggleIcon('#snapshotsToggleIcon'+{{item.id}})" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="snapshotsToggleIcon{{item.id}}" class="fa fa-chevron-down"></i>&nbsp;{{ _("virtualserver_snapshot") }}
                <div class="pull-right">
                    <div class="btn-group">
                        <a href="/virtual_servers/ovzSnapshotList/{{item.id}}"
                            class="btn btn-default btn-xs loadingScreen pending" data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_snapshot_refresh") }}">
                            <i class="fa fa-refresh"></i>
                        </a>
                    </div>
                </div>
            </h5>
        </span>
    </div>
    <div id="snapshots{{item.id}}" class="panel-collapse collapse in">
        <table class="table table-striped table-condensed table-hover">
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
    </div>
</div>