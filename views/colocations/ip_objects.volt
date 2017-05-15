<div class="clearfix panel panel-default sub-panel">
    <div class="panel-heading">
        <span role="button" data-target="#ipObjects{{item.id}}" onclick="toggleIcon('#ipObjectsToggleIcon'+{{item.id}})" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="ipObjectsToggleIcon{{item.id}}" class="fa fa-chevron-down"></i>&nbsp;{{ _("colocations_view_ipobjects") }}
                <div class="pull-right">
                    <div class="btn-group">
                    {{ link_to("colocations/ipObjectAdd/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("colocations_view_newipobject")) }}
                    </div>
                </div>
            </h5>
        </span>
    </div>
    <div id="ipObjects{{item.id}}" class="panel-collapse collapse in">
        <table class="table table-condensed table-striped">
            <tbody>
            {% if item.IpObjects.count() == 0 %}
                <tr colspan="4">
                    <td>
                        {{ _("colocations_view_noipobjects") }}
                    </td>
                </tr>
            {% else %}
                {% for index, ip in item.getIpObjects() %}
                    <tr>
                        <td>
                            {{ link_to("colocations/ipObjectEdit/"~ip.id,'<i class="fa fa-pencil"></i>',
                                'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("colocations_view_editipobject")) }}
                            <a href="#" link="/colocations/ipObjectDelete/{{ip.id}}" text="{{ _("colocations_view_delmessage") }}"
                                class="btn btn-default btn-xs confirm-button" data-toggle="tooltip" data-placement="top" title="{{ _("colocations_view_delete") }}"><i class="fa fa-trash-o"></i></a>
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
            </tbody>
        </table>
    </div>
</div>