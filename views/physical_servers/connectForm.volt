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
    <h2><i class="fa fa-server" aria-hidden="true"></i> {{ _("physicalserver_connect_title") }}</h2>
</div>

<div>
    {{ _("physicalserver_connection_stepsbefore") }}
    <br /><br />
<pre class="well"><code>yum -y update
yum -y install mc ntp wget mailx nano php-cli php-pdo
ssh-keygen -b 2048 -t rsa -f /root/.ssh/id_rsa -q -N ""</code></pre>
</div>
<hr />

<div class="well">
    {{ form("physical_servers/connect", 'role': 'form') }}

    {{ form.get('physical_servers_id').render() }}
    
    {% if form.hasMessagesFor('physical_servers_id') %}
        <div class="alert alert-danger" role="alert">{{form.getMessagesFor('id')[0]}}</div>  
    {% endif %}

    {{ renderElement('username',form) }}
    {{ renderElement('password',form) }}

    {{ submit_button( _("physicalserver_connect_connectbutton") , "class": "btn btn-primary") }}
    {{ link_to('/physical_servers/slidedata', _("physicalserver_cancel"), 'class': 'btn btn-default pull-right') }}
            
    </form>
</div>
