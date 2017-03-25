<table class="table">
    <thead>
        <tr>
            <th colspan="2">
                <div class="pull-left"><h5 class="panel-title pull-left">{{ _("virtualserver_hwspec") }}</h5></div>
                <div class="pull-right">
                    <div class="btn-group">
                        {#{{ link_to("virtual_servers/todo/"~item.id,'<i class="fa fa-bar-chart"></i>','class': 'btn btn-default btn-xs') }}
                        {{ link_to("virtual_servers/todo/"~item.id,'<i class="fa fa-question-circle-o"></i>','class': 'btn btn-default btn-xs') }}#}
                        {% if item.ovz == 1 %}
                            {{ link_to("virtual_servers/virtualServersConfigure/"~item.id,'<i class="fa fa-wrench"></i>','class': 'btn btn-default btn-xs loadingScreen') }}
                        {% endif %}
                    </div>
                </div>
            </th>
        </tr>
    </thead>
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
                {{(item.space*1024*1024)|formatBytesHelper}}
            </td>
        </tr>
    </tbody>
</table>
