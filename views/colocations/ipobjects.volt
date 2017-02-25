<table class="table">
    <thead>
        <tr>
            <th colspan="4">
                <div class="pull-left"><h5 class="panel-title pull-left">IP Objects</h5></div>
                <div class="pull-right">
                    <div class="btn-group">
                    {{ link_to("colocations/addIpObject/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':'Add new IP Object') }}
                    </div>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
    {% if item.dcoipobjects.count() == 0 %}
        <tr colspan="4">
            <td>
                No IP Objects found...
            </td>
        </tr>
    {% else %}
        {% for index, ip in item.dcoipobjects %}
            <tr>
                <td>
                    {{ link_to("colocations/editIpObject/"~ip.id,'<i class="fa fa-pencil"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':'Edit IP Object') }}
                    <a href="#" link="/colocations/deleteIpObject/{{ip.id}}" text="Are you sure to delete this IP Object?"
                        class="btn btn-default btn-xs confirm-button" data-toggle="tooltip" data-placement="top" title="Delete IP Object"><i class="fa fa-trash-o"></i></a>
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
