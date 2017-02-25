{# Edit connector form #}

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
    <h2><i class="fa fa-server" aria-hidden="true"></i> Physical Servers OVZ Connector</h2>
</div>

<div class="well">
    {{ form("physical_servers/connect", 'role': 'form') }}

    {{ form.get('physical_servers_id').render() }}
    
    {% if form.hasMessagesFor('physical_servers_id') %}
        <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
    {% endif %}

    {{ renderElement('username',form) }}
    {{ renderElement('password',form) }}

    {{ submit_button("Connect", "class": "btn btn-primary") }}
    {{ link_to('/physical_servers/slidedata', 'Cancel', 'class': 'btn btn-default pull-right') }}
            
    </form>
</div>
