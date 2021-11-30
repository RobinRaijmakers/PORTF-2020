<?PHP
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require __DIR__ .  '/vendor/autoload.php';


class Init  
{
/*	private $db = 'in';
	private $host = 'localhost';
	private $username = 'root';
	private $pass = '';*/

	private $db = '**********';
	private $host = '*********';
	private $username = '*********';
	private $pass = '**************'; 


	private $supportemail = 'support@zwlsoftware.com';


	public $base62; 

	public $conn;

	public $date; 

	//COMPOSER

	function __construct()
	{
		$this->base62 = new Tuupola\Base62;
	}

	/*   CONNECTION  */ 
	function connectDB()
	{

		$this->conn = new mysqli($this->host, $this->username, $this->pass, $this->db);
		$this->SetDate();
	}


	/* VALIDATE */
	function validate_API($key)
	{
		$sql = "SELECT * FROM `**************` WHERE api = ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param('s', $key);

		$stmt->execute();
		$result = $stmt->get_result();

		if($result->num_rows != 0)
		{
			return true;
		}
		else{
			return false;
		}


	}


	/*   GET FUNCTIONS   */
	function Get_ProductVersion($product, $token)
	{
		$sql = "SELECT * FROM `************` WHERE product = ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param('s', $product);

		$stmt->execute();
		$result = $stmt->get_result();

		if($result->num_rows > 0)
		{

			$sql1 = "SELECT * FROM `**************` WHERE api = ?";
			$stmt1 = $this->conn->prepare($sql1);
			$stmt1->bind_param('s', $token);

			$stmt1->execute();
			$result1 = $stmt1->get_result();
		

		 
			while ($r1 = $result1->fetch_array()) {
			 	

				$add = $r1;
				 
		 		 
	 			if(!str_contains($add['using_product'], $product)){ //php 8 

			 		if(empty($add['using_product']))
			 		{
			 			$newproduct = $product;
			 		}
			 		else{
			 		 	$newproduct = $add['using_product'] . " , " . $product;
			 		}
			 


		 		 
				 
					 
					$sql2 = "UPDATE `**************` SET using_product = ?";
					$stmt2 = $this->conn->prepare($sql2);
					$stmt2->bind_param('s', $newproduct);

					$stmt2->execute();
				
					$result2 = $stmt2->get_result();
							}

			

	 			while ($r = $result->fetch_assoc()) {
	 					return json_encode(array('version' => $r['version']));
	 			}

			}
		 	

	 

			

		}
		else{
			return false;
		}

	}
	function Get_Userdata($key)
	{
		$sql = "SELECT * FROM `**************` WHERE api = ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param('s', $key);

		$stmt->execute();
		$result = $stmt->get_result();


		while ($res = $result->fetch_assoc()) 
		{
			$userdata[] = $res;
		}

		return $userdata; 
	}


	function Get_AllProducts()
	{
		$sql = "SELECT * FROM `************`";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute();
		$result = $stmt->get_result();




		while ($res = $result->fetch_assoc()) 
		{
			$products[] = $res;

			
		}
		return $products;

	}

	function SetDate(){
		date_default_timezone_set('Europe/amsterdam');
		$this->date = date('Y-m-d G:i:s'); 

	}
	/*MISC */

