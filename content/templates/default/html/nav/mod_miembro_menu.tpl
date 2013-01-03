<a class="p_button {$system.module.if(panel,selected)}" href="{url:panel}">Novedades</a>
<a class="p_button {$system.action.if(proyectos,selected)}" href="{url:cuenta/proyectos}">Torneos</a>
<a class="p_button {$system.action.if(contratos,selected).if(contrato,selected)}" href="{url:cuenta/contratos}">Contratos</a>
<a class="p_button {$system.action.if(preguntas,selected)} {$system.action.if(respuestas,selected)}" href="{url:cuenta/preguntas}">Peloteo de ideas</a>
<a class="p_button {$system.action.if(portafolio,selected)}" href="{url:cuenta/portafolio}">Portafolio</a>
<a class="p_button {$system.action.if(transacciones,selected)}" href="{url:cuenta/transacciones}">Transacciones</a>
<a class="p_button {$system.action.if(mensajes,selected)} {$system.action.if(nuevo_mensaje,selected)} {$system.action.if(ver_mensaje,selected)}" href="{url:cuenta/mensajes}">Mensajes</a>
<a class="p_button {$system.action.if(preferencias,selected)}" href="{url:cuenta/preferencias}">Preferencias</a>
<a class="p_button {$system.action.if(perfil,selected)}" href="{url:cuenta/perfil}">Mi perfil</a>