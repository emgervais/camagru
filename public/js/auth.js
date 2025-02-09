let currentPage = 1;
let loading = false;
let hasMore = true;
document.addEventListener('DOMContentLoaded', function() {
    isLogged();
    loadPosts();
    likeCommentListener();
    document.getElementById('overlay').addEventListener('click', hideLogin);
    document.getElementById('modify-form').addEventListener('submit', changeInfo);
    document.getElementById('comment-form').addEventListener('submit', sendComment);
    document.getElementById('comments-overlay').addEventListener('click', hideComments)
    document.getElementById('main-feed').addEventListener('scroll', scrollHandler);
});
function scrollHandler() {
    const {scrollHeight, scrollTop, clientHeight} = document.getElementById('main-feed');
    if (scrollTop + clientHeight >= scrollHeight - 5 && !loading && hasMore) {
        loadPosts();
    }
}
function sendComment(e) {
    e.preventDefault();
    const comment = document.getElementById('comment').value;
    const id = document.getElementById('comments').getAttribute('data-id');
    if (!comment) {
        alert('Please write a comment');
        return;
    }
    fetch('/api/sendComment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({id: id, comment: comment}),
    }).then(response => {
        if (!response.ok) {
            return response.text().then(errorMessage => {
                alert(errorMessage);
                throw new Error(errorMessage);
            });
        }
        return response.json();
    }
    ).then(data => {
        const div = document.createElement('div');
        div.classList.add('comment');
        div.innerHTML = `
        <p>${data.username}: ${comment}</p>`;
        document.getElementById('comments').appendChild(div);
        document.getElementById('comment').value = '';
    }
    ).catch(e)
        return;
}
function hideComments(e) {
    if (e.target.id != 'comments-overlay') return;
    e.target.style.display = 'none';
}
function likeCommentListener() {
        document.getElementById('feed').addEventListener('click', function(e) {
            if (e.target.classList.contains('likes')) {
                const id = e.target.getAttribute('data-id');
                const like = e.target.classList.contains('fa-solid') ? 0 : 1;
                fetch('/api/like', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({id: id, like: like}),
                }).then(response => {
                    if (!response.ok) {
                        return response.text().then(errorMessage => {
                            alert(errorMessage);
                            throw new Error(errorMessage);
                        });
                    }
                    return response.json();
                }).then(data => {
                    if (like) {
                        e.target.innerHTML = parseInt(e.target.innerHTML) + 1;
                        e.target.classList.add('fa-solid');
                        e.target.classList.remove('fa-regular');
                    } else {
                        e.target.innerHTML = parseInt(e.target.innerHTML) - 1;
                        e.target.classList.add('fa-regular');
                        e.target.classList.remove('fa-solid');
                    }
                }).catch(error => {
                    console.error('Error:', error);
                });
            }
            else if (e.target.classList.contains('comment-button')) {
                fetch('/api/getComment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({id: e.target.getAttribute('data-id')}),
                }).then(response => {
                    if (!response.ok) {
                        return response.text().then(errorMessage => {
                            alert(errorMessage);
                            throw new Error(errorMessage);
                        });
                    }
                    return response.json();
                }).then(data => {
                    const el = document.getElementById('comments');
                    el.setAttribute('data-id', e.target.getAttribute('data-id'));
                    el.innerHTML = '';
                    data.forEach(comment => {
                        const div = document.createElement('div');
                        div.classList.add('comment');
                        const p = document.createElement('p');
                        p.innerText = `${comment.username}: ${comment.comment}`;
                        div.appendChild(p);
                        el.appendChild(div);
                    });
                    document.getElementById('comments-overlay').style.display = 'flex';
                }).catch(Error)
                    return;
            }
        });
}
function loadPosts() {
    try {
        loading = true;
        fetch(`/api/posts?page=${currentPage}`).then(response =>
            {
            if (!response.ok) {
                alert('failed to load posts');
                throw new Error('failed to load posts');
            }
            return response.json();
        }).then(posts => {
            if (posts.length == 0) {
                hasMore = false;
                return;
            }
            const el = document.getElementById('feed');
            posts.forEach(post => {
                const div = document.createElement('div');
                div.classList.add('post');
                div.innerHTML = `
                <img src="${post.image_path}" alt="post image">
                <div>
                    <i class="fa-regular fa-heart likes" data-id="${post.id}">${post.likes}</i>
                    <button onclick="deletePost(${post.id})">delete</button>
                    <a data-id="${post.id}" class="comment-button">comment</a>
                </div>`;
                el.appendChild(div);
            });
            currentPage++;
            loading = false;
        })
    }
    catch (e) {
        console.error(e);
    }
}
function switchLogin(logged) {
        const el = document.getElementsByClassName('if-not-logged');
        for (let i = 0; i < el.length; i++) {
            el[i].style.display = logged ? 'none': 'block';
        }
        const el2 = document.getElementsByClassName('if-logged');
        for (let i = 0; i < el2.length; i++) {
            el2[i].style.display = logged ? 'block': 'none';
        }
}
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
            switchLogin(true);
        } else {
            switchLogin(false);
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
    document.getElementById('overlay').style.display = 'flex';
}
function showRegister() {
    document.getElementById('login').style.display = 'none';
    document.getElementById('forgot').style.display = 'none';
    document.getElementById('register').style.display = 'flex';
    document.getElementById('overlay').style.display = 'flex';
}
function showForgot() {
    document.getElementById('login').style.display = 'none';
    document.getElementById('forgot').style.display = 'flex';
}
function hideLogin(e) {
    if (e.target.id != 'overlay') return;
    e.target.style.display = 'none';
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
        window.location.href = '/';
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
        return response.json();
    }).then(data => {
        if (data.status == 'success')
            alert(data.message);
        else
            alert(data.message);
    })
}
function getGallery() {
    fetch('/api/gallery').then(response => {
        if (!response.ok) {
            alert('Please connect to access Gallery');
            throw new Error('help');
        }
        return response.text();
    }).then(response => {
        document.body.innerHTML = response;
        const postScript = document.createElement('script');
        postScript.src = '../js/post.js';
        document.body.appendChild(postScript);
    }).catch(Error)
        return;
}
function deletePost(id) {
    fetch('/api/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({id: id}),
    }).then(response => {
        if (!response.ok) {
            return response.text().then(errorMessage => {
                alert(errorMessage);
                throw new Error(errorMessage);
            });
        }
        return response.json();
    }).then(data => {
        alert(data.message);
        window.location.href = '/';
    }).catch(Error) 
        return;
}
function notification() {
    fetch('/api/notification').then(response => {
        if (!response.ok) {
            alert('problem');
            throw new Error('help');
        }
        return response.text();
    }).then(response => {
        alert(response);
    }).catch(Error)
        return;
}