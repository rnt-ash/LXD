{% extends "templates/core/slidedata.volt" %}

{% block header %}
<div class="page-header">
    <h2><i class="fa fa-server" aria-hidden="true"></i> {{ _("physicalserver_slide_title") }}</h2>
</div>
{% endblock %}

{% block slideheader %}
    <div class="well well-sm">
        <form id="slidedatatoolbar" action="" method="post">
            <div class="row">
                <div class="col-sm-4 col-lg-6 buttons">
                    {{ link_to(controller~"/new",'<i class="fa fa-plus"></i>','class': 'btn btn-default createButton',
                        'title':_("physicalservers_new_physicalserver"), 'data-toggle':'tooltip') }}
                    {{ link_to(contaction~"?orderdir="~orderdir,orderdirIcon,'class': 'btn btn-default orderButton',
                        'title':_("tableslide_change_order"), 'data-toggle':'tooltip') }}
                    <label id="selectRows" class="select">
                        {{ select_static('limit',['10':'10 '~_("tableslide_rows"),'25':'25 '~_("tableslide_rows"),'50':'50 '~_("tableslide_rows"),'100':'100 '~_("tableslide_rows")],'size':'1','class':'form-control','onchange':'javascript: this.form.submit();') }}
                    </label>
                </div>
                <div class="col-sm-8 col-lg-6 serverFilter pull-right">
                    {% if permissions.checkPermission("physical_servers", "filter_colocations") and colocations is not empty %}
                    <label class="select col-lg-6 col-sm-6 col-xs-12 colocationsFilterLabel">
                        {{ select_static('filterColocations',colocations,'size':'1','class':'form-control','onchange':'javascript: this.form.submit();') }}
                    </label>
                    {% endif %}
                    <div id="searchFilter" class="input-group col-lg-6 col-sm-6 col-xs-12 pull-right">
                        <span class="input-group-addon"><i class="fa fa-search" onclick="$('form#slidedatatoolbar').submit();"></i></span>
                        {{ text_field("filterAll",'class':'form-control','placeholder':'Filter') }}
                        <input type="submit" style="display: none;">
                        <span class="input-group-addon" title="{{ _("tableslide_filter_clear") }}" data-toggle="tooltip">
                            <i class="fa fa-times" onclick="$('#filterAll').val('');$('form#slidedatatoolbar').submit();"></i>
                        </span>
                    </div>
                    {% if permissions.checkPermission("physical_servers", "filter_customers") %}
                    <div class="input-group col-xs-12 clearfix">
                        {{ hidden_field("filterCustomers_id","onchange":"$('form#slidedatatoolbar').submit();") }}
                        {{ text_field("filterCustomers",'class':'form-control autocomplete','placeholder':_("tableslide_filter_customer"))}}
                        <span class="input-group-addon" title="{{ _("tableslide_filter_clear") }}" data-toggle="tooltip">
                            <i class="fa fa-times" onclick="$('#filterCustomers').val('');$('#filterCustomers_id').val('');$('form#slidedatatoolbar').submit();"></i>
                        </span>
                    </div>
                    {% endif %}
                </div>
            </div>
        </form>
    </div>
{% endblock %}
