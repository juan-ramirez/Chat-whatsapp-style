<?php 
include 'json.php';
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Content-Type: text/html;charset=utf-8");

class Conectar {

     public static function con() {


  

 
  //    $conexion = mysql_connect("localhost", "iselcruc_omar", "nf9ckpg") or
      $conexion = mysql_connect("localhost", "root", "") or
                 
                  die("Error de conexion: " . mysql_error());
     
      mysql_select_db("bindsme") or

  // mysql_select_db("iselcruc_ejemplo") or
                
                  die("Error de conexion: " . mysql_error());

          mysql_query("SET NAMES 'utf-8'");

 
          return $conexion;
     }

}


 

//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//********************                                                                                          ****************************
//********************       CLASE LOGEO  , REGISTRAR USUARIOS , INICIAR SESION , CHECAR DISPONIBILIDAD DE USER ***************************
//********************                                                                                          **************************** 
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//********************                                                                                          ****************************
//********************       CLASE LOGEO  , REGISTRAR USUARIOS , INICIAR SESION , CHECAR DISPONIBILIDAD DE USER ***************************
//********************                                                                                          **************************** 
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************



class login{ 

	public function __construct() {
		
		$data = array();
 
	}



//REGISTRO
public 	function sing_up(){

 
	extract($_POST);

	$img = "img/user.jpg";

	$pss_ = md5( $pss_);

	$sql = "INSERT INTO user values(null , '$user_' , '$pss_' , '" . strtotime(date("Y-m-d H:i:s")) . " ', '$email_','$img') ";

 
	mysql_query($sql, Conectar::con());

	$id_user = mysql_insert_id();

	$_SESSION["loged"] = true;
	$_SESSION["username"] = $user_;
	$_SESSION["id_user"] = $id_user;

	 header("Location:   ../home");


}
//-----------------------------------------------------------------------------------------------------------------------------------------
//INICIAR SESION
public 	function session(){



	extract($_POST);

	$pss_ = md5( $pss_);

		$sql = "SELECT  
		 		count( id_user ) as is_available , id_user , username
				FROM user
				WHERE username = '$user_'
				AND password = '$pss_' ";


   $response =  mysql_query($sql, Conectar::con());


   while ( $parse = mysql_fetch_array($response)) {
	
   		$data[] = $parse;

   }
 

   if( $data[0]["is_available"]== 1 ){


    $_SESSION["loged"] = true;
	$_SESSION["username"] = $user_;
	$_SESSION["id_user"] =  $data[0]["id_user"];

	header("Location:   ../home");

   }else{


   header("Location:   ../error");

   }


 
}


//-----------------------------------------------------------------------------------------------------------------------------------------
//CHECAR SI EL USUARIO ESTA DISPONIBLE
public 	function check_user_available(){



 $key =  $_POST["key"];
$QUERY =  "SELECT id_user ,user_pic , username FROM user where username = '$key' ";


		$response = mysql_query( $QUERY , Conectar::con());


		while ( $parse = mysql_fetch_assoc($response)  ) {
		
			$DATA[]  = $parse;


		}



if( isset($DATA)   ){

echo json_encode(array('response' => $DATA)); // si hay datos regresamos las notificaciones!!!

$_SESSION["user_exist"] = true;
$_SESSION["user_id_added"] = $DATA[0]["id_user"];

} else{


$response = array('response' => false );

echo json_encode($response); // no regresamos nada!!!!

$_SESSION["user_exist"] = false;

}



}


}

//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//********************                                                                                          ****************************
//********************                            CLASE CHAT , SACAR MSG CHAT POR LIMITE                        ***************************
//********************                                                                                          **************************** 
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//********************                                                                                          ****************************
//********************                            CLASE CHAT , SACAR MSG CHAT POR LIMITE                        ***************************
//********************                                                                                          **************************** 
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************


class chat{

 
	public static function append_into_contact_list(){ // guardamos el contacto en nuestra lista


    $id_added_user = $_SESSION["user_id_added"]; // usuario que acabamos de agregar
	$current_user_id = $_SESSION["id_user"]; // este soy yo osea el que agrega

	$QUERY  = "INSERT INTO contact values(NULL,$current_user_id ,$id_added_user)  ";


	 mysql_query( $QUERY , Conectar::con());



	}


//***********************************************************************************************************************************

	public static function get_contact_list(){


   extract($_POST);

	$QUERY  = "SELECT DISTINCT id_current_user , user.id_user , user.username, user.user_pic
 			   FROM   contact  , user
 			   WHERE  id_current_user = $current_user_id
 			   and user.id_user = id_added_user";

 
		$response = mysql_query( $QUERY , Conectar::con());


		while ( $parse = mysql_fetch_assoc($response)  ) {
		
			$DATA[]  = $parse;


		}



if( isset($DATA)   ){

echo json_encode(array('response' => $DATA)); // si hay datos regresamos las notificaciones!!!


} else{


$response = array('response' => false );

echo json_encode($response); // no regresamos nada!!!!



}



	}



//***********************************************************************************************************************************
	public function append_into_db_msg(){

 

		extract($_POST);
 
		$UNIX_TIME = parseInt(strtotime(date("Y-m-d H:i:s")))+3600;


		$QUERY  = "INSERT INTO chat values(NULL, $id_user_writer ,$id_user_otherside ,'$msg' , '" . $UNIX_TIME. "' )  ";

		mysql_query( $QUERY , Conectar::con());



		$id_last_msg = mysql_insert_id();


	    chat::get_msg_from_db_by_limit("justOneMSG" , $id_user_writer); // obtenemos solo 1 msg

	    notify::set_notify($id_last_msg); // metemos la notifycacion |||| pasamos ( user_writer , user_reader , id_chat); 

	}

//***********************************************************************************************************************************.

