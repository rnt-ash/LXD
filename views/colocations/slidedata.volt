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
                    {{ link_to(controller~"/new",'<i class="fa fa-plus"></i>','class': 'btn btn-default createButton') }}
                    {{ link_to(contaction~"?orderdir="~orderdir,orderdirIcon,'class': 'btn btn-default orderButton') }}
                    <label id="selectRows" class="select">
                        {{ select_static('limit',['10':'10 rows','25':'25 rows','50':'50 rows','100':'100 rows'],'size':'1','class':'form-control','onchange':'javascript: this.form.submit();') }}
                    </label>
                </div>
                <div class="col-sm-8 col-lg-6 pull-right">
                    <div id="searchFilter" class="input-group col-lg-6 col-sm-6 col-xs-12 pull-right">
                        <span class="input-group-addon"><i class="fa fa-search" onclick="$('form#slidedatatoolbar').submit();"></i></span>
                        {{ text_field("filterAll",'class':'form-control','placeholder':'Filter') }}
                        <span class="input-group-addon"><i class="fa fa-times" onclick="$('#filterAll').val('');$('form#slidedatatoolbar').submit();"></i></span>
                    </div>
                    {% if permissions.checkPermission("colocations", "filter_customers") %}
                    <div class="input-group col-xs-12 clearfix">
                        <input type="hidden" name="filterCustomers_id" onchange="$('form#slidedatatoolbar').submit();">
                        {{ text_field("filterCustomers",'class':'form-control autocomplete','placeholder':'Kunde')}}
                        <span class="input-group-addon"><i class="fa fa-times" onclick="$('#filterCustomers').val('');$('form#slidedatatoolbar').submit();"></i></span>
                    </div>
                    {% endif %}
                </div>
            </div>
        </form>
    </div>
{% endblock %}