{% extends "templates/core/slidedata.volt" %}

{% block header %}
<div class="page-header">
    <h2><i class="fa fa-globe" aria-hidden="true"></i>{{ _("colocations_title")}}</h2>
</div>
{% endblock %}

{% block slideheader %}
    <div class="well well-sm">
        <form id="slidedatatoolbar" action="" method="post">
            <div class="row">
                <div class="col-sm-4 col-lg-6 buttons">
                    {{ link_to(controller~"/new",'<i class="fa fa-plus"></i>','class': 'btn btn-default createButton',
                    'title':_("colocations_new_colocation"), 'data-toggle':'tooltip') }}
                    {{ link_to(contaction~"?orderdir="~orderdir,orderdirIcon,'class': 'btn btn-default orderButton',
                    'title':_("tableslide_change_order"), 'data-toggle':'tooltip') }}
                    <span id="selectRows" class="select">
                        {{ select_static('limit',['10':'10 '~_("tableslide_rows"),'25':'25 '~_("tableslide_rows"),'50':'50 '~_("tableslide_rows"),'100':'100 '~_("tableslide_rows")],'size':'1','class':'selectpicker','data-width':'auto','onchange':'javascript: this.form.submit();') }}
                    </span>
                </div>
                <div class="col-sm-8 col-lg-6 pull-right">
                    <div id="searchFilter" class="input-group col-lg-6 col-sm-6 col-xs-12 pull-right">
                        <span class="input-group-addon" onclick="$('form#slidedatatoolbar').submit();">
                            <i class="fa fa-search" title="{{ _("tableslide_filter_search") }}" data-toggle="tooltip"></i>
                        </span>
                        {{ text_field("filterAll",'class':'form-control','placeholder':'Filter') }}
                        <input type="submit" class="hideSubmit">
                        <span class="input-group-addon" onclick="$('#filterAll').val('');$('form#slidedatatoolbar').submit();">
                            <i class="fa fa-times" title="{{ _("tableslide_filter_clear") }}" data-toggle="tooltip"></i>
                        </span>
                    </div>
                    {% if permissions.checkPermission("colocations", "filter_customers") %}
                    <div class="input-group col-xs-12 clearfix">
                        {{ hidden_field("filterCustomers_id","onchange":"$('form#slidedatatoolbar').submit();") }}
                        {{ text_field("filterCustomers",'class':'form-control autocomplete','placeholder':_("tableslide_filter_customer"))}}
                        <span class="input-group-addon" onclick="$('#filterCustomers').val('');$('#filterCustomers_id').val('');$('form#slidedatatoolbar').submit();">
                            <i class="fa fa-times" title="{{ _("tableslide_filter_clear") }}" data-toggle="tooltip"></i>
                        </span>
                    </div>
                    {% endif %}
                </div>
            </div>
        </form>
    </div>
{% endblock %}