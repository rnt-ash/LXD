<div class="clearfix panel panel-default sub-panel">
    <div class="panel-heading">
        <span role="button" data-target="#hwSpecs{{item.id}}" onclick="toggleIcon('#hwSpecsToggleIcon'+{{item.id}})" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="hwSpecsToggleIcon{{item.id}}" class="fa fa-chevron-down"></i>&nbsp;{{ _("virtualserver_hwspec") }}
                <div class="pull-right">
                    <div class="btn-group">
                        {#{{ link_to("virtual_servers/todo/"~item.id,'<i class="fa fa-bar-chart"></i>','class': 'btn btn-default btn-xs') }}
                        {{ link_to("virtual_servers/todo/"~item.id,'<i class="fa fa-question-circle-o"></i>','class': 'btn btn-default btn-xs') }}#}
                        {% if item.ovz == 1 %}
                            {% if permissions.checkPermission("virtual_servers", "configure") %}
                                {{ link_to("virtual_servers/virtualServersConfigure/"~item.id,'<i class="fa fa-wrench"></i>','class': 'btn btn-default btn-xs loadingScreen pending') }}
                            {% endif %}
                        {% endif %}
                    </div>
                </div>
            </h5>
        </span>
    </div>
    <div id="hwSpecs{{item.id}}" class="panel-collapse collapse in">
        <table class="table table-condensed">
            <tbody>
                <tr>
                    <td>
                        {{ _("virtualserver_hwspec_cpu") }}
                    </td>
                    <td>
                        {{item.core}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ _("virtualserver_hwspec_memory") }}
                    </td>
                    <td>
                        {{(item.memory*1024*1024)|formatBytesHelper}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ _("virtualserver_hwspec_space") }}
                    </td>
                    <td>
                        {{(item.space*1024*1024*1024)|formatBytesHelper}}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>