
<!DOCTYPE HTML>
<html lang="cs">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lednice</title>
    
    <style>
            body {
                background-color:rgb(72, 95, 103);
            }
            th,td,tr {
                margin: 10px;
                border: 3px solid;
                border-color: rgba(0, 0, 0, 0.25);
                border-radius: 5px;
            }
            caption{
                background-color: rgb(115, 173, 80);
                border-radius: 5px;
            }
            tr:nth-child(even) {
                background-color: rgb(101, 151, 70);
            }
            tr:nth-child(odd) {
                background-color: rgb(115, 173, 80);
            }
            tr:hover{
                background-color: rgb(87, 131, 60);
            }
            .sub:hover{
                border: -1px solid;
                border-color: red;
            }
            .add:hover{
                border: -1px solid;
                border-color: lightgreen;
            }
        </style>
    </head>  
<body>
        <h1 style="text-align: center; background-color: rgb(72, 95, 103);">Lednice</h1>
        <table style="margin-left: auto;margin-right: auto; text-align: start">
            <caption >Obsah</caption>
            <tr style="pointer-events: none">
                <th>&nbsp Název potraviny &nbsp</th>
                <th>&nbsp Datum výroby &nbsp</th>
                <th>&nbsp Datum nákupu &nbsp</th>
                <th>&nbsp Datum minimální trvanlivosti &nbsp</th>
                <th>Datum spotřeby</th>
                <th>Poločas spotřeby</th>
                <th>fotografie produktu</th>
            </tr>

            <?php
                $conn=PrepareDB();
                if (($_SERVER['REQUEST_METHOD'] == 'POST') & (isset($_POST["itemName"]))){
                    AddToDB($conn);
                }
                elseif (($_SERVER['REQUEST_METHOD'] == 'POST') & (isset($_POST["id"]))){
                    SubFromDB($conn);
                }
                LoadFromDB($conn);
                $conn->close();
            ?>

            </tr>
                <form method="POST" action="index.php" enctype="multipart/form-data">
                    <td><input name="itemName" type="text" required></td>
                    <td><input name="manDate" type="date"></td>
                    <td><input name="buyDate" type="date" required></td>
                    <td><input name="expDate" type="date" required></td>
                    <td><input name="besDate" type="date"></td>
                    <td></td>
                    <td><input name="itemFoto" type="file" accept=".png , .jpg">
                        <input type="submit" value="+" class="add" style="float: right; width: 23px; border-radius: 5px;">
                    </td>
                </form>
            </tr>
        </table>
        
    <?php
        function PrepareDB(){
            $servername = "localhost";
            $username = "root";
    
            // Create connection
            $conn = new mysqli($servername, $username);
            // Check connection
            if ($conn->connect_error) {
              die("Connection failed: " . $conn->connect_error);
            }
            
            // Create database
            $sql = "CREATE DATABASE IF NOT EXISTS myDB";
            if ($conn->query($sql) === TRUE) {
              //echo "Database created successfully";
            } else {
              echo "Error creating database: " . $conn->error;
            }
            //select database
            $sql = "USE myDB";
            if ($conn->query($sql) === TRUE) {
              //echo "Database selected successfully";
            } else {
              echo "Error selecting database: " . $conn->error;
            }
            // sql to create table
            $sql = "CREATE TABLE IF NOT EXISTS Lednice (
                id INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                itemName VARCHAR(100) NOT NULL,
                manDate DATE,
                buyDate DATE NOT NULL,
                expDate DATE NOT NULL,
                besDate DATE,
                itemFoto BLOB
                )";            
            if (mysqli_query($conn, $sql)) {
                //echo "Table Lednice created successfully";
            } else {
                echo "Error creating table: " . mysqli_error($conn);
            }
            return $conn;
        }
        function LoadFromDB($conn){
            $sql = "SELECT id,itemName,manDate,buyDate,expDate,besDate,itemFoto FROM Lednice";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // output data of each row
                $resultArr = $result->fetch_all(MYSQLI_ASSOC);
                usort($resultArr,"mySort");
                foreach($resultArr as $row ) {
                    echo '<tr id="' . $row["id"] . '">';
                        echo "<td name='id' style='display:none'>" , $row["id"] , "</td>";
                        echo "<td>" , $row["itemName"],"</td>";
                        echo "<td>" , $row["manDate"], "</td>";
                        echo "<td>" , $row["buyDate"], "</td>";
                        echo "<td>" , $row["expDate"], "</td>";
                        echo "<td>" , $row["besDate"], "</td>";
                        //datum mezi (DV ? DV : DN) a (DS ? DS :DMT)
                        echo "<td>";
                            $tmpDate_1= strtotime(  $row["manDate"]=="0000-00-00" ? $row["buyDate"] : $row["manDate"] );
                            $tmpDate_2= strtotime(  $row["besDate"]=="0000-00-00" ? $row["expDate"] : $row["besDate"] );
                            $midTime=date( 'Y-m-d' , ($tmpDate_1+$tmpDate_2)/2 );
                            echo $midTime;
                            echo"</td>";
                        echo "<td>" , '<form method="POST" action="index.php" style="float: right;">
                            <button type="submit" name="id" value="'. $row["id"] .'" class="sub" style=
                            "float: right; width: 23px; border-radius: 5px;">-</button></form> 
                            <img src="data:image/jpeg;base64,'. base64_encode( $row['itemFoto'] ) .'"/>
                            </td>';
                            //● oranžové zvýraznění řádku = PS < DATE(NOW())
                            //● červené zvýraznění řádku = (DS ? DS : DMT) < DATE(NOW())
                            echo '
                            <script>
                            
                            if (Date.parse("'. $midTime .'") < Date.parse(new Date())){
                                var myRgb= [216, 120, 2];
                                document.getElementById("'. $row["id"] .'").style.background = "rgb(" + myRgb.toString() +")";
                            }
                            if("'. $row["besDate"] .'"=="0000-00-00"){
                                if(Date.parse("'. $row["expDate"] .'") < Date.parse(new Date())){
                                    var myRgb= [255, 30, 30];
                                document.getElementById("'. $row["id"] .'").style.background = "rgb(" + myRgb.toString() +")";
                                }
                            }
                            </script>
                            ';                    
                            
                    echo "</tr>";
                }
            } else {
                //echo "0 results";
            }
        }
        function AddToDB($conn){
            echo "trying to add to DB";
            $sql = 'INSERT INTO Lednice (itemName, manDate, buyDate, expDate, besDate, itemFoto)
            VALUES ("' . $_POST["itemName"] . '","' . $_POST["manDate"] . '","' . $_POST["buyDate"] . '","' .
                $_POST["expDate"] . '","' . $_POST["besDate"] . '","' . $_POST["itemFoto"] . '")';
            
            echo $sql;
            if (mysqli_query($conn, $sql)) {
                echo "Item Added successfully";
                header('Location: index.php');
            } else {
                echo "Error Adding item: " . mysqli_error($conn);
            }
           
        }
        function SubFromDB($conn){
            echo"<br> sub";
            $sql="DELETE FROM Lednice WHERE id='" . $_POST["id"] . "';";
            echo $sql;
            if (mysqli_query($conn, $sql)) {
                echo "Item Removed successfully";
                header('Location: index.php');
            } else {
                echo "Error Removing item: " . mysqli_error($conn);
            }
        }
        function mySort($a,$b){//((DS ? DS : DMT) >= DATE(NOW())), dále podle PS
            if( ($a["besDate"]=="0000-00-00") & $b["besDate"]=="0000-00-00"){
                $tmpDate_a1= strtotime(  $a["manDate"]=="0000-00-00" ? $a["buyDate"] : $a["manDate"] );
                $tmpDate_a2= strtotime(  $a["expDate"] );
                $tmpDate_b1= strtotime(  $b["manDate"]=="0000-00-00" ? $b["buyDate"] : $b["manDate"] );
                $tmpDate_b2= strtotime(  $b["expDate"] );
                $a_ps= ($tmpDate_a1+$tmpDate_a2)/2 ;
                $b_ps= ($tmpDate_b1+$tmpDate_b2)/2 ;
                return $a_ps <=> $b_ps;
            }else{
                return $a["besDate"] <=> $b["besDate"];
            }
        }
        ?>
</body>
</html>