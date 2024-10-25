<?php
// Get the server's IP address
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
        const serverIP = "<?php echo $serverIP; ?>";
        let gamepadIndex = null;
        const deadzoneThreshold = 0.1; // Define the deadzone threshold for the axes

        const buttonLabels = [
            "A", "B", "X", "Y", "Left Bumper", "Right Bumper", "Left Trigger", "Right Trigger",
            "Select", "Start", "Left Stick", "Right Stick", "D-Pad Up", "D-Pad Down", "D-Pad Left", "D-Pad Right"
        ];
        const axisLabels = ["Left Stick X", "Left Stick Y", "Right Stick X", "Right Stick Y"];

        // Send gamepad data to the Flask server only if data is meaningful
        function sendGamepadData(data) {
            if (!serverIP || !data.buttons.length && !data.axes.length) return; // Skip if no active input

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

        // Listen for gamepad connection and disconnection
        window.addEventListener("gamepadconnected", (event) => {
            gamepadIndex = event.gamepad.index;
            document.getElementById("gamepad-status").textContent = `Gamepad connected: ${event.gamepad.id}`;
            updateGamepadStatus();
        });

        window.addEventListener("gamepaddisconnected", () => {
            document.getElementById("gamepad-status").textContent = "Gamepad disconnected.";
            gamepadIndex = null;
        });

        // Update gamepad status, filter for active buttons and deadzone axes, and send data to server
        function updateGamepadStatus() {
            if (gamepadIndex === null) return;

            const gamepad = navigator.getGamepads()[gamepadIndex];
            if (!gamepad) return;

            let statusText = `Gamepad: ${gamepad.id}\n\n`;

            // Filter for active buttons
            const activeButtons = gamepad.buttons.map((button, index) => ({
                label: buttonLabels[index] || `Button ${index}`,
                pressed: button.pressed
            })).filter(button => button.pressed);

            // Display active buttons only
            activeButtons.forEach(button => {
                statusText += `${button.label}: Pressed\n`;
            });

            // Filter axes within the deadzone threshold
            const filteredAxes = gamepad.axes.map((axis, index) => {
                const value = Math.abs(axis) < deadzoneThreshold ? 0 : axis.toFixed(2);
                return {
                    label: axisLabels[index] || `Axis ${index}`,
                    value: value
                };
            }).filter(axis => axis.value !== 0);

            // Display active axes only
            filteredAxes.forEach(axis => {
                statusText += `${axis.label}: ${axis.value}\n`;
            });

            document.getElementById("gamepad-status").textContent = statusText;

            // Prepare data to send to the server
            const data = {
                id: gamepad.id,
                buttons: activeButtons,
                axes: filteredAxes
            };

            // Send filtered data to the server
            sendGamepadData(data);

            // Continue polling for updates
            requestAnimationFrame(updateGamepadStatus);
        }
    </script>
</body>
</html>
