<?php

$url = 'http://103.107.17.141:8080/RestWebService/user'; // path to your JSON file
$data = file_get_contents($url); // put the contents of the file into a variable
$characters = json_decode($data); // decode the JSON feed

// echo "<b>Sr. No.</b>".'<i>'.$characters[0]->srNo.'</i>'."<b> Name: </b>".$characters[0]->firstName."<b> </b>".$characters[0]->lastName."<b> Role: </b>".$characters[0]->role."<b> Email: </b>".$characters[0]->email." <br>";  

foreach ($characters as $character) {
	// echo $character->email . '<br>';
	echo "<b>Sr. No.</b>".'<i>'.$character->srNo.'</i>'."<b> Name: </b>".$character->firstName."<b> </b>".$character->lastName."<b> Role: </b>".$character->role."<b> Email: </b>".$character->email." <br>";
}


class Emp {
      public $name = "";
      public $hobbies  = "";
      public $birthdate = "";
      public $start_date = "8/5/1974 12:20:03";
   }
	
   $e = new Emp();
   $e->name = "sachin";
   $e->hobbies  = "sports";

   $e->birthdate = date('m/d/Y h:i:s a', "8/5/1974 12:20:03 p");
   $e->birthdate = date('m/d/Y h:i:s a', strtotime($_GET['start_date']));   //$e->birthdate = date('m/d/Y h:i:s a', strtotime("8/5/1974 12:20:03"));


   echo json_encode($e);


?>
<!-- 	<script>

		$url = 'http://103.107.17.141:8080/RestWebService/user'; // path to your JSON file
		$data = file_get_contents($url); // put the contents of the file into a variable
		$characters = json_decode($data); // decode the JSON feed
		console.log($characters);

	</script> -->

<p id="user"></p>

<script>
var s = '{"first_name" : "Sammy", "last_name" : "Shark", "location" : "Ocean"}';

var obj = JSON.parse(s);

document.getElementById("user").innerHTML =
"Name: " + obj.first_name + " " + obj.last_name + "<br>" +
"Location: " + obj.location;
</script>
