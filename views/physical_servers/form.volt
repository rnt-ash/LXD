{# Edit physicalServer form #}

{{ partial("partials/core/partials/renderFormElement") }}   

<div class="page-header">
    <h2><i class="fa fa-server" aria-hidden="true"></i> {{ _("physicalserver_title") }}</h2>
</div>

<div class="well">
    <div class="row">
        {{ form("physical_servers/save", 'role': 'form') }}
        {{ form.get('id').render() }}

        {% if form.hasMessagesFor('id') %}
            <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
        {% endif %}

        <div class="clearfix">
            {{ renderElement('name',form,6) }}
            {{ renderElement('fqdn',form,6) }}
        </div>
        <div class="clearfix">
            {{ renderElement('customers',form,6,'autocomplete') }}
            {{ renderElement('colocations_id',form,6) }}
        </div>
        <div class="clearfix">
            {{ renderElement('core',form,6) }}
            {{ renderElement('memory',form,6) }}
        </div>
        <div class="clearfix">
            {{ renderElement('space',form,6) }}
            {{ renderElement('activation_date',form,6) }}
        </div>
        {{ renderElement('description',form) }}

        <div class="col-lg-12">
            {{ submit_button( _("physicalserver_save") , "class": "btn btn-primary") }}
            {{ link_to('/physical_servers/slidedata', _("physicalserver_cancel"), 'class': 'btn btn-default pull-right') }}
        </div>            
        </form>
    </div>
</div>