	public static function get_msg_from_db_by_limit($type){ // obtenemos n mensajes del chat

		extract($_POST);
 


		$QUERY =  "";


		switch ($type) {
		
		case 'justOneMSG': // obtenemos solo  1 mensaje

		  $QUERY = "SELECT DISTINCT id_user_reader as id_user_reader, msg ,( FROM_UNIXTIME(chat.fecha ) ) as fecha , user.username , user.user_pic

 from chat , user

 where 

 ( ( id_user_reader = $id_user_otherside AND  id_user_writer = $id_user_writer) AND user.id_user =  id_user_writer)



 
ORDER BY FROM_UNIXTIME(chat.fecha ) DESC LIMIT 1"; // (Id_user_reader, $limit_msg_chat )
		

				break;



//***********************************************************************************************************************************



	    case 'all': // todos los mensajes
	    	
	    	
$QUERY  = "SELECT DISTINCT id_user_reader as id_user_reader, msg ,( FROM_UNIXTIME(chat.fecha ) ) as fecha , user.username , user.user_pic

 from chat , user

 where 

( (id_user_reader =$id_user_writer and   id_user_writer =$id_user_otherside) AND user.id_user =  id_user_writer ) ||

 ( ( id_user_reader = $id_user_otherside AND  id_user_writer = $id_user_writer) AND user.id_user =  id_user_writer)

 
ORDER BY FROM_UNIXTIME(chat.fecha ) ASC ";




	    	break;


			default:
				# code...
				break;
		}



//***********************************************************************************************************************************
 
 
		$response = mysql_query( $QUERY , Conectar::con());


		while ( $parse = mysql_fetch_assoc($response)  ) {
		
			$DATA[]  = $parse;


		}



if( isset($DATA)   ){

echo json_encode(array('response' => $DATA)); // si hay datos regresamos las notificaciones!!!


} else{


$response = array('response' => false );

echo json_encode($response); // no regresamos nada!!!!



}


	}


 

}


function parseInt($string) {
// return intval($string);
if(preg_match('/(\d+)/', $string, $array)) {
return $array[1];
} else {
return 0;
}
} 


//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//********************                                                                                          ****************************
//**********      CLASE NOTIFICACIONES , ELIMINAR , INSERTAR , OBTENER  Y OBTENER LOS DATOS DE LAS NOTIFICACIONES     ***************************
//********************                                                                                          **************************** 
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//********************                                                                                          ****************************
//*************   CLASE NOTIFICACIONES , ELIMINAR , INSERTAR , OBTENER  Y OBTENER LOS DATOS DE LAS NOTIFICACIONES     ***************************
//********************                                                                                          **************************** 
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************



class notify{




public static function delete_notify ($id_user_otherside , $id_user_reader){





		 $QUERY  = "UPDATE notify inner join chat on  

		 (notify.id_chat = chat.id_chat and chat.id_user_writer = $id_user_otherside AND chat.id_user_reader = $id_user_reader)
										
	
		  set notified = 1"; // insertamos la notificacion


		mysql_query( $QUERY , Conectar::con());





}

//*****************************************************************************************


public static function set_notify( $id_chat){


		 $QUERY  = "INSERT INTO notify values(NULL, '$id_chat', 0 )  "; // insertamos la notificacion | 0 es cuando aun no se lee la notificacion

		mysql_query( $QUERY , Conectar::con());

 
}

//*****************************************************************************************



public static function get_notify($id_user_reader){ // checamos las notificaciones



  //DONDE id_user_reader = al usuario actual !!!


$QUERY = "SELECT count(notify.id_chat)as notify , chat.id_user_writer as id_user_otherside

 from notify , chat  where chat.id_user_reader = $id_user_reader

AND chat.id_chat= notify.id_chat

 AND notify.notified = 0 group by id_user_writer";

 
$response = mysql_query( $QUERY , Conectar::con());



    while($parse = mysql_fetch_assoc($response)){


      $DATA[] = $parse;


    }

if( isset($DATA)   ){

echo json_encode(array('response' => $DATA)); // si hay datos regresamos las notificaciones!!!


} else{


$response = array('response' => false );

echo json_encode($response); // no regresamos nada!!!!



}
}



//*****************************************************************************************



public function get_notify_data(){ // OBTENEMOS LOS COMENTARIOS NUEVOS!!!!!


extract($_POST);

//$id_user_writer , $id_user_otherside , $limit_msg_chat

 


$QUERY = "SELECT user.username , user.user_pic , chat.msg , ( FROM_UNIXTIME(chat.fecha ) ) as fecha 
from chat , user , notify

WHERE (chat.id_chat = notify.id_chat and id_user_writer = $id_user_otherside and user.id_user = id_user_writer and chat.id_user_reader=$writtr_)

	and notify.notified =  0
";

 
$response = mysql_query( $QUERY , Conectar::con());


 
    while($parse = mysql_fetch_assoc($response)){


      $DATA[] = $parse;


    }

if( isset($DATA)   ){

echo json_encode(array('response' => $DATA)); // si hay datos regresamos las notificaciones!!!

notify::delete_notify($id_user_otherside,$writtr_);

} else{


$response = array('response' => false );

echo json_encode($response); // no regresamos nada!!!!



}



 
 
}


 



}
























 ?>