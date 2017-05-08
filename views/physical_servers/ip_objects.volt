<div class="clearfix panel panel-default sub-panel">
    <div class="panel-heading">
        <span role="button" data-target="#ipObjects{{item.id}}" onclick="toggleIcon('#ipObjectsToggleIcon'+{{item.id}})" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="ipObjectsToggleIcon{{item.id}}" class="fa fa-chevron-down"></i>&nbsp;{{ _("physicalserver_ip_title") }}
                <div class="pull-right">
                    <div class="btn-group">
                    {{ link_to("physical_servers/ipObjectAdd/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("physicalserver_ip_addobject"),'onclick':'event.stopPropagation();') }}
                    </div>
                </div>
            </h5>
        </span>
    </div>
    <div id="ipObjects{{item.id}}" class="panel-collapse collapse in">
        <table class="table table-condensed table-striped collapse-table">
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
</div>