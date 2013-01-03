<div id="panel">
<a href="{url:ayuda/main}" class='p_button  {$system.action.if(main,selected)}'>¿Cómo funciona?</a>
<a  href="{url:ayuda/faq}" class="p_button  {$system.action.if(faq,selected)}">Preguntas Frecuentes</a>
<a  href="{url:ayuda/nivel}" class="p_button  {$system.action.if(nivel,selected)}">Nivel de creatividad</a>
<a  href="{url:ayuda/contenido?seccion=conducta_creativo}" class="p_button  {$system.request.seccion.if(conducta_creativo,selected)}">Código de conducta creativos</a>
<a  href="{url:ayuda/contenido?seccion=conducta_cliente}" class="p_button  {$system.request.seccion.if(conducta_cliente,selected)}">Código de conducta clientes</a>
<a  href="{url:ayuda/contenido?seccion=derechos_propiedad}" class="p_button  {$system.request.seccion.if(derechos_propiedad,selected)}">Derechos de propiedad</a>
<a  href="{url:ayuda/contenido?seccion=politicas_privacidad}" class="p_button  {$system.request.seccion.if(politicas_privacidad,selected)}">Políticas de privacidad</a>
<a  href="{url:ayuda/contenido?seccion=terminos_condiciones}" class="p_button  {$system.request.seccion.if(terminos_condiciones,selected)}">Términos y condiciones</a>
<a  href="{url:ayuda/contenido?seccion=politica_garantia}" class="p_button  {$system.request.seccion.if(politica_garantia,selected)}">Política de garantía</a>
<a  href="{url:ayuda/contacto}" class="p_button {$system.action.if(contacto,selected)}">Contáctanos</a>
</div>

<div style="padding: 20px 0px;">
    <form action="{url:ayuda/faq}" method="post" id="search_form">
        <input name="search" style="width: 210px; background-color: #f5f5f5;" type="text" />
        <a onclick="$('#search_form').submit();" href="#" class="start_now" style="margin-top: 10px; font-size: 14px">Buscar</a>
    </form>
</div>
