<div class="clearfix panel panel-default sub-panel">
    {% set state = slideSectionState(item.id,'VirtualServersController','ipobjects') %}
    <div class="panel-heading">
        <span role="button" data-target="#slide_section_ipobjects_{{item.id}}" onclick="toggleSectionState('slide_section_ipobjects_{{item.id}}','virtual_servers','',this)" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="slide_section_ipobjects_{{item.id}}_icon" class="fa fa-chevron-{% if state == 'show' %}down{% else %}right{% endif %}"></i>&nbsp;{{ _("virtualserver_ipobject") }}
                <div class="pull-right">
                    <div class="btn-group">
                    {% if permissions.checkPermission("ip_objects", "general") %}
                    {{ link_to("virtual_servers/ipObjectAdd/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_ip_newobject")) }}
                    {% endif %}
                    </div>
                </div>
            </h5>
        </span>
    </div>
    <div id="slide_section_ipobjects_{{item.id}}" class="panel-collapse collapse {% if state == 'show' %}in{% endif %}">
        <table class="table table-striped table-condensed table-hover">
            <tbody>
            {% if item.ipobjects.count() == 0 %}
                <tr colspan="4">
                    <td>
                        {{ _("virtualserver_noipobject") }}
                    </td>
                </tr>
            {% else %}
                {% for index, ip in item.ipobjects %}
                    <tr>
                        {% if permissions.checkPermission("ip_objects", "general") %}
                        <td>
                            {{ link_to("virtual_servers/ipObjectEdit/"~ip.id,'<i class="fa fa-pencil"></i>',
                                'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_ip_edit")) }}
                            <a href="#" link="/virtual_servers/ipObjectDelete/{{ip.id}}" text="{{ _("virtualserver_ip_deleteinfo") }}"
                                class="btn btn-default btn-xs confirm-button pending" data-toggle="tooltip" data-placement="top" title="{{ _("virtualserver_ip_delete") }}"><i class="fa fa-trash-o"></i></a>
                            {% if ip.main == 0 AND ip.allocated != constant('\RNTForest\ovz\models\IpObjects::ALLOC_RESERVED') %}
                                {{ link_to("virtual_servers/ipObjectMakeMain/"~ip.id,'<i class="fa fa-bolt"></i>',
                                    'class': 'btn btn-default btn-xs pending', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("virtualserver_ip_primary")) }}
                            {% endif %}
                        </td>
                        {% endif %}
                        <td>
                            {% if ip.allocated == constant('\RNTForest\ovz\models\IpObjects::ALLOC_RESERVED') %}
                                Reserved
                            {% elseif ip.allocated == constant('\RNTForest\ovz\models\IpObjects::ALLOC_ASSIGNED') %}
                                Assigned
                            {% elseif ip.allocated == constant('\RNTForest\ovz\models\IpObjects::ALLOC_AUTOASSIGNED') %}
                                Auto Assigned
                            {% endif %}   
                        </td>
                        <td>
                            {{ip.toString()}}
                        </td>
                        <td>
                            {{ip.comment}}
                        </td>
                    </tr>
                {% endfor %}
            {% endif %}
            </tbody>
        </table>
    </div>
</div>