from flask import Flask, request, jsonify, render_template
from werkzeug.security import generate_password_hash, check_password_hash
import re

app = Flask(__name__)

users_db = {
    "admin": generate_password_hash("123")
}

def authenticate(username, password):
    if username in users_db and check_password_hash(users_db[username], password):
        return True
    return False

@app.route('/', methods=['GET'])
def home():
    return render_template('index.html')

@app.route('/api/login', methods=['POST'])
def login():
    data = request.json
    if not data or not data.get('username') or not data.get('password'):
        return jsonify({"error": "Missing username or password"}), 400

    username = data['username']
    password = data['password']

    if authenticate(username, password):
        return jsonify({"message": "Login successful"}), 200
    else:
        return jsonify({"error": "Invalid username or password"}), 401

@app.route('/register', methods=['POST'])
def register():
    data = request.json

    if not data or not data.get('username') or not data.get('password'):
        return jsonify({"error": "Missing username or password"}), 400

    username = data['username']
    password = data['password']

    if username in users_db:
        return jsonify({"error": "User already exists"}), 400

    if not re.match(r'^[a-zA-Z0-9_]{3,}$', username):
        return jsonify({"error": "Invalid username format"}), 400

    users_db[username] = generate_password_hash(password)
    return jsonify({"message": "User registered successfully"}), 201

if __name__ == '__main__':
    app.run(debug=True)
