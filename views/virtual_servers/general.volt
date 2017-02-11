<table class="table">
    <thead>
        <tr>
            <th colspan="2">
                <div class="pull-left">
                    <h5 class="panel-title pull-left">General informations</h5>
                </div>
                <div class="pull-right">
                    {% if item.ovz == 1 %}
                    <div class="btn-group">
                        {% if acl.isAllowed("myRole" ,"virtual_servers", "changestate") %}
                        <button type="button" class="btn btn-success dropdown-toggle btn-xs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-lightbulb-o text-default"></i>&nbsp;<span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>{{ link_to("virtual_servers/startVS/"~item.id,'<i class="fa fa-play"></i> start') }}</li>
                            <li>{{ link_to("virtual_servers/stopVS/"~item.id,'<i class="fa fa-ban"></i> stop') }}</li>
                            <li>{{ link_to("virtual_servers/restartVS/"~item.id,'<i class="fa fa-retweet"></i> restart') }}</li>
                        </ul>
                        {% endif %}
                    </div>
                    {% endif %}
                    
                    {{ link_to("virtual_servers/edit/"~item.id,'<i class="fa fa-pencil"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':'Edit OVZ settings') }}
                    {% if item.ovz == 1 %}
                        {{ link_to("virtual_servers/ovzListInfo/"~item.id,'<i class="fa fa-refresh"></i>',
                            'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':'Update OVZ settings') }}
                        {# not yet implementet
                        {{ link_to("virtual_servers/todo/"~item.id,'<i class="fa fa-key"></i>',
                            'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':'Set new password') }}
                        #}
                    {% endif %}
                    <a href="#" link="/virtual_servers/delete/{{item.id}}" text="Are you sure to delete this item?"
                        class="btn btn-default btn-xs confirm-button" data-toggle="tooltip" data-placement="top" title="Delete virtual server"><i class="fa fa-trash-o"></i></a>

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
                Physical Server:
            </td>
            <td>
                {{item.physicalServers.name}}
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
                State:
            </td>
            <td>
                {{item.ovz_state}}
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
