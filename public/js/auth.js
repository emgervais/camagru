document.addEventListener('DOMContentLoaded', function() {
    showLogin();
    isLogged();  
    document.getElementById('modify-form').addEventListener('submit', changeInfo);
});







function isLogged() {
    fetch('/api/isLogged', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
}    }).then(response => {
        return response.json();
    }).then(data => {
        if (data.logged) {
            hideLogin();
        }
    })
    window.history.pushState({}, '', '/');
}
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
    document.getElementById('forgot').style.display = 'none';
}

function showRegister() {
    document.getElementById('login').style.display = 'none';
    document.getElementById('register').style.display = 'flex';
}

function showForgot() {
    document.getElementById('login').style.display = 'none';
    document.getElementById('forgot').style.display = 'flex';
}
function hideLogin() {
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

function forgotPassword() {
    const email = document.getElementById('forgot-email').value;

    if (!email) {    
        alert('Please fill out all fields');
        return;
    }
    fetch('/api/forgotPassword', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({email: email}),
        credentials: 'same-origin',
        mode: 'cors',
    }).then(response => {
        if (!response.ok) {
            alert('failed to send email');
            return;
        }
        alert('Email sent, please check your email');
    })
}

function changeInfo(e) {
    e.preventDefault();
    const email = document.getElementById('modify-email').value;
    const username = document.getElementById('modify-username').value;
    const password = document.getElementById('modify-password').value;

    fetch('/api/changeInfo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({email: email, username: username, password: password}),
        credentials: 'same-origin',
        mode: 'cors',
    }).then(response => {
        if (response.ok) {
            alert('changes saved');
            return;
        }
        return response.json();
    }).then(data => {
        alert(data.message);
    })
}