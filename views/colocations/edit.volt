{# Edit customer form #}

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
    <h2><i class="fa fa-globe" aria-hidden="true"></i> Colocations</h2>
</div>

<div class="well">
    {{ form("colocations/save", 'role': 'form') }}
    {{ form.get('id').render() }}

    {% if form.hasMessagesFor('id') %}
        <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
    {% endif %}

    {{ renderElement('name',form) }}
    {{ renderElement('customers_id',form) }}
    {{ renderElement('description',form) }}
    {{ renderElement('location',form) }}
    {{ renderElement('activation_date',form) }}
    
    {{ submit_button("Save", "class": "btn btn-primary") }}
    {{ link_to('/customers/tabledata', 'Cancel', 'class': 'btn btn-default pull-right') }}
            
    </form>
</div>
