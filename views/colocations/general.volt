<table class="table">
    <thead>
        <tr>
            <th colspan="2">
                <div class="pull-left">
                    <h5 class="panel-title pull-left">General informations</h5>
                </div>
                <div class="pull-right">
                    {{ link_to("colocations/edit/"~item.id,'<i class="fa fa-pencil"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':'Edit OVZ settings') }}
                    <a href="#" link="/colocations/delete/{{item.id}}" text="Are you sure to delete this item?"
                        class="btn btn-default btn-xs confirm-button" data-toggle="tooltip" data-placement="top" title="Delete colocation"><i class="fa fa-trash-o"></i></a>

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
