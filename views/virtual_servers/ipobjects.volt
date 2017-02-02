<table class="table">
    <thead>
        <tr>
            <th colspan="4">
                <div class="pull-left"><h5 class="panel-title pull-left">IP Objects</h5></div>
                <div class="pull-right">
                    <div class="btn-group">
                    {{ link_to("virtual_servers/addIpObject/"~item.id,'<i class="fa fa-plus"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':'Add new IP Object') }}
                    </div>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
    {% for index, ip in item.dcoipobjects %}
        <tr>
            <td>
                {{ link_to("virtual_servers/editIpObject/"~ip.id,'<i class="fa fa-pencil"></i>',
                    'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':'Edit IP Object') }}
                <a href="#" link="/virtual_servers/deleteIpObject/{{ip.id}}" text="Are you sure to delete this IP Object?"
                    class="btn btn-default btn-xs delete-tableslide-item" data-toggle="tooltip" data-placement="top" title="Delete IP Object"><i class="fa fa-trash-o"></i></a>
                {% if ip.main == 0 AND ip.allocated != constant('Dcoipobjects::ALLOC_RESERVED') %}
                    {{ link_to("virtual_servers/makeMainIpObject/"~ip.id,'<i class="fa fa-bolt"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':'Make IP Object to primary') }}
                {% endif %}
            </td>
            <td>
                {% if ip.allocated == constant('Dcoipobjects::ALLOC_RESERVED') %}
                    Reserved
                {% elseif ip.allocated == constant('Dcoipobjects::ALLOC_ASSIGNED') %}
                    Assigned
                {% elseif ip.allocated == constant('Dcoipobjects::ALLOC_AUTOASSIGNED') %}
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
    </tbody>
</table>
