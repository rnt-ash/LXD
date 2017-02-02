
{% set controller = dataInfo['controller'] %}
{% set contaction = controller~"/"~dataInfo['action'] %}
{% set orderdir = dataInfo['orderdir']=='asc'?'desc':'asc' %}
{% set orderdirIcon = orderdir=='asc'?'<i class="fa fa-arrow-down"></i>':'<i class="fa fa-arrow-up"></i>' %}

{% block header %}
<div class="page-header">
    <h2><i class="fa fa-globe" aria-hidden="true"></i> Colocations</h2>
</div>
{% endblock %}

<div class="well well-sm">
    <form id="slidedatatoolbar" action="" method="get">
    
        <div class="row">
            <div class="col-sm-5">
                {{ link_to(controller~"/new",'<i class="fa fa-plus"></i>','class': 'btn btn-default') }}
                {{ link_to(contaction~"?orderdir="~orderdir,orderdirIcon,'class': 'btn btn-default') }}
                <label class="select">
                    {{ select_static('limit',['10':'10 rows','25':'25 rows','50':'50 rows','100':'100 rows'],'size':'1','class':'form-control','onchange':'javascript: this.form.submit();') }}
                </label>
            </div>
            <div class="col-sm-2"></div>
            <div class="col-sm-5">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-search" onclick="$('form#slidedatatoolbar').submit();"></i></span>
                    {{ text_field("filterAll",'class':'form-control','placeholder':'Filter') }}
                    <span class="input-group-addon"><i class="fa fa-times" onclick="$('#filterAll').val('');$('form#slidedatatoolbar').submit();"></i></span>
                </div>
            </div>
        </div>
    </form>
</div>

{# Flash Message #}
{{ flashSession.output() }}
{{ flash.output() }}

<div class="panel-group" role="tablist">
    {% if slides is defined %}
    {{ slides }}
    {% endif %}
</div>

<div class="well well-sm">
    <div class="text-center">
        {# Paginator #}
        {{ partial("partials/paginator") }}
    </div>
</div>
