<?php
// This PHP block gets the server's IP address
$serverIP = $_SERVER['SERVER_ADDR'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gamepad Status</title>
</head>
<body>
    <h1>Gamepad Status</h1>
    <div id="gamepad-status">No gamepad detected.</div>

    <script>
        // Server IP from PHP
        const serverIP = "<?php echo $serverIP; ?>";
        let gamepadIndex = null;

        // Map to label buttons and axes for common gamepads
        const buttonLabels = [
            "A", "B", "X", "Y", "Left Bumper", "Right Bumper", "Left Trigger", "Right Trigger",
            "Select", "Start", "Left Stick", "Right Stick", "D-Pad Up", "D-Pad Down", "D-Pad Left", "D-Pad Right"
        ];
        const axisLabels = ["Left Stick X", "Left Stick Y", "Right Stick X", "Right Stick Y"];

        // Send gamepad data to the Flask server using XMLHttpRequest
        function sendGamepadData(data) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", `http://${serverIP}:5000/update_gamepad`, true);
            xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log("Server response:", xhr.responseText);
                }
            };
            xhr.send(JSON.stringify(data));
        }

        // Listen for gamepad connection and disconnection events
        window.addEventListener("gamepadconnected", (event) => {
            gamepadIndex = event.gamepad.index;
            document.getElementById("gamepad-status").textContent = `Gamepad connected: ${event.gamepad.id}`;
            updateGamepadStatus();
        });

        window.addEventListener("gamepaddisconnected", () => {
            document.getElementById("gamepad-status").textContent = "Gamepad disconnected.";
            gamepadIndex = null;
        });

        // Update gamepad status, display button/axis labels, and send data to server
        function updateGamepadStatus() {
            if (gamepadIndex === null) return;

            const gamepad = navigator.getGamepads()[gamepadIndex];
            if (!gamepad) return;

            let statusText = `Gamepad: ${gamepad.id}\n\n`;

            // Display button states with labels
            gamepad.buttons.forEach((button, index) => {
                const label = buttonLabels[index] || `Button ${index}`;
                statusText += `${label}: ${button.pressed ? 'Pressed' : 'Released'}\n`;
            });

            // Display axis states with labels
            gamepad.axes.forEach((axis, index) => {
                const label = axisLabels[index] || `Axis ${index}`;
                statusText += `${label}: ${axis.toFixed(2)}\n`;
            });

            // Update the status div with the labeled status
            document.getElementById("gamepad-status").textContent = statusText;

            // Prepare data to send to the server
            const data = {
                id: gamepad.id,
                buttons: gamepad.buttons.map((button, index) => ({
                    label: buttonLabels[index] || `Button ${index}`,
                    pressed: button.pressed
                })),
                axes: gamepad.axes.map((axis, index) => ({
                    label: axisLabels[index] || `Axis ${index}`,
                    value: axis.toFixed(2)
                }))
            };

            // Send data to the server
            sendGamepadData(data);

            requestAnimationFrame(updateGamepadStatus); // Continuously poll for updates
        }
    </script>
</body>
</html>
