function isLogged() {
    fetch('/api/isLogged', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
}    }).then(response => {
        return response.json();
    }).then(data => {
        console.log('Response:', data);
        if (data.logged) {
            hideLogin();
        }
    })
    window.history.pushState({}, '', '/');
}
isLogged();  

function logout() {
    fetch('/api/logout', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    }).then(response => {
        return response.json();
    }).then(data => {
        console.log('Response:', data);
        if (data.logged) {
            alert('Logout failed');
        }
        else
            window.location.href = '/';
    })
}

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
            alert('Login failed');
            return;
        }
        hideLogin();
    })
}

function register() {
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
            alert('failed to register try again');
            return;
        }
        alert('Registration successfull, please check your email to confirm');
    })
}
