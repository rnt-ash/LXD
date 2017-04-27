{% extends "templates/core/slidedata.volt" %}

{% block header %}
<div class="page-header">
    <h2><i class="fa fa-cube" aria-hidden="true"></i>{{ _("virtualserver_title") }}</h2>
</div>
{% endblock %}

{% block slideheader %}
<div class="well well-sm">
    <form id="slidedatatoolbar" action="" method="post">
        <div class="row">
            <div class="col-sm-4 col-lg-6 buttons">
                {% if permissions.checkPermission("virtual_servers", "create") %}
                <div class="btn-group createButton">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <b>{{ _("virtualserver_view_new") }}</b> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="/{{controller}}/newVS">{{ _("virtualserver_view_independentsys") }}</a></li>
                        <li><a href="/{{controller}}/newCT">{{ _("virtualserver_view_container") }}</a></li>
                        <li><a href="/{{controller}}/newVM">{{ _("virtualserver_view_vm") }}<br />{{ _("virtualserver_view_vm_beta") }}</a></li>
                    </ul>
                </div>  
                {% endif %}  
                {{ link_to(contaction~"?orderdir="~orderdir,orderdirIcon,'class': 'btn btn-default orderButton') }}
                <label id="selectRows" class="select">
                    {{ select_static('limit',['10':'10 rows','25':'25 rows','50':'50 rows','100':'100 rows'],'size':'1','class':'form-control','onchange':'javascript: this.form.submit();') }}
                </label>
            </div>
            <div class="col-sm-8 col-lg-6 serverFilter pull-right">
                {% if permissions.checkPermission("virtual_servers", "filter_physical_servers") and physicalServers is not empty %}
                <label class="select col-lg-6 col-sm-6 col-xs-12 physicalServersFilterLabel">
                    {{ select_static('filterPhysicalServers',physicalServers,'size':'1','class':'form-control','onchange':'javascript: this.form.submit();') }}
                </label>
                {% endif %}
                <div id="searchFilter" class="input-group col-lg-6 col-sm-6 col-xs-12 pull-right">
                    <span class="input-group-addon"><i class="fa fa-search" onclick="$('form#slidedatatoolbar').submit();"></i></span>
                    {{ text_field("filterAll",'class':'form-control','placeholder':'Filter Virtual Servers') }}
                    <input type="submit" style="display: none;">
                    <span class="input-group-addon"><i class="fa fa-times" onclick="$('#filterAll').val('');$('form#slidedatatoolbar').submit();"></i></span>
                </div>
                {% if permissions.checkPermission("virtual_servers", "filter_customers") %}
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
