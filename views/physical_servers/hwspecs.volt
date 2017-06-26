<div class="clearfix panel panel-default sub-panel">
    <div class="panel-heading">
        <span role="button" data-target="#hwSpecs{{item.id}}" onclick="toggleIcon('#hwSpecsToggleIcon'+{{item.id}},this)" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="hwSpecsToggleIcon{{item.id}}" class="fa fa-chevron-down"></i>&nbsp;{{ _("physicalserver_hw_title") }}
                {#<div class="pull-right">
                    {{ link_to("physical_servers/todo/"~item.id,'<i class="fa fa-bar-chart"></i>','class': 'btn btn-default btn-xs') }}
                    {{ link_to("physical_servers/todo/"~item.id,'<i class="fa fa-question-circle-o"></i>','class': 'btn btn-default btn-xs') }}
                </div>#}
            </h5>
        </span>
    </div>
    <div id="hwSpecs{{item.id}}" class="panel-collapse collapse in">
        <table class="table table-condensed">
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
    </div>
</div>