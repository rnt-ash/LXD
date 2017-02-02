<table class="table">
    <thead>
        <tr>
            <th colspan="2">
                <div class="pull-left">
                    <h5 class="panel-title pull-left">General informations</h5>
                </div>
                <div class="pull-right">

                    {{ link_to("physical_servers/edit/"~item.id,'<i class="fa fa-pencil"></i>',
                            'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':'Edit settings') }}
                    {% if item.ovz %}
                        {{ link_to("physical_servers/ovzHostInfo/"~item.id,'<i class="fa fa-refresh"></i>',
                            'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':'Update OVZ settings') }}
                    {% endif %}
                    {{ link_to("physical_servers/connectForm/"~item.id,'<i class="fa fa-link"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':'Connect OVZ') }}
                    <a href="#" link="/physical_servers/delete/{{item.id}}" text="Are you sure to delete this item?"
                        class="btn btn-default btn-xs delete-tableslide-item" data-toggle="tooltip" data-placement="top" title="Remove this server">
                        <i class="fa fa-trash-o"></i>
                    </a>
                            
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                Customer:
            </td>
            <td>
                {{item.customers.printAddressText('short')}}
            </td>
        </tr>
        <tr>
            <td>
                FQDN:
            </td>
            <td>
                {{item.fqdn}}
            </td>
        </tr>
        <tr>
            <td>
                Host Type:
            </td>
            <td>
                {% if item.ovz %}OpenVZ ({{ovzSetting['Version']}}){% else %}Not connected{% endif %}
            </td>
        </tr>
        <tr>
            <td>
                Colocation:
            </td>
            <td>
                {{item.colocations.name}}
            </td>
        </tr>
        <tr>
            <td>
                Activation date:
            </td>
            <td>
                {{item.activation_date}}
            </td>
        </tr>
        <tr>
            <td>
                Description:
            </td>
            <td>
                {{item.description}}
            </td>
        </tr>
    </tbody>
</table>
