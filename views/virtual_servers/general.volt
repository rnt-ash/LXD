<table class="table">
    <thead>
        <tr>
            <th colspan="2">
                <div class="pull-left">
                    <h5 class="panel-title pull-left">{{ _("virtualserver_generalinfo") }}</h5>
                </div>
                <div class="pull-right">
                    {% if item.ovz == 1 %}
                    <div class="btn-group">
                        {% if permissions.checkPermission("virtual_servers", "changestate") %}
                        <button type="button" class="btn btn-success dropdown-toggle btn-xs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-lightbulb-o text-default"></i>&nbsp;<span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>{{ link_to("virtual_servers/startVS/"~item.id,'<i class="fa fa-play"></i> {{ _("virtualserver_general_start") }}') }}</li>
                            <li>{{ link_to("virtual_servers/stopVS/"~item.id,'<i class="fa fa-ban"></i> {{ _("virtualserver_general_stop") }}') }}</li>
                            <li>{{ link_to("virtual_servers/restartVS/"~item.id,'<i class="fa fa-retweet"></i> {{ _("virtualserver_general_restart") }}') }}</li>
                        </ul>
                        {% endif %}
                    </div>
                    {% endif %}
                    
                    {{ link_to("virtual_servers/edit/"~item.id,'<i class="fa fa-pencil"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_general_editovz")) }}
                    {% if item.ovz == 1 %}
                        {{ link_to("virtual_servers/ovzListInfo/"~item.id,'<i class="fa fa-refresh"></i>',
                            'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_general_updateovz")) }}
                        {{ link_to("virtual_servers/ovzStatisticsInfo/"~item.id,'<i class="fa fa-refresh"></i>',
                            'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_general_updatestats")) }}
                        {# not yet implementet
                        {{ link_to("virtual_servers/todo/"~item.id,'<i class="fa fa-key"></i>',
                            'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_general_setpwd")) }}
                        #}
                    {% endif %}
                    <a href="#" link="/virtual_servers/delete/{{item.id}}" text="{{ _("virtualserver_general_deleteinfo") }}"
                        class="btn btn-default btn-xs confirm-button" data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_general_delete") }}"><i class="fa fa-trash-o"></i></a>

                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                {{ _("virtualserver_genera_customer") }}
            </td>
            <td>
                {{item.customers.printAddressText('short')}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_genera_fqdn") }}
            </td>
            <td>
                {{item.fqdn}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_genera_physicalserver") }}
            </td>
            <td>
                {{item.physicalServers.name}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_genera_activdate") }}
            </td>
            <td>
                {{item.activation_date}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_genera_state") }}
            </td>
            <td>
                {{item.ovz_state}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_genera_description") }}
            </td>
            <td>
                {{item.description}}
            </td>
        </tr>
    </tbody>
</table>
