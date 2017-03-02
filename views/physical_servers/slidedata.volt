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
                <div class="col-sm-4">
                    {{ link_to(controller~"/new",'<i class="fa fa-plus"></i>','class': 'btn btn-default') }}
                    {{ link_to(contaction~"?orderdir="~orderdir,orderdirIcon,'class': 'btn btn-default') }}
                    <label class="select">
                        {{ select_static('limit',['10':'10 rows','25':'25 rows','50':'50 rows','100':'100 rows'],'size':'1','class':'form-control','onchange':'javascript: this.form.submit();') }}
                    </label>
                </div>
                <div class="col-sm-4"></div>
                <div class="col-sm-4">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search" onclick="$('form#slidedatatoolbar').submit();"></i></span>
                        {{ text_field("filterAll",'class':'form-control','placeholder':'Filter') }}
                        <span class="input-group-addon"><i class="fa fa-times" onclick="$('#filterAll').val('');$('form#slidedatatoolbar').submit();"></i></span>
                    </div>
                    {% if permissions.checkPermission("physical_servers", "filter_customers") %}
                    <label class="select">
                        {{ select_static('filterCustomers',customers,'size':'1','class':'form-control','onchange':'javascript: this.form.submit();') }}
                    </label>
                    {% endif %}
                    {% if permissions.checkPermission("physical_servers", "filter_colocations") and colocations is not empty %}
                    <label class="select">
                        {{ select_static('filterColocations',colocations,'size':'1','class':'form-control','onchange':'javascript: this.form.submit();') }}
                    </label>
                    {% endif %}
                </div>
            </div>
        </form>
    </div>
{% endblock %}
