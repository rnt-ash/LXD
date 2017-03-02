<table class="table">
    <thead>
        <tr>
            <th colspan="2">
                <div class="pull-left"><h5 class="panel-title pull-left">{{ _("physicalserver_hw_title") }}</h5></div>
                {#
                <div class="pull-right">
                    <div class="btn-group">
                        {{ link_to("physical_servers/todo/"~item.id,'<i class="fa fa-bar-chart"></i>','class': 'btn btn-default btn-xs') }}
                        {{ link_to("physical_servers/todo/"~item.id,'<i class="fa fa-question-circle-o"></i>','class': 'btn btn-default btn-xs') }}
                    </div>
                </div>
                #}
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                {{ _("physicalserver_hw_cores") }}
            </td>
            <td>
                {{item.core}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("physicalserver_hw_ram") }}
            </td>
            <td>
                {{(item.memory*1024*1024)|formatBytesHelper}}
            </td>
        </tr>
        <tr>
            <td>
                {{ _("physicalserver_hw_space") }}
            </td>
            <td>
                {{(item.space*1024*1024*1024)|formatBytesHelper}}
            </td>
        </tr>
    </tbody>
</table>
