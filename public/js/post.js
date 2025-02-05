class draggableAddon {
    constructor(id, src) {
        this.id = id;
        this.offsetX = 0;
        this.offsetY = 0;
        this.src = src;
        this.isDragging = -1;
    }
}
class draggableManager {
    constructor() {
        this.addons = [];
        this.draggable = -1;
    }
    addAddon(id, src) {
        const addon = new draggableAddon(id, src);
        this.addons.push(addon);
    }
    removeAddon(id) {
        const index = this.addons.findIndex(addon => addon.id === id);
        if (index !== -1)
            this.addons.slice(index, 1);
    }
    dragStart(e) {
        if (!e.target.classList.contains('add-img')) return;
        const id = e.target.getAttribute('data-id');
        const index = this.addons.findIndex(addon => addon.id === id);
        if(index === -1) return;
        if (this.draggable !== -1) {
            this.draggable = -1;
            e.target.style.cursor = 'default';
            // this.addons[index].isDragging = false;
        } else {
            this.draggable = index;
            e.target.style.cursor = 'grab';
            // this.addons[index].isDragging = true;
        }
    }
    drag(e) {
        if (this.draggable == -1) return;
        e.preventDefault();
        const addon = this.addons[this.draggable];
        const element = document.querySelector(`[data-id="${addon.id}"]`);
        let x = e.clientX - addon.offsetX;
        let y = e.clientY - addon.offsetY;
        const container = document.getElementById('main-img');
        const containerRect = container.getBoundingClientRect();
        const imgRect = element.getBoundingClientRect();
        
        x = Math.max(containerRect.left + imgRect.width / 2, x);
        x = Math.min(containerRect.right - imgRect.width / 2, x);
        
        y = Math.max(containerRect.top + imgRect.height / 2, y);
        y = Math.min(containerRect.bottom - imgRect.height / 2, y);
        element.style.left = `${x - imgRect.width / 2}px`;
        element.style.top = `${y - imgRect.height / 2}px`;
        element.style.position = 'absolute';
    }
    press(e) {
        if (this.draggable === -1) return;
        if ((e.key === '+' || e.key === '-')) {
            const addon = this.addons[this.draggable];
            const element = document.querySelector(`[data-id="${addon.id}"]`);
            const sizeDelta = e.key === '+' ? 10 : -10;
            const newWidth = element.clientWidth + sizeDelta;
            element.style.width = `${newWidth}px`;
        }
    }

}


const manager = new draggableManager();
document.getElementById('history').addEventListener('click', deleteImg);
document.getElementById('gallery-addons').addEventListener('click', loadaddons);
document.getElementById('img-container').addEventListener('click', (e) => manager.dragStart(e));
document.addEventListener('mousemove', (e) => manager.drag(e));
document.addEventListener("keypress", (e) => manager.press(e));
setUpCamera();
let currentFile = null;
let currentId = 0;
const creations = [];
function setUpCamera() {
    let camera = navigator.mediaDevices.getUserMedia({
        video: true
    });
    let img = null;
    img = document.createElement('video');
    img.id = 'main-img';
    camera.then(stream => {
        img.srcObject = stream;
        document.getElementById('img-container').appendChild(img);
        img.addEventListener("loadedmetadata", () => {
            img.play();
        });
    }).catch(err => {
        img = document.createElement('img');
        img.src = '../img/egervais.jpg';
        img.id = 'main-img';
        document.getElementById('img-container').appendChild(img);
    });
    
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
function upload() {
    const file = document.getElementById('file');
    
    file.click()
    file.onchange = function(e) {
        currentFile = e.target.files[0];
    
        if (!currentFile) return;
        
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        if (!validTypes.includes(currentFile.type)) {
            alert('Please select a valid image file (JPEG, PNG, or GIF)');
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(event) {
            const container = document.getElementById('img-container');
            container.innerHTML = '';
            const img = document.createElement('img');
            img.id = 'main-img';
            img.src = event.target.result;
            container.appendChild(img);
        };
        
        reader.onerror = function() {
            alert('Error reading file');
        };
        
        reader.readAsDataURL(currentFile);
    };
}
function mergeImages() {
    if (document.getElementsByClassName('add-img').length === 0) {
        alert('Please select at least one addon');
        return;
    };
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    const mainImg = document.getElementById('main-img');
    const isVideo = mainImg.tagName.toLowerCase() === 'video';
    const overlayImg = document.getElementsByClassName('add-img');
    let width = mainImg.width;
    let height = mainImg.height;
    if (isVideo) {
        width = mainImg.videoWidth;
        height = mainImg.videoHeight;
    }
    const canvas2 = document.createElement('canvas');
    canvas.width = canvas2.width = width;
    canvas.height = canvas2.height = height;
    const addons = []
    ctx.drawImage(mainImg, 0, 0, canvas.width, canvas.height);
    const main = canvas2.getContext('2d');
    main.drawImage(mainImg, 0, 0, canvas2.width, canvas2.height);
    for(let i = 0; i < overlayImg.length; i++) {
        const img = overlayImg[i];
        let top = img.offsetTop - mainImg.offsetTop;
        let left = img.offsetLeft - mainImg.offsetLeft;
        console.log(img.width, img.height);
        ctx.drawImage(img, left, top, img.width, img.height);
        addons.push({'top': top, 'left': left, 'src': img.src, 'width': img.width, 'height': img.height});
    }
    
    const history = document.getElementById('history');
    const div = document.createElement('div');
    div.classList.add('saved-img');
    const img = canvas.toDataURL('image/png');
    div.innerHTML = `
                <button class="publish-buton" onclick="publish(${currentId})">Publish</button>
                <button class="delete-img" data-id="${currentId}">X</button>
                <img src="${img}" alt="Merged image">`;
    history.appendChild(div);
    creations.push(new creation(currentId++, addons, canvas2.toDataURL('image/png', 1)));
}
class creation {
    constructor(id, addons, dest) {
        this.id = id;
        this.addons = addons;
        this.dest = dest;
    }
}
function deleteImg(e) {
    const el = e.target;
    if(el.classList.contains('delete-img')) {
        const id = el.getAttribute('data-id');
        const index = creations.findIndex(creation => creation.id === id);
        if (index !== -1)
            creations.slice(index, 1);
        el.parentNode.remove();
    }
}
function loadaddons(e) {
    if (e.target.classList.contains('addons')) {
        const id = e.target.getAttribute('data-id');
        const container = document.getElementById('img-container');
        const el = container.querySelector(`img[data-id="a${id}"]`);
        if (el) {
            el.remove();
            manager.removeAddon(id)
            return;
        }
        const img = document.createElement('img');
        img.src = e.target.src;
        img.classList.add('add-img');
        img.setAttribute('data-id', `a${id}`);
        container.appendChild(img);
        manager.addAddon(`a${id}`, img.src);
    }
}
function getBase64Dimensions(base64String) {
    return new Promise((resolve) => {
      const img = new Image();
      img.src = base64String;
      img.onload = () => {
        resolve({
          width: img.width,
          height: img.height
        });
      };
    });
  }
async function publish(id) {
    const creation = creations.find(creation => creation.id === id);
    const dimensions = await getBase64Dimensions(creation.dest);
    console.log(dimensions);
    const formData = new FormData();
    formData.append('dest', creation.dest);
    formData.append('addons', JSON.stringify(creation.addons));
    fetch('/api/publish', {
        method: 'POST',
        body: formData
    }).then(response => {
        return response.json();
    }).then(data => {
        if (data.error) {
            alert(data.error);
        }
        else {
            alert('Image published');
        }
    });
}