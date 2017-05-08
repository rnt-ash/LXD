<table class="table">
    <thead>
        <tr>
            <th colspan="4">
                <div class="pull-left">
                    <h5 class="panel-title">
                        <a href="#ipObjects{{item.id}}" onclick="toggleIcon('#ipObjectsToggleIcon'+{{item.id}})" data-toggle="collapse" class="pull-left">
                            <i id="ipObjectsToggleIcon{{item.id}}" class="fa fa-chevron-right"></i>&nbsp;{{ _("physicalserver_ip_title") }}
                        </a>
                    </h5>
                </div>
                <div class="pull-right">
                    <div class="btn-group">
                    {{ link_to("physical_servers/ipObjectAdd/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("physicalserver_ip_addobject")) }}
                    </div>
                </div>
            </th>
        </tr>
    </thead>
    <tbody><tr><td class="removePadding">
        <div id="ipObjects{{item.id}}" class="collapse">
            <table class="table table-condensed table-striped sub-table">
                {% if item.getIpObjects().count() == 0 %}
                    <tr colspan="4">
                        <td>
                            {{ _("physicalserver_ip_notfound") }}
                        </td>
                    </tr>
                {% else %}
                {% for index, ip in item.getIpObjects() %}
                    <tr>
                        <td>
                            {{ link_to("physical_servers/ipObjectEdit/"~ip.id,'<i class="fa fa-pencil"></i>',
                                'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("physicalserver_ip_editobject")) }}
                            <a href="#" link="/physical_servers/ipObjectDelete/{{ip.id}}" text="{{ _("physicalserver_ip_deleteconf") }}"
                                class="btn btn-default btn-xs confirm-button" data-toggle="tooltip" data-placement="top" title="{{ _("physicalserver_ip_delete") }}"><i class="fa fa-trash-o"></i></a>
                            {% if ip.main == 0 AND ip.allocated != constant('\RNTForest\ovz\models\IpObjects::ALLOC_RESERVED') %}
                                {{ link_to("physical_servers/ipObjectMakeMain/"~ip.id,'<i class="fa fa-bolt"></i>',
                                    'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("physicalserver_ip_primary")) }}
                            {% endif %}
                        </td>
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
            </table>
        </div>
    </td></tr></tbody>
</table>
