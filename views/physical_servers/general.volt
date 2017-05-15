<div class="clearfix panel panel-default sub-panel">
    <div class="panel-heading">
        <span role="button" data-target="#general{{item.id}}" onclick="toggleIcon('#generalToggleIcon'+{{item.id}})" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="generalToggleIcon{{item.id}}" class="fa fa-chevron-down"></i>&nbsp;{{ _("physicalserver_general_title") }}
                <div class="pull-right">
                    {{ link_to("physical_servers/edit/"~item.id,'<i class="fa fa-pencil"></i>',
                            'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title': _("physicalserver_general_editsettings") ) }}
                    {% if item.ovz %}
                        {{ link_to("physical_servers/ovzAllInfo/"~item.id,'<i class="fa fa-refresh"></i>',
                            'class': 'btn btn-default btn-xs loadingScreen', 'data-toggle':'tooltip', 'data-placement':'top', 'title': _("physicalserver_general_update_infos") ) }}
                    {% endif %}
                    {{ link_to("physical_servers/ovzConnector/"~item.id,'<i class="fa fa-link"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title': _("physicalserver_general_connectovz") ) }}
                    <a href="#" link="/physical_servers/delete/{{item.id}}" text="{{ _("physicalserver_confirm_removeserver") }}"
                        class="btn btn-default btn-xs confirm-button" data-toggle="tooltip" data-placement="top" title="{{ _("physicalserver_tooltip_removeserver") }}">
                        <i class="fa fa-trash-o"></i>
                    </a>
                </div>
            </h5>
        </span>
    </div>
    <div id="general{{item.id}}" class="panel-collapse collapse in">
        <table class="table table-condensed">
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
    </div>
</div>