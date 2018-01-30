<div class="clearfix panel panel-default sub-panel">
    {% set state = slideSectionState(item.id,'PhysicalServersController','general') %}
    <div class="panel-heading">
        <span role="button" data-target="#slide_section_general_{{item.id}}" onclick="toggleSectionState('slide_section_general_{{item.id}}','physical_servers','',this)" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="slide_section_general_{{item.id}}_icon" class="fa fa-chevron-{% if state == 'show' %}down{% else %}right{% endif %}"></i>&nbsp;{{ _("physicalserver_general_title") }}
                <div class="pull-right">
                    {{ link_to("physical_servers/edit/"~item.id,'<i class="fa fa-pencil"></i>',
                            'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title': _("physicalserver_general_editsettings") ) }}
                    {{ link_to("physical_servers/lxdConnector/"~item.id,'<i class="fa fa-link"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title': _("physicalserver_general_connectlxd") ) }}
                    <a href="#" link="/physical_servers/delete/{{item.id}}" text="{{ _("physicalserver_confirm_removeserver") }}"
                        class="btn btn-default btn-xs confirm-button" data-toggle="tooltip" data-placement="top" title="{{ _("physicalserver_tooltip_removeserver") }}">
                        <i class="fa fa-trash-o"></i>
                    </a>
                </div>
            </h5>
        </span>
    </div>
    <div id="slide_section_general_{{item.id}}" class="panel-collapse collapse {% if state == 'show' %}in{% endif %}">
        <table class="table table-condensed">
            <tbody>
                <tr>
                    <td>
                        {{ _("physicalserver_general_customer") }}
                    </td>
                    <td>
                        {{item.customer.printAddressText('short')}}
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
                        {% if item.lxd %}LXD Host{% else %}Not connected{% endif %}
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