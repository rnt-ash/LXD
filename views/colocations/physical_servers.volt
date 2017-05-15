<div class="clearfix panel panel-default sub-panel">
    <div class="panel-heading">
        <span role="button" data-target="#physicalServers{{item.id}}" onclick="toggleIcon('#physicalServersToggleIcon'+{{item.id}})" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="physicalServersToggleIcon{{item.id}}" class="fa fa-chevron-right"></i>&nbsp;{{ _("colocations_view_physicalserver") }}
            </h5>
        </span>
    </div>
    <div id="physicalServers{{item.id}}" class="panel-collapse collapse">
        <table class="table table-condensed table-striped">
            <tbody>
            {% if item.physicalServers.count() == 0 %}
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
    </div>
</div>