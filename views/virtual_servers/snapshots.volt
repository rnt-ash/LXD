<div class="clearfix panel panel-default sub-panel">
    {% set state = slideSectionState(item.id,'VirtualServersController','snapshots') %}
    <div class="panel-heading">
        <span role="button" data-target="#slide_section_snapshots_{{item.id}}" onclick="toggleSectionState('slide_section_snapshots_{{item.id}}','virtual_servers','',this)" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="slide_section_snapshots_{{item.id}}_icon" class="fa fa-chevron-{% if state == 'show' %}down{% else %}right{% endif %}"></i>&nbsp;{{ _("virtualserver_snapshot") }}
                <div class="pull-right">
                    <div class="btn-group">
                        <a href="/virtual_servers/lxdSnapshotList/{{item.id}}"
                            class="btn btn-default btn-xs loadingScreen pending" data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_snapshot_refresh") }}">
                            <i class="fa fa-refresh"></i>
                        </a>
                    </div>
                </div>
            </h5>
        </span>
    </div>
    <div id="slide_section_snapshots_{{item.id}}" class="panel-collapse collapse {% if state == 'show' %}in{% endif %}">
        <table class="table table-striped table-condensed">
            <tbody>
                <tr>
                    <td id="snapshots">
                        <ul>
                            {% set loopLength = 0 %}
                            {% if item.getLxdSnapshots() is not empty %}
                                {% for name, snapshot in item.getLxdSnapshotsArray() %}
                                    <li class="list-group-item" style="margin-left: {{ loop.index0*2 }}0px">
                                        <div class="btn-group">
                                            {% if loop.last %}
                                            <a href="#/" link="lxdSnapshotSwitch/{{ name }}/{{ item.id }}" 
                                                class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" data-placement="top"
                                                text="{{ _("virtualserver_snapshot_switchinfo") }}" title="{{ _("virtualserver_snapshot_switch") }}">
                                                <i class="fa fa-play-circle fa-lg"></i>
                                            </a>
                                            {% endif %}
                                            <a href="#/" link="lxdSnapshotDelete/{{ name }}/{{ item.id }}"
                                                class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" data-placement="top"
                                                text="{{ _("virtualserver_snapshot_deleteinfo") }}" title="{{ _("virtualserver_snapshot_delete") }}">
                                                <i class="fa fa-trash fa-lg"></i>
                                            </a>
                                        </div>
                                        {{ name }}
                                        <span class="pull-right hidden-xs">{{ snapshot['created_at'] }}</span>
                                    </li>
                                    {% set loopLength = loop.length %}
                                {% endfor %}
                            {% endif %}
                            <li class="list-group-item" style="margin-left: {{ loopLength*2 }}0px">
                                <div class="btn-group">
                                    <a href="/virtual_servers/lxdSnapshotCreate/{{item.id}}" class="btn btn-default btn-xs pending"
                                        data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_snapshot_create") }}">
                                        <i class="fa fa-plus fa-lg"></i>
                                    </a>
                                </div>
                                {{ _("virtualserver_snapshot_run") }}
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>