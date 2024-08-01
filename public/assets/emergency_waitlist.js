
function updatePatient(){
    var name = document.getElementById('1name').value;
    var last_name = document.getElementById('2name').value;
    var date = document.getElementById('1date').value;
    var time = document.getElementById('1time').value;
    var priority = document.getElementById('1priority').value;
    var identifier = document.getElementById('updatePatient').value;

    console.log("here");
    console.log(name);

    $.ajax({
        url: './assets/emergency_waitlist.php',
        type: 'POST',
        data: { action: 'updatePatient', 'identifier': identifier, 'name': name, 'last_name': last_name, 'date': date, 'time': time, 'priority': priority},
        success: function(data){
            console.log('data ', data);
            var result = JSON.parse(data);
            var table = document.getElementById("patientList");
            var rows = result.patient_list;

            localStorage.setItem('patientList', rows);

            window.location.href = '../Public/adminPortal.html';
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.error('Error:', errorThrown);
        }
    });

    return false;
}

function login(){

    var username = document.getElementById('uname').value;
    var password = document.getElementById('psw').value;

    $.ajax({
        url: './assets/emergency_waitlist.php',
        type: 'POST',
        data: { action: 'login', 'username': username, 'password': password},
        success: function(data){
            console.log('data ', data);
            var result = JSON.parse(data);
            if(result.role == 'incorrect'){
               showAlert(); 
            }
            else if(result.role == 'ad'){
                administration();
            }
            else{
                patient(result.username);
            }
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.error('Error:', errorThrown);
        }
    });

    return false;
}

function patient(username){
    var role = "pa";

    console.log(username);

    console.log("here");

    $.ajax({
        url: './assets/emergency_waitlist.php',
        type: 'POST',
        data: { action: 'generateTime', 'username': username},
        success: function(data){
            console.log('data ', data);
            var result = JSON.parse(data);
            var value = result.WaitingTime;

            localStorage.setItem('WaitingTime', value);

            window.location.href = '../Public/patientPortal.html';
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.error('AJAX Error:', textStatus, errorThrown);
            console.error('Response Text:', jqXHR.responseText);
        }
    });
}

function administration(){
    var role = "ad";

    console.log("here");

    $.ajax({
        url: './assets/emergency_waitlist.php',
        type: 'POST',
        data: { action: 'generate', 'role': role},
        success: function(data){
            console.log('data ', data);
            var result = JSON.parse(data);
            var table = document.getElementById("patientList");
            var rows = result.patient_list;

            localStorage.setItem('patientList', rows);

            window.location.href = '../Public/adminPortal.html';
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.error('Error:', errorThrown);
        }
    });
}

function addNewPatient(){
    var name = document.getElementById('name').value;
    var last_name = document.getElementById('last_name').value;
    var date = document.getElementById('date').value;
    var time = document.getElementById('time').value;
    var priority = document.getElementById('priority').value;


    $.ajax({
        url: './assets/emergency_waitlist.php',
        type: 'POST',
        data: { action: 'addNewPatient', 'name': name, 'last_name': last_name, 'date': date, 'time': time, 'priority': priority},
        success: function(data){
            console.log('data ', data);
            var result = JSON.parse(data);
            var table = document.getElementById("patientList");
            var rows = result.patient_list;
            table.querySelector('tbody').innerHTML = rows;
            var username = result.username;
            var password = result.password;

            alert("Your patient's username is ${username} and password is ${password}");
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.error('Error:', errorThrown);
        }
    });

    return false;
}


function removePatient(){
    var identifier = document.getElementById('updatePatient').value;

    $.ajax({
        url: './assets/emergency_waitlist.php',
        type: 'POST',
        data: { action: 'removePatient', 'identifier': identifier},
        success: function(data){
            console.log('data ', data);
            var result = JSON.parse(data);
            var table = document.getElementById("patientList");
            var rows = result.patient_list;
            table.querySelector('tbody').innerHTML = rows;
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.error('Error:', errorThrown);
        }
    });

    return false;
}



function showAlert(){
    alert("Your username or password is incorrect");
}