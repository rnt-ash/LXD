<div class="clearfix panel panel-default sub-panel">
    {% set state = slideSectionState(item.id,'VirtualServersController','general') %}
    <div class="panel-heading">
        <span role="button" data-target="#slide_section_general_{{item.id}}" onclick="toggleSectionState('slide_section_general_{{item.id}}','virtual_servers','',this)" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="slide_section_general_{{item.id}}_icon" class="fa fa-chevron-{% if state == 'show' %}down{% else %}right{% endif %}"></i>&nbsp;{{ _("virtualserver_generalinfo") }}
                <div class="pull-right">
                    {% if item.lxd == 1 %}
                    <div class="btn-group">
                        {% if permissions.checkPermission("virtual_servers", "changestate") %}
                            {% set buttonstate = "btn-info" %}
                            {% if item.lxd_status == 'Running' %}
                                {% set buttonstate = "btn-success" %}
                            {% elseif item.lxd_status == 'Stopped' %}
                                {% set buttonstate = "btn-danger" %}
                            {% endif %}
                            <button type="button" class="btn {{buttonstate}} dropdown-toggle btn-xs pending" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-lightbulb-o text-default"></i>&nbsp;<span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>{{ link_to("virtual_servers/changeCTState/"~item.id~"/start",'<i class="fa fa-play"></i> '~_("virtualserver_general_start"), 'class': 'loadingScreen pending') }}</li>
                                <li>{{ link_to("virtual_servers/changeCTState/"~item.id~"/stop",'<i class="fa fa-ban"></i> '~_("virtualserver_general_stop"), 'class': 'loadingScreen pending') }}</li>
                                <li>{{ link_to("virtual_servers/changeCTState/"~item.id~"/restart",'<i class="fa fa-retweet"></i> '~_("virtualserver_general_restart"), 'class': 'loadingScreen pending') }}</li>
                            </ul>
                        {% endif %}
                    </div>
                    {% endif %}
                    
                    {% if item.lxd == 1 %}
                        {% if permissions.checkPermission("virtual_servers", "modify") %}
                            {{ link_to("virtual_servers/virtualServerModify/"~item.id,'<i class="fa fa-pencil"></i>',
                                'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_general_editovz")) }}
                        {% endif %}
                        
                        {{ link_to("virtual_servers/rootPasswordChange/"~item.id,'<i class="fa fa-key"></i>',
                            'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_general_setpwd")) }}
                    {% else %}
                        {% if permissions.checkPermission("virtual_servers", "edit") %}
                            {{ link_to("virtual_servers/edit/"~item.id,'<i class="fa fa-pencil"></i>',
                                'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_general_editovz")) }}
                        {% endif %}
                    {% endif %}
                    {% if permissions.checkPermission("virtual_servers", "delete") %}
                    <a href="#" link="/virtual_servers/delete/{{item.id}}" text="{{ _("virtualserver_general_deleteinfo") }}"
                        class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_general_delete") }}"><i class="fa fa-trash-o"></i></a>
                    {% endif %}
                    {{ link_to('virtual_servers/genPDF/'~item.getId(),'<i class="fa fa-file-pdf-o"></i>', 'target': '_blank',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualservers_show_pdf")) }}
                </div>
            </h5>
        </span>
    </div>
    <div id="slide_section_general_{{item.id}}" class="panel-collapse collapse {% if state == 'show' %}in{% endif %}">
        <table class="table table-condensed">
            <tbody>
                <tr>
                    <td>
                        {{ _("virtualserver_general_customer") }}
                    </td>
                    <td>
                        {{item.customer.printAddressText('short')}}
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
                        {{item.lxd_status}}
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
    </div>
</div>