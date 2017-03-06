{# Edit snapshot form #}

{%- macro renderElement(element, form) %}
    {% if form.hasMessagesFor(element) %}{% set error = 'has-error has-feedback' %}{% else %}{% set error = '' %}{% endif %}
    <div class="form-group {{error}} ">
        {{ form.get(element).label(['class': 'control-label']) }}
        {{ form.get(element).render(['class': 'form-control']) }}
        {% if form.hasMessagesFor(element) %}
            <span class="form-control-feedback"><i class="fa fa-exclamation-triangle"></i></span>
            <span class="help-block">{{form.getMessagesFor(element)[0]}}</span>
        {% endif %}
    </div>
{%- endmacro %}    

<div class="page-header">
    <h2><i class="fa fa-cube" aria-hidden="true"></i>{{ _("virtualserver_snapshot") }}</h2>
</div>

<div class="well">
    {{ form("virtual_servers/ovzCreateSnapshot", 'role': 'form') }}

    {{ form.get('virtual_servers_id').render() }}
    {% if form.hasMessagesFor('virtual_servers_id') %}
        <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
    {% endif %}

    {{ renderElement('name',form) }}
    {{ renderElement('description',form) }}

    {{ submit_button(_("virtualserver_save"), "class": "btn btn-primary") }}
    {{ link_to('/virtual_servers/slidedata', _("virtualserver_cancel"), 'class': 'btn btn-default pull-right') }}
            
    </form>
</div>