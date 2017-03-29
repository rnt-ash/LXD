{# Edit DCO IP Objects form #}

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
    <h2><i class="fa fa-bolt" aria-hidden="true"></i>{{ _("ipobjects_edit_title") }}</h2>
</div>

<div class="well">
    {{ form("ip_objects/save", 'role': 'form') }}
    {{ form.get('id').render() }}
    {% if form.has('version') %} {{ form.get('version').render() }} {% endif %}
    {% if form.has('type') %} {{ form.get('type').render() }} {% endif %}

    {% if form.hasMessagesFor('id') %}
        <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
    {% endif %}

    {{ renderElement('value1',form) }}
    {{ renderElement('value2',form) }}
    {{ renderElement('main',form) }}
    {{ renderElement('allocated',form) }}
    {{ renderElement('comment',form) }}

    {{ submit_button(_("ipobjects_edit_save"), "class": "btn btn-primary") }}
    {{ link_to('/ipobjects/cancel', _("ipobjects_edit_cancel"), 'class': 'btn btn-default pull-right') }}
            
    </form>
</div>
