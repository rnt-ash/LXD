<table class="table">
    <thead>
        <tr>
            <th colspan="2">
                <div class="pull-left">
                    <h5 class="panel-title pull-left">{{ _("physicalserver_general_title") }}</h5>
                </div>
                <div class="pull-right">

                    {{ link_to("physical_servers/edit/"~item.id,'<i class="fa fa-pencil"></i>',
                            'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title': _("physicalserver_general_editsettings") ) }}
                    {% if item.ovz %}
                        {{ link_to("physical_servers/ovzHostInfo/"~item.id,'<i class="fa fa-refresh"></i>',
                            'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title': _("physicalserver_general_updatesettings") ) }}
                    {% endif %}
                    {{ link_to("physical_servers/ovzConnector/"~item.id,'<i class="fa fa-link"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title': _("physicalserver_general_connectovz") ) }}
                    <a href="#" link="/physical_servers/delete/{{item.id}}" text="{{ _("physicalserver_confirm_removeserver") }}"
                        class="btn btn-default btn-xs confirm-button" data-toggle="tooltip" data-placement="top" title="{{ _("physicalserver_tooltip_removeserver") }}">
                        <i class="fa fa-trash-o"></i>
                    </a>
                            
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                {{ _("physicalserver_general_customer") }}
            </td>
            <td>
                {{item.customers.printAddressText('short')}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("physicalserver_general_fqdn") }}
            </td>
            <td>
                {{item.fqdn}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("physicalserver_general_hosttype") }}
            </td>
            <td>
                {% if item.ovz %}OpenVZ ({{ovzSetting['Version']}}){% else %}Not connected{% endif %}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("physicalserver_general_colocation") }}
            </td>
            <td>
                {{item.colocations.name}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("physicalserver_general_activdate") }}
            </td>
            <td>
                {{item.activation_date}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("physicalserver_general_description") }}
            </td>
            <td>
                {{item.description}}
            </td>
        </tr>
    </tbody>
</table>
