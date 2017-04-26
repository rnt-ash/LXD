<table class="table">
    <thead>
        <tr>
            <th colspan="2">
                <div class="pull-left">
                    <h5 class="panel-title pull-left">{{ _("virtualserver_replica") }}</h5>
                </div>
                <div class="pull-right">
                    {% if item.ovz_replica <= 0 %}
                        {{ link_to("virtual_servers/ovzReplicaActivate/"~item.id,'<i class="fa fa-plus"></i>',
                            'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_replica_tooltip_activate")) }}
                    {% else %}    
                        {{ link_to("virtual_servers/ovzReplicaRun/"~item.id,'<i class="fa fa-play loadingScreen"></i>',
                            'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_replica_tooltip_run")) }}
                        <a href="#" link="/virtual_servers/ovzReplicaFailover/{{item.id}}" text="{{ _("virtualserver_replica_confirm_failover") }}"
                            class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_replica_tooltip_failover") }}"><i class="fa fa-random"></i></a>
                        <a href="#" link="/virtual_servers/ovzReplicaDelete/{{item.id}}" text="{{ _("virtualserver_replica_confirm_delete") }}"
                            class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_replica_tooltip_delete") }}"><i class="fa fa-trash-o"></i></a>
                    {% endif %}
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
    {% if item.ovz_replica <= 0 %}
        <tr colspan="2">
            <td>
                {{ _("virtualserver_replica_not_activated") }}
            </td>
        </tr>
    {% else %}
        <tr>
            <td>
                {{ _("virtualserver_replica_status") }}
            </td>
            <td>
                {# 0:off, 1:idle, 2:sync, 3:initial, 9:error #}
                {% if item.ovz_replica_status == 0 %}
                    off
                {% elseif item.ovz_replica_status == 1 %}                
                    idle
                {% elseif item.ovz_replica_status == 2 %}                
                    sync
                {% elseif item.ovz_replica_status == 3 %}                
                    initial
                {% else %}                
                    error
                {% endif %}
                
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_replica_slave") }}
            </td>
            <td>
                {{item.ovzReplicaId.name}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_replica_host") }}
            </td>
            <td>
                {{item.ovzReplicaHost.name}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_replica_lastrun") }}
            </td>
            <td>
                {{item.ovz_replica_lastrun}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_replica_nextrun") }}
            </td>
            <td>
                {{item.ovz_replica_nextrun}}
            </td>
        </tr>
    {% endif %}
    </tbody>
</table>
