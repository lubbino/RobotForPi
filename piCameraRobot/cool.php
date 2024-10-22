<?php
// Get the server's IP address
$server_ip = $_SERVER['SERVER_ADDR'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Control Mostor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        button {
            padding: 10px 20px;
            margin: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Control the Motor</h1>
    <button onclick="controlMotor('move_forward', 360)">Move Forward</button>
    <button onclick="controlMotor('move_backward', 360)">Move Backward</button>

    <script>
        function controlMotor(command, degrees) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "http://<?php echo $server_ip; ?>:5000/control_motor", true);
            xhr.setRequestHeader("Content-Type", "application/json");

            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        alert('Motor executed command: ' + response.command + ' by ' + response.degrees + ' degrees');
                    } else {
                        alert('Error: ' + xhr.status);
                    }
                }
            };

            const data = JSON.stringify({
                command: command,
                degrees: degrees
            });

            xhr.send(data);
        }
    </script>
</body>
</html>