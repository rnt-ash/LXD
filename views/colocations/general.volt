<div class="clearfix panel panel-default sub-panel">
    {% set state = slideSectionState(item.id,'ColocationsController','general') %}
    <div class="panel-heading">
        <span role="button" data-target="#slide_section_general_{{item.id}}" onclick="toggleSectionState('slide_section_general_{{item.id}}','colocations','',this)" data-toggle="collapse">
            <h5 class="panel-title">
                <i id="slide_section_general_{{item.id}}_icon" class="fa fa-chevron-{% if state == 'show' %}down{% else %}right{% endif %}"></i>&nbsp;{{ _("colocations_generalinfo") }}
                <div class="pull-right">
                    {{ link_to("colocations/generateIpPdf/"~item.id,'<i class="fa  fa-file-pdf-o"></i>','class': 'btn btn-default btn-xs', 'target': '_blank', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("colocations_createpdf")) }}
                    {{ link_to("colocations/edit/"~item.id,'<i class="fa fa-pencil"></i>',
                        'class': 'btn btn-default btn-xs', 'data-toggle':'tooltip', 'data-placement':'top', 'title':_("colocations_editovz")) }}
                    <a href="#" link="/colocations/delete/{{item.id}}" text="{{ _("colocations_view_delmessage") }}"
                        class="btn btn-default btn-xs confirm-button" data-toggle="tooltip" data-placement="top" title="Delete{{ _("colocations_delcolocation") }}"><i class="fa fa-trash-o"></i></a>
                </div>
            </h5>
        </span>
    </div>
    <div id="slide_section_general_{{item.id}}" class="panel-collapse collapse {% if state == 'show' %}in{% endif %}">
        <table class="table table-condensed">
            <tbody>
                <tr>
                    <td>
                        {{ _("colocations_view_customer") }}
                    </td>
                    <td>
                        {{item.customer.printAddressText('short')}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ _("colocations_view_activdate") }}
                    </td>
                    <td>
                        {{item.activation_date}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ _("colocations_view_description") }}
                    </td>
                    <td>
                        {{item.description}}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
