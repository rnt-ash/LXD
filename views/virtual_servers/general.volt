<table class="table table-condensed">
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
                            {% set buttonstate = "btn-info" %}
                            {% if item.ovzSettingsArray['State'] == 'running' %}
                                {% set buttonstate = "btn-success" %}
                            {% elseif item.ovzSettingsArray['State'] == 'stopped' %}
                                {% set buttonstate = "btn-danger" %}
                            {% endif %}
                            <button type="button" class="btn {{buttonstate}} dropdown-toggle btn-xs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-lightbulb-o text-default"></i>&nbsp;<span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>{{ link_to("virtual_servers/startVS/"~item.id,'<i class="fa fa-play"></i> '~_("virtualserver_general_start"), 'class': 'loadingScreen pending') }}</li>
                                <li>{{ link_to("virtual_servers/stopVS/"~item.id,'<i class="fa fa-ban"></i> '~_("virtualserver_general_stop"), 'class': 'loadingScreen pending') }}</li>
                                <li>{{ link_to("virtual_servers/restartVS/"~item.id,'<i class="fa fa-retweet"></i> '~_("virtualserver_general_restart"), 'class': 'loadingScreen pending') }}</li>
                            </ul>
                        {% endif %}
                    </div>
                    {% endif %}
                    
                    {% if item.ovz == 1 %}
                        {{ link_to("virtual_servers/virtualServerModify/"~item.id,'<i class="fa fa-pencil"></i>',
                            'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_general_editovz")) }}
                        {{ link_to("virtual_servers/ovzUpdateInfo/"~item.id,'<i class="fa fa-refresh"></i>',
                            'class': 'btn btn-default btn-xs loadingScreen pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_general_updateovz")) }}
                        {{ link_to("virtual_servers/rootPasswordChange/"~item.id,'<i class="fa fa-key"></i>',
                            'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_general_setpwd")) }}
                    {% else %}
                        {{ link_to("virtual_servers/edit/"~item.id,'<i class="fa fa-pencil"></i>',
                            'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_general_editovz")) }}
                    {% endif %}
                    <a href="#" link="/virtual_servers/delete/{{item.id}}" text="{{ _("virtualserver_general_deleteinfo") }}"
                        class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_general_delete") }}"><i class="fa fa-trash-o"></i></a>

                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                {{ _("virtualserver_general_customer") }}
            </td>
            <td>
                {{item.customers.printAddressText('short')}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_general_fqdn") }}
            </td>
            <td>
                {{item.fqdn}}
            </td>
        </tr>
        {% if item.ovz == 1 %}
        <tr>
            <td>
                {{ _("virtualserver_general_uuid") }}
            </td>
            <td>
                {{item.ovz_uuid}}
            </td>
        </tr>
        {% endif %}
        <tr>
            <td>
                {{ _("virtualserver_general_physicalserver") }}
            </td>
            <td>
                {{item.physicalServers.name}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_general_activdate") }}
            </td>
            <td>
                {{item.activation_date}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_general_state") }}
            </td>
            <td>
                {{item.ovz_state}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("virtualserver_general_description") }}
            </td>
            <td>
                {{item.description}}
            </td>
        </tr>
    </tbody>
</table>
