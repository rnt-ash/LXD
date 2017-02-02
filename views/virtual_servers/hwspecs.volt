<table class="table">
    <thead>
        <tr>
            <th colspan="2">
                <div class="pull-left"><h5 class="panel-title pull-left">HW Specs</h5></div>
                {#
                <div class="pull-right">
                    <div class="btn-group">
                        {{ link_to("virtual_servers/todo/"~item.id,'<i class="fa fa-bar-chart"></i>','class': 'btn btn-default btn-xs') }}
                        {{ link_to("virtual_servers/todo/"~item.id,'<i class="fa fa-question-circle-o"></i>','class': 'btn btn-default btn-xs') }}
                    </div>
                </div>
                #}
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                CPU-Cores:
            </td>
            <td>
                {{item.core}}
            </td>
        </tr>
        <tr>
            <td>
                Memory (RAM):
            </td>
            <td>
                {{(item.memory*1024*1024)|formatBytesHelper}}
            </td>
        </tr>
        <tr>
            <td>
                Space:
            </td>
            <td>
                {{(item.space*1024*1024*1024)|formatBytesHelper}}
            </td>
        </tr>
    </tbody>
</table>
