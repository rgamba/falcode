<?php
/**
* [Table:usuario]
* Usuario.php
* 
* @package     BM
* @author      $Autor$
* @copyright   $Copyright$
* @version     $Version$
* @access      public
* @uses        ActiveRecord
*/
class Usuario extends ActiveRecord{
    /**
    * Table name definition
    */
    protected static $table_name="usuario";
    /**
    * Primary key column name
    */
    protected static $primary_key="id_usuario";
    /**
    * Prefix
    */
    protected static $prefix="u";
    /**
    * Foreign key & relations definition
    */
    protected static $fk=array( 'UsuarioRol' => array( 'table' => 'usuario_rol', 'local_key' => 'id_usuario_rol', 'foreign_key' => 'id_usuario_rol', 'instance' => null, 'rel_type' => Db::REL_MULTIPLE_TO_ONE ), 'UsuarioPortafolio' => array( 'table' => 'usuario_portafolio', 'local_key' => 'id_usuario', 'foreign_key' => 'id_usuario', 'instance' => null, 'rel_type' => Db::REL_ONE_TO_MULTIPLE ) );

    public function getRank($id_usuario){
        $Db = Db::getInstance();
        $qry = $Db->fetch("SELECT u.*,AVG(e.rank)+(COUNT(*)/10) as rank, (select count(*) from premio where id_usuario = u.id_usuario and lugar = 1) as premios
            FROM usuario u
            INNER JOIN envio e using(id_usuario)
            WHERE e.retirado != 1
            GROUP BY id_usuario
            ORDER BY premios desc, rank desc");
        $rank['total'] = $qry->num_rows;
        if($rank['total']>0){
            $i=1;
            foreach($qry->rows as $row){
                if($row['id_usuario'] == $id_usuario)
                    break;
                $i++;
            }
            $rank['individual'] = $i;
        }
        return $rank;
    }

    public function defaultImg($id=NULL){
        if(is_null($id)){
            $id = $this->id;
            if(empty($id))
                $id = $this->rs[self::$primary_key];
        }
        if(empty($id))
            return "generic.png";
        $U = new UsuarioImagen();
        $U->where("id_usuario = {0}",$id)->orderBy("principal","DESC")->limit(1)->execute();
        if($U->rows > 0){
            $U->next();
            return $U->imagen;
        }
        return "generic.png";
    }
    
    public function getImg($id=NULL){
        $Db = Db::getInstance();
        $img = $Db->getVal("usuario","id_usuario",$id,"imagen");
        if(empty($img))
            $img = "generic.png";
        return $img;
    }
    /*
     * Niveles y puntos
     *
       Tiene 50 como base
       Sección de Peloteo
          +1 por escoger mejor respuesta de tu pregunta  ***********
          +1 por cada respuesta
          +1 por cada voto a favor de cada pregunta o respuesta
           -1 por cada voto en contra de cada pregunta o respuesta
           +10 por cada respuesta seleccionada como la mejor
           -10 por cada reporte
           -5 por cada pregunta

       Sección de torneos
           +5 por enviar propuesta
           +5x donde x es el numero de estrellas del raqueo
           +500 por ganar un torneo
     *
     * */
    public function getLevel($id_usuario){
        if(empty($id_usuario))
            return false;
        $sql = "SELECT
            (SELECT  COUNT(id_pregunta_propuesta) FROM pregunta_propuesta WHERE id_usuario = '".$id_usuario."' AND (eliminado is null OR eliminado = '')) as propuestas,
            (SELECT  SUM(mejor_propuesta) FROM pregunta_propuesta WHERE id_usuario = '".$id_usuario."' AND (eliminado is null OR eliminado = '')) as propuestas_ganadoras,
            (SELECT  SUM(val) FROM voto v INNER JOIN pregunta_propuesta pp ON pp.id_pregunta_propuesta = v.id_objeto WHERE objeto = 'pregunta_propuesta' AND pp.id_usuario = '".$id_usuario."') as votos_propuestas,
            (SELECT  SUM(val) FROM voto v INNER JOIN pregunta p ON p.id_pregunta = v.id_objeto WHERE objeto = 'pregunta' AND p.id_usuario = '".$id_usuario."') as votos_preguntas,
            (SELECT  COUNT(id_pregunta) FROM pregunta WHERE  id_usuario = '".$id_usuario."') as preguntas,
            (SELECT  COUNT(id_reporte) FROM reporte r INNER JOIN pregunta p ON p.id_pregunta=r.id_objeto WHERE objeto = 'pregunta' AND p.id_usuario = '".$id_usuario."') as reportes_preguntas,
            (SELECT  COUNT(id_reporte) FROM reporte r INNER JOIN pregunta_propuesta pp ON pp.id_pregunta=r.id_objeto WHERE objeto = 'pregunta_propuesta' AND pp.id_usuario = '".$id_usuario."') as reportes_propuestas,
            (SELECT  COUNT(id_envio) FROM envio e WHERE id_usuario = '".$id_usuario."') as envios,
            (SELECT  SUM(rank) FROM envio e WHERE id_usuario = '".$id_usuario."') as estrellas,
            (SELECT  COUNT(id_premio) FROM premio p WHERE lugar='1' AND id_usuario='".$id_usuario."' ) as premios";
        $query = Sys::get("db")->fetch($sql);
        //print_r($query->row);
        // Convertimos a puntos y sumamos
            // ¿Restar lo de las preguntas?
        $puntos = 50+
        (int)$query->row['propuestas']+
        (int)$query->row['propuestas_ganadoras']*10+
        (int)$query->row['votos_propuestas']+
        (int)$query->row['votos_preguntas']+
        (int)$query->row['preguntas']*5*(-1)+
        (int)$query->row['reportes_preguntas']*10*(-1)+
        (int)$query->row['reportes_propuestas']*10*(-1)+
        (int)$query->row['envios']*5+
        (int)$query->row['estrellas']*5+
        (int)$query->row['premios']*500;

        $limite_inferior = 1;
        $limite_superior = 99;
        $incremento = 0.5;
        $nivel=1;
        //echo "<table><tr><td>Nivel</td><td>Limite inferior</td><td>Limite superior</td><td>Incremento</td></tr>";
        //echo "<tr><td>".$nivel."</td><td>".$limite_inferior."</td><td>".$limite_superior."</td><td>".$incremento."</td></tr>";
        while($limite_superior<$puntos){
            $incremento = $incremento-pow($incremento,5);
            $limite_inferior=$limite_superior+1;
            $limite_superior=round($limite_superior*(1+$incremento),0);
            $nivel++;
            //echo "<tr><td>".$nivel."</td><td>".$limite_inferior."</td><td>".$limite_superior."</td><td>".$incremento."</td></tr>";
        }
        //echo "</table>";

        $result['puntos'] = $puntos;
        $result['nivel'] = $nivel;
        $result['limite_inferior'] = $limite_inferior;
        $result['limite_superior'] = $limite_superior;
        $result['porcentaje'] = round((float)($puntos-$limite_inferior)/($limite_superior-$limite_inferior),2)*100;

        return $result;

    }

    public function selectPais(){
        $this->select("pais.iso2 as iso2")->select("pais.spanish_name as pais")->select("u.*");
        $this->join("pais USING(id_pais)");
        return $this;
    }
    
    
}