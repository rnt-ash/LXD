<div class="clearfix panel panel-default sub-panel">
    {% set defaultState = 'hide' %}
    {% set state = slideSectionState(item.id,'ColocationsController','physicalservers',defaultState) %}
    <div class="panel-heading">
        <span role="button" data-target="#slide_section_physicalservers_{{item.id}}" onclick="toggleSectionState('slide_section_physicalservers_{{item.id}}','colocations','{{defaultState}}',this)" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="slide_section_physicalservers_{{item.id}}_icon" class="fa fa-chevron-{% if state == 'show' %}down{% else %}right{% endif %}"></i>&nbsp;{{ _("colocations_view_physicalserver") }}
            </h5>
        </span>
    </div>
    <div id="slide_section_physicalservers_{{item.id}}" class="panel-collapse collapse {% if state == 'show' %}in{% endif %}">
        <table class="table table-condensed table-striped table-hover">
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