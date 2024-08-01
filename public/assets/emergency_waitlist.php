<?php
session_start();
session_destroy();
session_start();

$_SESSION["role"] = "";
$_SESSION["username"] = "";
$_SESSION["patient_list"] = "";
$_SESSION["WaitingTime"] = "";

function login(){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $dsn = "pgsql:host=localhost;port=5432;dbname=Emergency;";
        $dbconn = new PDO($dsn, "postgres", "1234");

    $sql =<<<EOF
        SELECT * FROM accounts WHERE username = :username AND password = :password
        EOF;

        $stmt = $dbconn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 0){
            foreach($result as $row){
                $_SESSION["role"] = $row["role"];
                $_SESSION["username"] = $row["username"];
            }
        } else {
            $_SESSION["role"] = "incorrect";
            $_SESSION["username"] = "incorrect";
        }

}

function addNewPatient(){
    $name = $_POST['name'];
    $last_name = $_POST['last_name'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $priority = $_POST['priority'];
    $username = $name . $last_name;

    $dsn = "pgsql:host=localhost;port=5432;dbname=Emergency;";
    $dbconn = new PDO($dsn, "postgres", "1234");

    $sql =<<<EOF
        INSERT INTO patients_list (first_name, last_name, arrival_date, arrival_time, updated_date, updated_time, priority, username) VALUES ('$name', '$last_name', '$date', '$time', '$date', '$time', '$priority', '$username')
    EOF;

    $stmt = $dbconn->prepare($sql);

    $stmt->execute();

    $password = date("h") . $priority;
    $role = 'pa';

    $sql =<<<EOF
        INSERT INTO accounts VALUES (:username, :password, :role)
    EOF;

    $stmt = $dbconn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':role', $role);

    $stmt->execute();

    $_SESSION['username'] = $username;
    $_SESSION['password'] = $password;

    generatePatients();
}

function generatePatients(){
    $dsn = "pgsql:host=localhost;port=5432;dbname=Emergency;";
    $dbconn = new PDO($dsn, "postgres", "1234");

    $sql =<<<EOF
        SELECT * FROM patients_list ORDER BY priority, updated_date, updated_time
    EOF;

    $stmt = $dbconn->prepare($sql);

    $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $List = "<tr><th>Identifier</th><th>First Name</th><th>Last Name</th><th>Arrival Date</th><th>Arrival Time</th><th>Updated Time</th><th>Priority</th><th>Change Profile</th>";

        if (count($result) > 0){
            foreach($result as $row){
                $List = $List."<tr><td>".$row["identifier"]."</td><td>".$row["first_name"]."</td><td>".$row["last_name"]."</td><td>".$row["arrival_date"]."</td><td>".$row["arrival_time"]."</td><td>".$row["updated_time"]."</td><td>".$row["priority"]."</td><td><button class=\"open-button\" value=\"".$row["identifier"]."\" onclick=\"changeProfile(this)\">Change Profile</button></tr>";
            }
        } else {
        }

        $_SESSION["patient_list"] = $List;

}

function updatePatient(){
    $name = $_POST['name'];
    $last_name = $_POST['last_name'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $priority = $_POST['priority'];
    $identifier = $_POST['identifier'];

    $dsn = "pgsql:host=localhost;port=5432;dbname=Emergency;";
    $dbconn = new PDO($dsn, "postgres", "1234");

    $sql =<<<EOF
        UPDATE patients_list SET first_name='$name', last_name='$last_name', updated_date='$date', updated_time='$time', priority='$priority' WHERE identifier = $identifier
    EOF;

    $stmt = $dbconn->prepare($sql);

    $stmt->execute();

    generatePatients();
}

function removePatient(){
    $identifier = $_POST['identifier'];

    $dsn = "pgsql:host=localhost;port=5432;dbname=Emergency;";
    $dbconn = new PDO($dsn, "postgres", "1234");

    $sql =<<<EOF
        SELECT username FROM patients_list WHERE identifier = $identifier
        EOF;

    $stmt = $dbconn->prepare($sql);

    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($result)>0){
        foreach($result as $row){
            $username = $row["username"];

            $sql =<<<EOF
                DELETE FROM accounts WHERE username = '$username'
            EOF;
            $stmt = $dbconn->prepare($sql);

    $stmt->execute();
        }
    }

    $sql =<<<EOF
        DELETE FROM patients_list WHERE identifier = $identifier
    EOF;

    $stmt = $dbconn->prepare($sql);

    $stmt->execute();

    generatePatients();
}

function generateTime(){
    $username = $_POST['username'];

    $dsn = "pgsql:host=localhost;port=5432;dbname=Emergency;";
    $dbconn = new PDO($dsn, "postgres", "1234");

    $sql =<<<EOF
       SELECT priority FROM patients_list WHERE priority < ( SELECT priority FROM patients_list WHERE username = '$username') OR (priority = (SELECT priority FROM patients_list WHERE username = '$username') AND (updated_date < (SELECT updated_date FROM patients_list WHERE username = '$username') AND updated_time < ( SELECT updated_time FROM patients_list WHERE username = '$username')));
    EOF;

    $stmt = $dbconn->prepare($sql);

    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $WaitingTime = 0;

        if (count($result) > 0){
            foreach($result as $row){
                if($row['priority']==1){
                    $WaitingTime = $WaitingTime + 60;
                }
                else if($row['priority']==2){
                    $WaitingTime = $WaitingTime + 50;
                }
                else if($row['priority']==3){
                    $WaitingTime = $WaitingTime + 40;
                }
                else if($row['priority']==4){
                    $WaitingTime = $WaitingTime + 30;
                }
                else if($row['priority']==5){
                    $WaitingTime = $WaitingTime + 20;
                }
            }
        } else {
        }

        $_SESSION["WaitingTime"] = $WaitingTime;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if($_POST['action'] == 'login'){
        login();
        echo json_encode($_SESSION);
    }
    else if($_POST['action'] == 'addNewPatient'){
        addNewPatient();
        echo json_encode($_SESSION);
    }
    else if($_POST['action'] == 'generate'){
        generatePatients();
        echo json_encode($_SESSION);
    }
    else if($_POST['action'] == 'updatePatient'){
        updatePatient();
        echo json_encode($_SESSION);
    }
    else if($_POST['action'] == 'removePatient'){
        removePatient();
        echo json_encode($_SESSION);
    }
    else if($_POST['action'] == 'generateTime'){
        generateTime();
        echo json_encode($_SESSION);
    }
}

?>