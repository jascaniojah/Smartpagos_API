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
    $response = array("tag" => $tag, "success" => 0, "error" => 0);
    // check for tag type
    if ($tag == 'login') {
        // Request type is check Login
        $user = $_POST['usuario'];
        $password = $_POST['password'];
        // check for cuenta
        $cuenta = $db->validarUsuario($user, $password);
        if ($cuenta != false) {
            // cuenta found
            // echo json with success = 1
            $response["success"] = 1;
            $response["cuenta"]["telefono"] = $cuenta["telefono"];
            $response["cuenta"]["imei"] = $cuenta["imei"];
            $response["cuenta"]["fecha_server"] = $cuenta["fechahora_server"];
            $response["cuenta"]["saldo"] = $cuenta["saldo"];
            $response["cuenta"]["uid"] = $cuenta["UID"];
            $response["cuenta"]["fecha_trans"] = $cuenta["fechahora_trans"];
            echo json_encode($response);
        } else {
            // cuenta not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["error_msg"] = "Incorrect email or password!";
            echo json_encode($response);
        }
    }
  else if ($tag == 'chgpass'){
  $email = $_POST['email'];
  $newpassword = $_POST['newpas'];
  $hash = $db->hashSSHA($newpassword);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"];
  $subject = "Change Password Notification";
         $message = "Hello cuenta,nnYour Password is sucessfully changed.nnRegards,nLearn2Crack Team.";
          $from = "contact@learn2crack.com";
          $headers = "From:" . $from;
  if ($db->iscuentaExisted($email)) {
 $cuenta = $db->forgotPassword($email, $encrypted_password, $salt);
if ($cuenta) {
         $response["success"] = 1;
          mail($email,$subject,$message,$headers);
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
} else {
    echo "Learn2Crack Login API";
}
?>