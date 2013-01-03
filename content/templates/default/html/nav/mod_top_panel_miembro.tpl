<table style="width: 100%;" id="top_panel">
<tr>
    <td style="padding: 10px 0px; padding-bottom: 15px; width: 400px;">
        <img style="float: left; margin-right: 20px;" src="{$user_pic}" />
        <div><h1 style="border: 0px; padding-bottom: 0px;">{$system.user.nombre_display}</h1></div>
        <div style="overflow: hidden; margin-top: 3px;">
            <span style="font-size:18px; color: #88979f; float: left; font-family: museo-sans, arial; font-weight: 500; margin-top: 4px">Nivel <span style="color: #333">{$nivel.nivel}</span></span>
            <div id="lvlbar" style="">
                <a class="progress" tooltip="{$nivel.puntos} puntos de {$nivel.limite_superior} que necesitas para el siguiente nivel" style="width:{$nivel.porcentaje}%;"></a>
            </div>
        </div>
    </td>
    <td class="notifications" style="vertical-align: middle; width: 500px; text-align: right;">
        {if:$n_preguntas} <a href="{url:cuenta/preguntas}" gravity="ne" tooltip="Tienes {$n_preguntas} preguntas abiertas"><img  class="signal" style="margin-right: 15px;" src="{$system.path(img)}8_peloteo.png" /><span class="new">{$n_preguntas}</span></a>{else}<img gravity="ne"  tooltip="No tienes preguntas abiertas" class="signal" style="margin-right: 15px; opacity: 0.4;" src="{$system.path(img)}8_peloteo.png" />{/if}
        {if:$n_premios}<a href="{url:cuenta/proyectos}" gravity="ne" tooltip="Eres ganador de {$n_premios} torneo(s)"><img  class="signal" style="margin-right: 15px;" src="{$system.path(img)}8_estrella.png" /><span class="new">{$n_premios}</span></a>{else}<img gravity="ne" tooltip="No eres ganador de un torneo en este momento" class="signal" style="margin-right: 15px; opacity: 0.4;" src="{$system.path(img)}8_estrella.png" />{/if}
        {if:$n_ideas}<a href="{url:cuenta/proyectos}" gravity="ne" tooltip="Tienes {$n_ideas} nuevas propuestas en tu torneo"><img  class="signal" style="margin-right: 15px;" src="{$system.path(img)}8_idea.png" /><span class="new">{$n_ideas}</span></a>{else}<img gravity="ne" tooltip="No tienes nuevas ideas en tus torneos" class="signal" style="margin-right: 15px; opacity: 0.4;" src="{$system.path(img)}8_idea.png" />{/if}
        {if:$n_mensajes}<a href="{url:cuenta/mensajes}" gravity="ne" tooltip="Tienes {$n_mensajes} mensajes sin leer"><img  class="signal" style="margin-right: 15px;" src="{$system.path(img)}8_mensaje.png" /><span class="new">{$n_mensajes}</span></a>{else}<img gravity="ne" tooltip="No tiene nuevos mensajes" class="signal" style="margin-right: 15px; opacity: 0.4;" src="{$system.path(img)}8_mensaje.png" />{/if}
    </td>
</tr>
</table>
<div class="shadow_panel"></div>