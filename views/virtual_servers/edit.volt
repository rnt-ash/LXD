{# Edit virtial server form #}

{%- macro renderElement(element, form) %}
    {% if form.has(element) %}
        {% if form.hasMessagesFor(element) %}{% set error = 'has-error has-feedback' %}{% else %}{% set error = '' %}{% endif %}
        <div class="form-group {{error}} ">
            {{ form.get(element).label(['class': 'control-label']) }}
            {{ form.get(element).render(['class': 'form-control']) }}
            {% if form.hasMessagesFor(element) %}
                <span class="form-control-feedback"><i class="fa fa-exclamation-triangle"></i></span>
                <span class="help-block">{{form.getMessagesFor(element)[0]}}</span>
            {% endif %}
        </div>
    {% endif %}
{%- endmacro %}    

<div class="page-header">
    <h2><i class="fa fa-server" aria-hidden="true"></i> Virtual Servers</h2>
</div>

<div class="well">
    {{ form("virtual_servers/save", 'role': 'form') }}
    {{ form.get('id').render() }}

    {% if form.hasMessagesFor('id') %}
        <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
    {% endif %}

    {{ renderElement('name',form) }}
    {{ renderElement('customers_id',form) }}
    {{ renderElement('physical_servers_id',form) }}
    {{ renderElement('password',form) }}
    {{ renderElement('ostemplate',form) }} 
    {{ renderElement('distribution',form) }}
    {{ renderElement('core',form) }}
    {{ renderElement('memory',form) }}
    {{ renderElement('space',form) }}
    {{ renderElement('activation_date',form) }}
    {{ renderElement('description',form) }}

    {{ submit_button("Save", "class": "btn btn-primary") }}
    {{ link_to('/virtual_servers/slidedata', 'Cancel', 'class': 'btn btn-default pull-right') }}
            
    </form>
</div>
