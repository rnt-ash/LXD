<div class="clearfix panel panel-default sub-panel">
    {% set defaultState = 'hide' %}
    {% set state = slideSectionState(item.id,'VirtualServersController','replica',defaultState) %}
    <div class="panel-heading">
        <span role="button" data-target="#slide_section_replica_{{item.id}}" onclick="toggleSectionState('slide_section_replica_{{item.id}}','virtual_servers','{{defaultState}}',this)" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="slide_section_replica_{{item.id}}_icon" class="fa fa-chevron-{% if state == 'show' %}down{% else %}right{% endif %}"></i>&nbsp;{{ _("virtualserver_replica") }}
                <div class="pull-right">
                    {% if item.ovz_replica <= 0 %}
                        {{ link_to("virtual_servers/ovzReplicaActivate/"~item.id,'<i class="fa fa-plus"></i>',
                            'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_replica_tooltip_activate")) }}
                    {% else %}    
                        <a href="#" link="/virtual_servers/ovzReplicaRun/{{item.id}}" text="{{ _("virtualserver_replica_confirm_run") }}"
                            class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_replica_tooltip_run") }}"><i class="fa fa-play"></i></a>
                        <a href="#" link="/virtual_servers/ovzReplicaFailover/{{item.id}}" text="{{ _("virtualserver_replica_confirm_failover") }}"
                            class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_replica_tooltip_failover") }}"><i class="fa fa-random"></i></a>
                        <a href="#" link="/virtual_servers/ovzReplicaDelete/{{item.id}}" text="{{ _("virtualserver_replica_confirm_delete") }}"
                            class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_replica_tooltip_delete") }}"><i class="fa fa-trash-o"></i></a>
                    {% endif %}
                </div>
            </h5>
        </span>
    </div>
    <div id="slide_section_replica_{{item.id}}" class="panel-collapse collapse {% if state == 'show' %}in{% endif %}">
        <table class="table table-condensed">
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
                        {{ _("virtualserver_replica_uuid") }}
                    </td>
                    <td>
                        {{item.ovzReplicaId.ovz_uuid}}
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
    </div>
</div>