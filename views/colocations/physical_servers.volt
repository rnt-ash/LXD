<table class="table">
    <thead>
        <tr>
            <th colspan="3">
                <div class="pull-left"><h5 class="panel-title pull-left">{{ _("colocations_view_physicalserver") }}</h5></div>
            </th>
        </tr>
    </thead>
    <tbody>
    {% if item.dcoipobjects.count() == 0 %}
        <tr colspan="3">
            <td>
                {{ _("colocations_view_nophysicalserver") }}
            </td>
        </tr>
    {% else %}
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
    {% endif %}
    </tbody>
</table>
