<?php
class DB_Functions {
    private $db;
    //put your code here
    // constructor
    function __construct() {
        require_once 'DB_Connect.php';
        // connecting to database
        $this->db = new DB_Connect();
        $this->db->connect();
    }
    // destructor
    function __destruct() {
    }
    /**
     * Random string which is sent by mail to reset password
     */
public function random_string()
{
    $character_set_array = array();
    $character_set_array[] = array('count' => 7, 'characters' => 'abcdefghijklmnopqrstuvwxyz');
    $character_set_array[] = array('count' => 1, 'characters' => '0123456789');
    $temp_array = array();
    foreach ($character_set_array as $character_set) {
        for ($i = 0; $i < $character_set['count']; $i++) {
            $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) - 1)];
        }
    }
    shuffle($temp_array);
    return implode('', $temp_array);
}
public function forgotPassword($forgotpassword, $newpassword, $salt){
  $result = mysql_query("UPDATE `users` SET `encrypted_password` = '$newpassword',`salt` = '$salt'
              WHERE `email` = '$forgotpassword'");
if ($result) {
return true;
}
else
{
return false;
}
}
/**
     * Adding new user to mysql database
     * returns user details
     */
    public function storeUser($fname, $lname, $email, $uname, $password) {
        $uuid = uniqid('', true);
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt
        $result = mysql_query("INSERT INTO users(unique_id, firstname, lastname, email, username, encrypted_password, salt, created_at) VALUES('$uuid', '$fname', '$lname', '$email', '$uname', '$encrypted_password', '$salt', NOW())");
        // check for successful store
        if ($result) {
            // get user details
            $uid = mysql_insert_id(); // last inserted id
            $result = mysql_query("SELECT * FROM users WHERE uid = $uid");
            // return user details
            return mysql_fetch_array($result);
        } else {
            return false;
        }
    }
    /**
     * Verifies user by email and password
     */
    public function Login($usuario, $password,$imei) {
      
        $result = mysql_query("SELECT * FROM cuenta WHERE usuario = '$usuario'");
        if(!$result)
        {
		return constant("DB_ERROR");
			
		}
        
        // check for result
        $no_of_rows = mysql_num_rows($result);
        
        if ($no_of_rows > 0) {
            $result = mysql_fetch_array($result);
            $encrypted_password = $result['password'];
			$storedImei=$result['imei'];
			
			
            if ($encrypted_password == $password && $storedImei==$imei) {
				
                // user authentication details are correct
                return $result;
            }
            
            else if($encrypted_password != $password)
            {
				//invalid password
				return constant("INV_PSW");
				
			}
			
			else if($storedImei != $imei)
            {
				//invalid password
				return constant("INV_IMEI");
				
			}
            
            else
            {
				return constant("INV_PSW");
			}
			
			
        } else {
            // user not found
            return constant("INV_USER");
        }
    }

public function  SaldoOperador($usuario, $imei){
    
      $result = mysql_query("SELECT saldo, fechahora_server, fechahora_trans FROM cuenta WHERE imei = '$imei'");
     if(!$result)
        {
		return constant("DB_ERROR");
			
		}
    $no_of_rows = mysql_num_rows($result);
    
    if($no_of_rows > 0){
      $result = mysql_fetch_array($result);
      return $result;
          }
          else{
            return constant("INV_USER");
          }
    }
    
    public function RecargaTelefono($usuario, $imei,$monto,$fechahora,$telefono,$producto,$modo_pago,$medio_pago){
    
      $result = mysql_query("SELECT saldo, fechahora_server, fechahora_trans FROM cuenta WHERE imei = '$imei'");
     if(!$result)
        {
		return constant("DB_ERROR");
			
		}
    $no_of_rows = mysql_num_rows($result);
    
    if($no_of_rows > 0){
        $result = mysql_fetch_array($result);
        $saldo =$result['saldo'];   
        if($saldo<$monto)
        {
           return constant("INSUFFICIENT");
            
        }
        else
        {
        $sql = 'INSERT INTO venta '.
       '(usuario, imei, monto,telefono_recarga,fecha_hora,producto,modo_pago,medio_pago) '.
       "VALUES ( '$usuario','$imei','$monto','$telefono','$fechahora','$producto','$modo_pago','$medio_pago' )";            
        $venta=mysql_query($sql);
      //  $venta = mysql_fetch_array($venta);

        if($venta)
        {
        $saldo=$saldo-$monto;
        $sql="UPDATE cuenta SET saldo='$saldo' WHERE usuario='$usuario'";
        $venta=  mysql_query($sql);
        $sql="UPDATE cuenta SET fechahora_trans='$fechahora' WHERE usuario='$usuario'";
        $venta=  mysql_query($sql);

        return constant('SUCCESS');
        
        }
        else
        return constant('DB_ERROR');
        }  
      return $result;
          }
          else{
            return constant("INV_USER");
          }
    }
    
    
    public function NotificarDeposito($cuenta_id, $imei,$monto,$fechahora,$tipo_deposito,$cuenta_origen){
    
      
        $sql = 'INSERT INTO notificacion '.
       '(cuenta_id, imei, monto,fechahora,tipo_deposito,cuenta_origen) '.
       "VALUES ( '$cuenta_id','$imei','$monto','$fechahora','$tipo_deposito','$cuenta_origen' )";            
        $notificacion=mysql_query($sql);
      //  $venta = mysql_fetch_array($venta);
       if($notificacion)
        {
        return constant('SUCCESS');
        }
        else
        return constant('DB_ERROR');
        }  
     
  /**
     * Checks whether the email is valid or fake
     */
public function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\.\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\-\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\.\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      
      if ($isValid && !(checkdnsrr($domain,"MX") ||checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}
 /**
     * Check user is existed or not
     */
    public function isUserExisted($email) {
        $result = mysql_query("SELECT email from users WHERE email = '$email'");
        $no_of_rows = mysql_num_rows($result);
        if ($no_of_rows > 0) {
            // user existed
            return true;
        } else {
            // user not existed
            return false;
        }
    }
    /**
     * Encrypting password
     * returns salt and encrypted password
     */
    public function hashSSHA($password) {
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
    /**
     * Decrypting password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) {
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
        return $hash;
    }
}
?>
