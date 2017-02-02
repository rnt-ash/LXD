<table class="table">
    <thead>
        <tr>
            <th colspan="3">
                <div class="pull-left"><h5 class="panel-title pull-left">Physical Servers</h5></div>
            </th>
        </tr>
    </thead>
    <tbody>
    {% for index, physical_server in item.physicalServers %}
        <tr>
            <td>
                {{physical_server.name}}
            </td>
            <td>
                {{physical_server.fqdn}}
            </td>
            <td>
                {{physical_server.activation_date}}
            </td>
        </tr>
    {% endfor %}
    </tbody>
</table>
