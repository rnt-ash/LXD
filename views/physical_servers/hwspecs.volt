<div class="clearfix panel panel-default sub-panel">
    {% set state = slideSectionState(item.id,'PhysicalServersController','hwspecs') %}
    <div class="panel-heading">
        <span role="button" data-target="#slide_section_hwspecs_{{item.id}}" onclick="toggleSectionState('slide_section_hwspecs_{{item.id}}','physical_servers','',this)" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="slide_section_hwspecs_{{item.id}}_icon" class="fa fa-chevron-{% if state == 'show' %}down{% else %}right{% endif %}"></i>&nbsp;{{ _("physicalserver_hw_title") }}
                <div class="pull-right">
                    {{ link_to("physical_servers/edit/"~item.id,'<i class="fa fa-wrench"></i>',
                            'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title': _("physicalserver_general_editsettings") ) }}
                    {#{{ link_to("physical_servers/todo/"~item.id,'<i class="fa fa-bar-chart"></i>','class': 'btn btn-default btn-xs') }}
                    {{ link_to("physical_servers/todo/"~item.id,'<i class="fa fa-question-circle-o"></i>','class': 'btn btn-default btn-xs') }}#}
                </div>
            </h5>
        </span>
    </div>
    <div id="slide_section_hwspecs_{{item.id}}" class="panel-collapse collapse {% if state == 'show' %}in{% endif %}">
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