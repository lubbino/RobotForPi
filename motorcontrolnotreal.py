from flask import Flask, request, jsonify
from flask_cors import CORS
from buildhat import Motor
from time import sleep

leftMotor = Motor("A")
rightMotor = Motor("D")

app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

@app.route('/update_gamepad', methods=['POST'])
def update_gamepad():
    data = request.json  # Parse the JSON data sent from the client
    x_left = 0.0
    y_left = 0.0
    # Isolate relevant parts of the data
    gamepad_id = data.get('id')

    # Extract pressed buttons with labels
    buttons = [
        {"label": button["label"], "pressed": button["pressed"]}
        for button in data.get("buttons", [])
        if button["pressed"]  # Include only pressed buttons
    ]

    # Extract axes with labels and non-zero values
    axes = [
        {"label": axis["label"], "value": float(axis["value"])}
        for axis in data.get("axes", [])
        if axis["value"] != "0"  # Include only axes with significant movement
    ]

    # Print or log isolated data
    for axis in data.get("axes", []):
        if axis["label"] == "Left Stick X":
            x_left = float(axis["value"])
        elif axis["label"] == "Left Stick Y":
            y_left = float(axis["value"])

    motor_left_speed = y_left - x_left
    motor_right_speed = y_left + x_left

    if max(abs(motor_left_speed), abs(motor_right_speed)) > 1:
        motor_left_speed /= max(abs(motor_left_speed), abs(motor_right_speed))
        motor_right_speed /= max(abs(motor_left_speed), abs(motor_right_speed))
    
    motor_left_speed = max(-1, min(1, motor_left_speed))
    motor_right_speed = max(-1, min(1, motor_right_speed))

    motor_left_speed *= -1

    leftMotor.pwm(motor_left_speed)
    rightMotor.pwm(motor_right_speed)

    # Respond with isolated data for verification
    response_data = {
        "gamepad_id": gamepad_id,
        "buttons": buttons,
        "axes": axes
    }
    return jsonify(response_data), 200

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
