<?php
/**
 PHP API for Login, Register, Changepassword, Resetpassword Requests and for Email Notifications.
 **/
if (isset($_POST['tag']) && $_POST['tag'] != '') {
    
    // Get tag
    $tag = $_POST['tag'];
    // Include Database handler
    require_once 'include/DB_Functions.php';
    $db = new DB_Functions();
    // response Array
    $response = array("tag" => $tag);
    // check for tag type
    if ($tag == 'login') {
        // Request type is check Login
        $user = $_POST['usuario'];
        $password = $_POST['password'];
        $imei=$_POST['imei'];
        $numero=$_POST['numero'];
        
        // check for cuenta
        $cuenta = $db->Login($user,$password,$imei);
        
        if ($cuenta != constant("DB_ERROR")&& $cuenta != constant("INV_IMEI")&& 
        $cuenta != constant("INV_PSW")&& $cuenta != constant("INV_USER")){
            $response["error"] = 0;
            $response["code"]=constant("SUCCESS");
            $response["cuenta"]["usuario"] = $user;
            $response["code_desc"]="Inicio de Sesion Exitoso";
          
            echo json_encode($response);
        } else if($cuenta == constant("DB_ERROR")){
            // cuenta not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["code"]=$cuenta;
            $response["error_msg"] = "Error en la Base de Datos";
            echo json_encode($response);    
        }
        
        else if($cuenta == constant("INV_IMEI")){
            // cuenta not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["code"]=$cuenta;
            $response["error_msg"] = "Imei Invalido";
            echo json_encode($response);    
        }
        
        else if($cuenta == constant("INV_PSW")){
            // cuenta not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["code"]=$cuenta;
            $response["error_msg"] = "Password Invalido";
            echo json_encode($response);    
        }
        
        else if($cuenta == constant("INV_USER")){
            // cuenta not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["code"]=$cuenta;
            $response["error_msg"] = "Usuario Invalido";
            echo json_encode($response);    
        }
        
    }
  else if ($tag == 'consulta'){
  $imei = $_POST['imei'];
  $user = $_POST['usuario'];
  $cuenta = $db->SaldoOperador($user, $imei);
        if ($cuenta != constant("INV_USER") && $cuenta!=constant("DB_ERROR")) {
            // cuenta found
            // echo json with success = 1
            $response["success"] = 1;
            $response["cuenta"]["fecha_server"] = $cuenta["fechahora_server"];
            $response["cuenta"]["fecha_trans"] = $cuenta["fechahora_trans"];
            $response["cuenta"]["saldo"] = $cuenta["saldo"];
            echo json_encode($response);
        } else if($cuenta==constant("INV_USER")){
            // cuenta not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["code"]=$cuenta;
            $response["error_msg"] = "Usuario Invalido";
            echo json_encode($response);
        }
        else if($cuenta==constant("DB_ERROR")){
            // cuenta not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["code"]=$cuenta;
            $response["error_msg"] = "Error en la Base de Datoss";
            echo json_encode($response);
        }
          
}
elseif ($tag=="recarga") {

    $usuario=$_POST['usuario'];
    $imei=$_POST['imei'];
    $monto=$_POST['monto'];
    $fechahora=$_POST['fechahora'];
    $telefono=$_POST['telefono'];
    $producto=$_POST['producto'];
    $modo_pago=$_POST['modo_pago'];
    $medio_pago=$_POST['medio_pago'];
    $venta=$db->RecargaTelefono($usuario, $imei,$monto,$fechahora,$telefono,$producto,$modo_pago,$medio_pago);
    if ($venta ==constant("INSUFFICIENT"))
    {
            $response["error"] = 1;
            $response["code"]=$venta;
            $response["error_msg"] = "Saldo Insuficiente";
            echo json_encode($response);

    }
    else if($venta==constant("DB_ERROR"))
    {
        $response["error"] = 2;
            $response["code"]=$venta;
            $response["error_msg"] = "Error en la Base de Datos";
            echo json_encode($response);
        
    }
    
    else if($venta== constant("SUCCESS"))
    {   
            $response["error"] = 0;
            $response["code"]=$venta;
            $response["error_msg"] = "Transaccion Exitosa";
            echo json_encode($response);   
    }
    
}

elseif ($tag=="notificacion") {

    $cuenta_id=$_POST['cuenta_id'];
    $imei=$_POST['imei'];
    $monto=$_POST['monto'];
    $fechahora=$_POST['fechahora'];
    $referencia=$_POST['referencia'];
    $tipo_deposito=$_POST['tipo_deposito'];
    $cuenta_origen=$_POST['cuenta_origen'];
    $notificacion=$db->NotificarDeposito($cuenta_id, $imei,$monto,$fechahora,$tipo_deposito,$cuenta_origen,$referencia);
   
    if($notificacion==constant("DB_ERROR"))
    {
        $response["error"] = 1;
            $response["code"]=$notificacion;
            $response["error_msg"] = "Error en la Base de Datos";
            echo json_encode($response);
        
    }
    
    else if($notificacion== constant("SUCCESS"))
    {   
            $response["error"] = 0;
            $response["code"]=$notificacion;
            $response["error_msg"] = "Transaccion Exitosa";
            echo json_encode($response);   
    }
    
}

elseif ($tag =="cuentas"){
    $telefono=$_POST['telefono'];
    $servicio=$_POST['servicio'];
    $origen=$_POST['origen'];
    $imei=$_POST['imei'];
    $fechahora=$_POST['fechahora'];
    $codigo_banco=$_POST['codigo_banco'];
    $cuentas_bancarias = $db ->ConsultaCuentas($telefono,$servicio,$origen,$imei,$fechahora,$codigo_banco);
    $response["cuentas"]=$cuentas_bancarias;
    echo json_encode($response); 
}

elseif ($tag =="bancos"){
    $telefono=$_POST['telefono'];
    $servicio=$_POST['servicio'];
    $origen=$_POST['origen'];
    $imei=$_POST['imei'];
    $fechahora=$_POST['fechahora'];
    $cuentas_bancarias = $db ->ConsultaBancos($telefono,$servicio,$origen,$imei,$fechahora);
    $response["bancos"]=$cuentas_bancarias;
    echo json_encode($response); 
}

elseif ($tag =="productos"){
    $telefono=$_POST['telefono'];
    $servicio=$_POST['servicio'];
    $origen=$_POST['origen'];
    $imei=$_POST['imei'];
    $fechahora=$_POST['fechahora'];
    $productos = $db ->ConsultaProductos($telefono,$servicio,$origen,$imei,$fechahora);
    $response["productos"]=$productos;
    echo json_encode($response); 
}



elseif($tag=="transacciones")
{
    $telefono=$_POST['telefono'];
    $servicio=$_POST['servicio'];
    $origen=$_POST['origen'];
    $imei=$_POST['imei'];
    $fechahora=$_POST['fechahora'];
    $fechainicio=$_POST['fechainicio'];
    $fechafin=$_POST['fechafin'];

    
    $transacciones=$db->ConsultaTransacciones($telefono,$servicio,$origen,$imei,$fechahora,$fechainicio,$fechafin);
    $response["transacciones"]=$transacciones;
    echo json_encode($response);
    
    
    
}

















else if ($tag == 'forpass'){
$forgotpassword = $_POST['forgotpassword'];
$randomcode = $db->random_string();
$hash = $db->hashSSHA($randomcode);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"];
  $subject = "Password Recovery";
         $message = "Hello cuenta,nnYour Password is sucessfully changed. Your new Password is $randomcode . Login with your new Password and change it in the cuenta Panel.nnRegards,nLearn2Crack Team.";
          $from = "contact@learn2crack.com";
          $headers = "From:" . $from;
  if ($db->iscuentaExisted($forgotpassword)) {
 $cuenta = $db->forgotPassword($forgotpassword, $encrypted_password, $salt);
if ($cuenta) {
         $response["success"] = 1;
          mail($forgotpassword,$subject,$message,$headers);
         echo json_encode($response);
}
else {
$response["error"] = 1;
echo json_encode($response);
}
            // cuenta is already existed - error response
        }
           else {
            $response["error"] = 2;
            $response["error_msg"] = "cuenta not exist";
             echo json_encode($response);
}
}
else if ($tag == 'register') {
        // Request type is Register new cuenta
        $fname = $_POST['fname'];
    $lname = $_POST['lname'];
        $email = $_POST['email'];
    $uname = $_POST['uname'];
        $password = $_POST['password'];
          $subject = "Registration";
         $message = "Hello $fname,nnYou have sucessfully registered to our service.nnRegards,nAdmin.";
          $from = "contact@learn2crack.com";
          $headers = "From:" . $from;
        // check if cuenta is already existed
        if ($db->iscuentaExisted($email)) {
            // cuenta is already existed - error response
            $response["error"] = 2;
            $response["error_msg"] = "cuenta already existed";
            echo json_encode($response);
        }
           else if(!$db->validEmail($email)){
            $response["error"] = 3;
            $response["error_msg"] = "Invalid Email Id";
            echo json_encode($response);
}
else {
            // store cuenta
            $cuenta = $db->storecuenta($fname, $lname, $email, $uname, $password);
            if ($cuenta) {
                // cuenta stored successfully
            $response["success"] = 1;
            $response["cuenta"]["fname"] = $cuenta["firstname"];
            $response["cuenta"]["lname"] = $cuenta["lastname"];
            $response["cuenta"]["email"] = $cuenta["email"];
            $response["cuenta"]["uname"] = $cuenta["cuentaname"];
            $response["cuenta"]["uid"] = $cuenta["unique_id"];
            $response["cuenta"]["created_at"] = $cuenta["created_at"];
               mail($email,$subject,$message,$headers);
                echo json_encode($response);
            } else {
                // cuenta failed to store
                $response["error"] = 1;
                $response["error_msg"] = "JSON Error occured in Registartion";
                echo json_encode($response);
            }
        }
    } else {
         $response["error"] = 3;
         $response["error_msg"] = "JSON ERROR";
        echo json_encode($response);
    }
} 


else {
    echo "by HispanoSoluciones";
}
?>
