<?php
// Get the server's IP address
$server_ip = $_SERVER['SERVER_ADDR'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Mouse</title>
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
        #box {
            width: 296px;
            height: 296px;
            border: 2px solid #000;
            margin-top: 20px;
            position: relative;
            background-color: #eaeaea;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <!--
    <h1>Control the Motor</h1>
    <button onclick="controlMotor('move_forward', 360)">Move Forward</button>
    <button onclick="controlMotor('move_backward', 360)">Move Backward</button>
    -->

    <h1>Track Cursor Position</h1>
    <p>Move your mouse in the circle!</p>
    <div id="box"></div>
    <button onclick="sendMousePositionToFlask(150,150)">Stop</button>
    <div>
        <button onclick="sendMousePositionToFlash(150, 0)">Forward</button>
        <button onclick="sendMousePositionToFlash(0, 150)">Left</button>
        <button onclick="sendMousePositionToFlash(300, 150)">Right</button>
        <button onclick="sendMousePositionToFlash(150, 300)">Backwards</button>

    </div>

    <script>
        // Motor control function
        /*function controlMotor(command, degrees) {
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
            */

        // Mouse tracking logic
        const box = document.getElementById('box');
        box.addEventListener('mousemove', (event) => {
            const rect = box.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            // Log the current mouse position
            console.log(`Mouse Position: (${x}, ${y})`);

            // Send the mouse position to Flask server 
            sendMousePositionToFlask(x, y);
        });

        // Function to send mouse position data with XMLHttpRequest
        function sendMousePositionToFlask(x, y) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "http://<?php echo $server_ip; ?>:5000/receive_mouse_position", true);
            xhr.setRequestHeader("Content-Type", "application/json");

            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        console.log('Mouse data sent:', response);
                    } else {
                        console.error('Error sending mouse data:', xhr.status);
                    }
                }
            };

            const data = JSON.stringify({ x: x, y: y });
            xhr.send(data);
        }
    </script>
</body>
</html>