	function DoSelectQuery($sql) // can be removed ... 
	{
		$result = mysqli_query($this->conn, $sql);

		if($result->num_rows == 0){

			echo json_encode(
				array(
					'code' => 3,
					'message' => 'API Key is invalid'
				)

			);
		}

	}
	function DoUpdateQuery($sql) // can be removed ... 
	{
		$result = mysqli_query($this->conn, $sql);

	}
	/* USER */
	function user_login($email, $password)
	{


		if($this->conn){


			$sql = "SELECT * FROM `**************` WHERE email = ?";
			$stmt = $this->conn->prepare($sql);
			$stmt->bind_param('s', $email);

			$stmt->execute();
			$result = $stmt->get_result();


			if($result->num_rows <= 0)
			{
	  				//no user with that email exist (code 1)
				return json_encode(array('Access' => 0, 'Code' => 1));
			}
			else{

				while ($r = $result->fetch_assoc()) {


					if(isset($r["password"]))
					{     
						$crypted_pass = $r["password"];

				  				if(crypt($password, $crypted_pass) == $crypted_pass) // check if filled in password matches encrypte stored hash 
				  				{
                                    // ingelogt

				  					$_SESSION['email'] = $email; 




				  					if(empty($r['api'])){

				  						$loginkey = $this->base62->encode(random_bytes(32));  
				  						$sql = "UPDATE `**************` SET api = ? WHERE email = ?";
				  						$stmt = $this->conn->prepare($sql);
				  						$stmt->bind_param('ss', $loginkey, $r['email']);
				  						$stmt->execute();

				  						return json_encode(array('Access' => 1, 'Code' => 0, 'token' => $r['api']));

				  					}
				  					else{
									 //correct login  (code 0)
				  						return json_encode(array('Access' => 1, 'Code' => 0, 'token' => $r['api']));
				  					}


				  				}
				  				else
				  				{

                                      //wrong password (code 2)
				  					
				  					return json_encode(array('Access' => 0, 'Code' => 2));
				  				}

				  			} 
				  		}
				  	}


				  }
				  else
				  {
 			die('could not connect to database, please check your internet connection or try again later..'); // 
 		}


 	} 
 	function user_register($email, $password)
 	{	

 		$stored_hash = password_hash($password, PASSWORD_DEFAULT);
 		$crypted_pass = crypt($password, $stored_hash);



 	}

 	function user_GenerateAPIkey()
 	{
		//check if the generated key exist, if so replace it


 	}

	function user_forgotpw($email) // <<< make dynamic  mail function 
	{	
		$to      = '{$email}';
		$subject = 'Resetting Your Password';
		$message = 'Hi, You recently requested to reset your Automate account password. Click the button below to reset your password. If you didnt make this request, ignore this email.';
		$headers  = "From: ZWL Software <{$this->supportemail}>\n";
		$headers .= "Cc:\n"; 
		$headers .= "X-Sender: ZWL Software <{$this->supportemail}>\n";
		$headers .= 'X-Mailer: PHP/' . phpversion();
	    $headers .= "X-Priority: 1\n"; // Urgent message!
	    $headers .= "Return-Path: {}\n"; // Return path for errors
	    $headers .= "MIME-Version: 1.0\r\n";
	    $headers .= "Content-Type: text/html; charset=iso-8859-1\n";

	    if(mail($to, $subject, $message, $headers))
	    {
	    	return true;
	    }
	    else{
	    	return false;

	    }

	}

	/*	API  */
	function ReadAPIkey($key, $product)
	{	

		/*$check api key here*/
		//check if api key is legit first ^^^
		if(!$this->validate_API($key))
		{
 
			return json_encode(array(
					'code' => 3 , 'message' => 'API Key is invalid'
				));
			exit();
		}
	 

		$products = $this->Get_AllProducts();



		for ($i=0; $i < count($products); $i++) { 

			if($products[$i]['product'] == $product)
			{
				return json_encode($userdata = $this->Get_Userdata($key, $product));


			}
		

		} 

	}
	function requestAPIlogin($email, $rawpassword)
	{
		$sql = "SELECT * FROM `**************` WHERE email = ?";  

		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param('s', $email);

		$stmt->execute();
		$result = $stmt->get_result();

		while ($r = $result->fetch_assoc()) {

			if($result->num_rows == 0){
				return json_encode(array('Access' => 0, 'code' => 1 ));	 	
			}
			else{

				$jsondata = $this->user_login($email, $rawpassword); 
				$data = json_decode(json_encode($jsondata), true);
			 	return $data;

				/*if($data['Code'] == 0)
				{
					return json_encode($data);
				}
				else{
					return $data; 
				}*/
			} 

		}
	}

}







?>
