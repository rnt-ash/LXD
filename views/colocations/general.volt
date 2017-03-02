<table class="table">
    <thead>
        <tr>
            <th colspan="2">
                <div class="pull-left">
                    <h5 class="panel-title pull-left">{{ _("colocations_generalinfo") }}</h5>
                </div>
                <div class="pull-right">
                    {{ link_to("colocations/edit/"~item.id,'<i class="fa fa-pencil"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("colocations_editovz")) }}
                    <a href="#" link="/colocations/delete/{{item.id}}" text="{{ _("colocations_view_delmessage") }}"
                        class="btn btn-default btn-xs confirm-button" data-toggle="tooltip" data-placement="top" title="Delete{{ _("colocations_delcolocation") }}"><i class="fa fa-trash-o"></i></a>

                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                {{ _("colocations_view_customer") }}
            </td>
            <td>
                {{item.customers.printAddressText('short')}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("colocations_view_activdate") }}
            </td>
            <td>
                {{item.activation_date}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("colocations_view_description") }}
            </td>
            <td>
                {{item.description}}
            </td>
        </tr>
    </tbody>
</table>
