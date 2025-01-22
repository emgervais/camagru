function showLogin() {
    document.getElementById('login').style.display = 'flex';
    document.getElementById('register').style.display = 'none';
}

function showRegister() {
    document.getElementById('login').style.display = 'none';
    document.getElementById('register').style.display = 'flex';
}

function showModify() {
    const el = document.getElementById('modify');
    el.style.display == 'flex' ? el.style.display = 'none' : el.style.display = 'flex';
}

function login() {
    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;

    if (!username || !password)
        alert('Please fill out all fields');
    // if(password.length < 7 || password == password.tolowercase() || password == password.toUpperCase() || !/\d/.test(password))
    //     alert('Password must be at least 7 characters, contain upper and lowercase letters, and at least one number');
    fetch('/api/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username, password })
    }).then(res => {
        if (res.status === 200) {
            // window.location.href = '/';
            console.log('Login successful');
        } else {
            alert('Login failed');
        }
    });
}