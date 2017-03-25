<table class="table">
    <thead>
        <tr>
            <th colspan="4">
                <div class="pull-left"><h5 class="panel-title pull-left">{{ _("colocations_view_ipobjects") }}</h5></div>
                <div class="pull-right">
                    <div class="btn-group">
                    {{ link_to("colocations/ipObjectAdd/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("colocations_view_newipobject")) }}
                    </div>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
    {% if item.dcoipobjects.count() == 0 %}
        <tr colspan="4">
            <td>
                {{ _("colocations_view_noipobjects") }}
            </td>
        </tr>
    {% else %}
        {% for index, ip in item.dcoipobjects %}
            <tr>
                <td>
                    {{ link_to("colocations/ipObjectEdit/"~ip.id,'<i class="fa fa-pencil"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("colocations_view_editipobject")) }}
                    <a href="#" link="/colocations/ipObjectDelete/{{ip.id}}" text="{{ _("colocations_view_delmessage") }}"
                        class="btn btn-default btn-xs confirm-button" data-toggle="tooltip" data-placement="top" title="{{ _("colocations_view_delete") }}"><i class="fa fa-trash-o"></i></a>
                </td>
                <td>
                    {% if ip.allocated == constant('\RNTForest\ovz\models\Dcoipobjects::ALLOC_RESERVED') %}
                        Reserved
                    {% elseif ip.allocated == constant('\RNTForest\ovz\models\Dcoipobjects::ALLOC_ASSIGNED') %}
                        Assigned
                    {% elseif ip.allocated == constant('\RNTForest\ovz\models\Dcoipobjects::ALLOC_AUTOASSIGNED') %}
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
