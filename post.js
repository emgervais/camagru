window.addEventListener('load', function() {
    const addImg = document.getElementById('add-img');
    const container = document.getElementById('main-img');
    let offsetX = 0;
    let offsetY = 0;
    let isDragging = false;

    addImg.addEventListener('click', dragStart);
    document.addEventListener('mousemove', drag);
    document.addEventListener("keypress", press);

    function press(e) {
        if (e.key === '+' && isDragging) {
            addImg.style.width = `${addImg.clientWidth + 10}px`;
        }
        if (e.key === '-' && isDragging) {
            addImg.style.width = `${addImg.clientWidth - 10}px`;
        }
    }
    function dragStart(e) {
        if (isDragging) {
            isDragging = false;
            addImg.style.cursor = 'grab';
            return;
        }
        
        isDragging = true;
        offsetX = e.clientX - addImg.offsetLeft;
        offsetY = e.clientY - addImg.offsetTop;
        addImg.style.cursor = 'grabbing';
    }

    function drag(e) {
        if (!isDragging) return;
        
        e.preventDefault();
        let x = e.clientX - offsetX;
        let y = e.clientY - offsetY;
        
        const containerRect = container.getBoundingClientRect();
        const imgRect = addImg.getBoundingClientRect();
        
        x = Math.max(containerRect.left, x);
        x = Math.min(containerRect.right - imgRect.width, x);
        
        y = Math.max(containerRect.top, y);
        y = Math.min(containerRect.bottom - imgRect.height, y);
        
        addImg.style.left = `${x}px`;
        addImg.style.top = `${y}px`;
        addImg.style.position = 'absolute';
    }
    
});