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
                {% if permissions.checkPermission("virtual_servers", "new") %}
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
                {{ link_to(contaction~"?orderdir="~orderdir,orderdirIcon,'class': 'btn btn-default orderButton',
                    'title':_("tableslide_change_order"), 'data-toggle':'tooltip') }}
                <span id="selectRows" class="select">
                    {{ select_static('limit',['10':'10 '~_("tableslide_rows"),'25':'25 '~_("tableslide_rows"),'50':'50 '~_("tableslide_rows"),'100':'100 '~_("tableslide_rows")],'size':'1','class':'selectpicker','data-width':'auto','onchange':'javascript: this.form.submit();') }}
                </span>
            </div>
            <div class="col-sm-8 col-lg-6 serverFilter pull-right">
                <div class="col-xs-12 input-group">
                    {% if permissions.checkPermission("virtual_servers", "filter_physical_servers") and physicalServers is not empty %}
                    <div class="select col-lg-6 col-sm-6 col-xs-12 physicalServersFilterLabel">
                        <select name="filterPhysicalServers" class="selectpicker" data-live-search="true" data-width="100%" data-size="15" onchange="javascript: this.form.submit();">
                            {% for id,physicalServer in physicalServers %}
                                <option value="{{ id }}" data-subtext="{{ physicalServer['count'] }}" {% if physicalServer['selected'] is not empty %}selected{% endif %}>
                                    {{ physicalServer['name'] }}
                                </option>
                            {% endfor %}
                        </select>
                    </div>
                    {% endif %}
                    <div id="searchFilter" class="input-group col-lg-6 col-sm-6 col-xs-12 pull-right">
                        <span class="input-group-addon"><i class="fa fa-search" onclick="$('form#slidedatatoolbar').submit();"></i></span>
                        {{ text_field("filterAll",'class':'form-control','placeholder':'Filter Virtual Servers') }}
                        <input type="submit" style="display: none;">
                        <span class="input-group-addon" title="{{ _("tableslide_filter_clear") }}" data-toggle="tooltip">
                            <i class="fa fa-times" onclick="$('#filterAll').val('');$('form#slidedatatoolbar').submit();"></i>
                        </span>
                    </div>
                </div>
                {% if permissions.checkPermission("virtual_servers", "filter_customers") %}
                <div class="input-group col-xs-12 clearfix filterCustomer">
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
