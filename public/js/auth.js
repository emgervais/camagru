function showLogin() {
    document.getElementById('login').style.display = 'flex';
    document.getElementById('register').style.display = 'none';
}

function showRegister() {
    document.getElementById('login').style.display = 'none';
    document.getElementById('register').style.display = 'flex';
}

function hideLogin() {
    document.getElementById('login').style.display = 'none';
    document.getElementById('register').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}
function showModify() {
    const el = document.getElementById('modify');
    el.style.display == 'flex' ? el.style.display = 'none' : el.style.display = 'flex';
}

function login() {
    console.log('Logging in...');
        const username = document.getElementById('login-username').value;
        const password = document.getElementById('login-password').value;

        if (!username || !password) {    
            alert('Please fill out all fields');
            return;
        }

        fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            mode: 'cors',
            body: JSON.stringify({ 
                username: username, 
                password: password 
            })
        }).then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        }).then(data => {
            console.log('Response:', data);
            if (data.token) {
                document.cookie = 'token=' + data.token;
                // window.location.href = '/';
                hideLogin();
            } else {
                alert('Login failed: Invalid response from server');
            }
        }).catch(error => {
            console.error('Login error:', error);
            alert(`Login failed: ${error.message}`);
        });
}

function register() {
    console.log('Logging in...');
        const username = document.getElementById('register-username').value;
        const password = document.getElementById('register-password').value;
        const email = document.getElementById('register-email').value;

        if (!username || !password || !email) {    
            alert('Please fill out all fields');
            return;
        }

        fetch('/api/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            mode: 'cors',
            body: JSON.stringify({ 
                email: email,
                username: username, 
                password: password 
            })
        }).then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        }).then(data => {
            console.log('Response:', data);
            if (data.token) {
                document.cookie = 'token=' + data.token;
                // window.location.href = '/';
                hideLogin();
            } else {
                alert('Login failed: Invalid response from server');
            }
        }).catch(error => {
            console.error('Login error:', error);
            alert(`Login failed: ${error.message}`);
        });
}
