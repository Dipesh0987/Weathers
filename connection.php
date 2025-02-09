<?php 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$serverName = "mysql.railway.internal";
$userName = "root";
$password = "pdGaAeEtDanpjUpsvBrpJgKyKWUIamrI";
$db = "railway";
$port = "3306";
$conn = mysqli_connect($serverName, $userName, $password, $db, $port);
if($conn){
    // echo "Connection Successful <br>";
}
else{
    echo "Failed to connect <br>".mysqli_connect_error();
}


#creating database



#selecting the database we created
mysqli_select_db($conn, "railway");

#create table in the database
$createTable = "CREATE TABLE IF NOT EXISTS WEATHER(
City_Name varchar(255),
Temperature varchar(255),
Humidity varchar(255),
Wind_Speed varchar(255),
Wind_Direction varchar(255),
Pressure varchar(255),
Icon_Code varchar(100),
Last_Updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $createTable)){
    // echo "Table created <br>";
}else{
    echo "Failed to create table <br>".mysqli_connect_error();
}


#getting weather from the client side
if(isset($_GET['t'])){
    $cityname = $_GET['t'];
    // echo $cityname;
}else{
    $cityname = "Guntersville";
}


function checkWeather($conn, $city){
    $city = urlencode($city);
    $apikey = "a1387411d9751f10a2be3e09afc3fcb4";
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apikey&units=metric";

    #now fetch data from the API
    $mydata = file_get_contents($url);
    if ($mydata === false) {
        error_log("Failed to fetch data from OpenWeather API: " . error_get_last()['message']);
    }
    $data = json_decode($mydata, True);
    // $Date_ = date("n/j/y");
    $City_Name = $data['name'];
    $Temperature = $data['main']['temp'];
    $Humidity = $data['main']['humidity'];
    $Wind_Speed = $data['wind']['speed'];
    $Wind_Direction = $data['wind']['deg'];
    $Pressure = $data['main']['pressure'];
    $Icon_Code = $data['weather'][0]['icon'];

    $sql_check = "SELECT * FROM WEATHER WHERE City_Name = '$City_Name'";
    $check_result = mysqli_query($conn, $sql_check);
    if(mysqli_num_rows($check_result) > 0){
        $sql_update = "UPDATE WEATHER SET
            Temperature = '$Temperature',
            Humidity = '$Humidity',
            Wind_Speed = '$Wind_Speed',
            Wind_Direction = '$Wind_Direction',
            Pressure = '$Pressure',
            Icon_Code = '$Icon_Code'
            WHERE City_Name = '$City_Name'";
        mysqli_query($conn, $sql_update);
    } else {
        // Insert new record
        $sql_insert = "INSERT INTO WEATHER(City_Name,Temperature,Humidity,Wind_Speed,Wind_Direction,Pressure,Icon_Code) VALUES ('$City_Name', '$Temperature', '$Humidity', '$Wind_Speed', '$Wind_Direction', '$Pressure', '$Icon_Code')";
        mysqli_query($conn, $sql_insert);
    }
    return [
        'City_Name' => $City_Name,
        'Temperature' => $Temperature,
        'Humidity' => $Humidity,
        'Wind_speed' => $Wind_Speed,
        'Wind_Direction' => $Wind_Direction,
        'Pressure' => $Pressure,
        'Icon_Code' => $Icon_Code
    ];
}


$finaldata = [];
#selecting data from the database
$sql_select1 = "SELECT * FROM WEATHER WHERE City_Name = '$cityname';";
$sql_delete = "DELETE FROM WEATHER WHERE City_Name = '$cityname';";
$result1 = mysqli_query($conn, $sql_select1);
if($result1){
    // echo "Data fetched successfully <br>";
}else{
    echo "Data not fetched <br>".mysqli_connect_error();
}
#check if the data already exists or not
if(mysqli_num_rows($result1)==0){
    $finaldata[] = checkWeather($conn, $cityname);
}else{
    while($row = mysqli_fetch_assoc($result1)){
        if(strtolower($row["City_Name"])==strtolower($cityname)){
            $lastFetchTime = new DateTime($row['Last_Updated']);
            $currentTime = new DateTime();
            $timeDiff = $currentTime->getTimestamp() - $lastFetchTime->getTimestamp();

            if ($timeDiff > 2 * 60 * 60) { // 2 hours in seconds
                $result8 = mysqli_query($conn,$sql_delete);
                # Checking for errors in SQL
                if($result8){
                }else{
                    echo 'Data not able to delete!'.mysqli_connect_error();
                // Fetch new data and update fetch_time
                $finaldata = checkWeather($conn, $cityname);
                }
            }
        }
    }
    if($finaldata == []){
        $finaldata = [checkWeather($conn, $cityname)];
    }
}
$json_data  = json_encode($finaldata);

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// header("Cache-Control: no-cache, no-store, must-revalidate");
// header("Expires: 0");
// header("Pragma: no-cache");
echo $json_data



?>
