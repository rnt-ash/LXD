<div class="clearfix panel panel-default sub-panel">
    {% set state = slideSectionState(item.id,'VirtualServersController','snapshots') %}
    <div class="panel-heading">
        <span role="button" data-target="#slide_section_snapshots_{{item.id}}" onclick="toggleSectionState('slide_section_snapshots_{{item.id}}','virtual_servers','',this)" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="slide_section_snapshots_{{item.id}}_icon" class="fa fa-chevron-{% if state == 'show' %}down{% else %}right{% endif %}"></i>&nbsp;{{ _("virtualserver_snapshot") }}
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
    <div id="slide_section_snapshots_{{item.id}}" class="panel-collapse collapse {% if state == 'show' %}in{% endif %}">
        <table class="table table-striped table-condensed table-hover">
            <tbody>
                <tr>
                    <td id="snapshots">
                    {% include "partials/lxd/virtual_servers/macros.inc.volt" %}
                    
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