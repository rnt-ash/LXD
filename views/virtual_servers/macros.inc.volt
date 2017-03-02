{%- macro render_snapshots(snapshots,serverId) %}
{% for snapshot in snapshots %}
<ul>
    <li>
        <div class="list-group-item" title="{{ snapshot['Description'] }}">
            <div class="btn-group">
                <a href="#" link="ovzSwitchSnapshot/{{snapshot['UUID']}}/{{serverId}}" 
                    class="btn btn-default btn-xs confirmDialog" data-toggle="tooltip" data-placement="top"
                    text="{{ _("virtualserver_snapshot_switchinfo") }}" title="{{ _("virtualserver_snapshot_switch") }}">
                    <i class="fa fa-play-circle fa-lg"></i>
                </a>
            </div>

            {% if snapshot['Removable'] == 1 %}
            <div class="btn-group">
                <a href="#" link="ovzDeleteSnapshot/{{snapshot['UUID']}}/{{serverId}}"
                    class="btn btn-default btn-xs confirmDialog" data-toggle="tooltip" data-placement="top"
                    text="{{ _("virtualserver_snapshot_deleteinfo") }}" title="{{ _("virtualserver_snapshot_delete") }}">
                    <i class="fa fa-trash fa-lg"></i>
                </a>
            </div>
            {% endif %}

            {% if snapshot['Name'] is empty %} 
            no name available
            {% else %}
            {{ snapshot['Name'] }}
            {% endif %}

            <span class="pull-right hidden-xs">{{ snapshot['Date'] }}</span>
        </div>

        {% if snapshot['Current'] == 'yes' %}
        <ul>
            <li class="list-group-item">
                <div class="btn-group">
                    <a href="/virtual_servers/snapshotForm/{{serverId}}" class="btn btn-default btn-xs"
                        data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_snapshot_new") }}">
                        <i class="fa fa-plus fa-lg"></i>
                    </a>
                </div>
                {{ _("virtualserver_snapshot_run") }}
            </li>
        </ul>
        {% endif %}

        {% if snapshot['Childs'] is not empty %}
        {{ render_snapshots(snapshot['Childs'],serverId) }}
        {% endif %}
    </li>
</ul>
{% endfor %}
{%- endmacro %}