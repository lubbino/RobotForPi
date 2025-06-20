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
    <style>
        #video-feed {
            max-width: 100%;
            height: auto;
            border: 1px solid black; /* Optional: Add border to video feed */
        }
    </style>
</head>
<body>
    <h1>Gamepad Status</h1>
    <div id="gamepad-status">No gamepad detected.</div>

    <!-- Video Feed from Flask server -->
    <h2>Video Feed</h2>
    <img id="video-feed" src="http://<?php echo $serverIP; ?>:5000/video_feed" alt="Video Feed" />

    <script>
        const serverIP = "<?php echo $serverIP; ?>";
        let gamepadIndex = null;
        const deadzoneThreshold = 0.1; // Define the deadzone threshold for the axes
        const throttleTime = 20; // Time in milliseconds between each send
        let lastSentTime = 0; // Store the last time data was sent

        const buttonLabels = [
            "A", "B", "X", "Y", "Left Bumper", "Right Bumper", "Left Trigger", "Right Trigger",
            "Select", "Start", "Left Stick", "Right Stick", "D-Pad Up", "D-Pad Down", "D-Pad Left", "D-Pad Right"
        ];
        const axisLabels = ["Left Stick X", "Left Stick Y", "Right Stick X", "Right Stick Y"];

        // Send gamepad data to the Flask server only if data is meaningful
        function sendGamepadData(data) {
            const currentTime = new Date().getTime();
            
            // Only send if enough time has passed since the last send
            if (currentTime - lastSentTime >= throttleTime) {
                lastSentTime = currentTime; // Update the last sent time

                if (!serverIP || !(data.buttons.length || data.axes.length)) return; // Skip if no active input

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
            }).filter(axis => axis.value !== null && axis.value !== undefined && axis.value !== "0.00");

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

        // Start polling for gamepad status when the page loads
        setInterval(() => {
            const gamepads = navigator.getGamepads();
            if (gamepads[0]) {
                updateGamepadStatus();  // Update status for the first gamepad
            }
        }, 20); // Check every 20ms

    </script>
</body>
</html>
