function app_ready() {
    return new Promise((resolve, fail) => {
        if (document.readyState === 'complete') {
            setTimeout(() => {
                resolve();
            },0)
            return;
        }
        document.addEventListener('readystatechange', (event) => {
            if (event.target.readyState === 'complete') {
                app_ready().then(resolve);
            }
        });
    });
}

app_ready().then(()=>{
    const drawerToggle = document.querySelector('.app-drawer-toggle');
    const drawer = document.querySelector(drawerToggle.dataset.target);
    drawerToggle.addEventListener('click', (e)=>{
        drawer.classList.toggle('--opened');
    });

    document.addEventListener('click', (e)=>{
        if (e.target.closest('.app-drawer-surface')
            || e.target.closest('.app-drawer-toggle')
            || !drawer.classList.contains('--opened')) {
                return;
        }
        drawer.classList.remove('--opened');
    });

    setDrawerOnResize = function() {
        if (window.innerWidth > 900){
            if (drawer.classList.contains('app-drawer--fixed')) {return;}
            drawer.classList.remove('app-drawer--normal');
            drawer.classList.add('app-drawer--fixed');
            drawerToggle.style.display = 'none';
            return;
        }

        if(drawer.classList.contains('app-drawer--normal')) {return;}
        drawer.classList.remove('app-drawer--fixed');
        drawer.classList.add('app-drawer--normal');
        drawerToggle.style.display = 'block';

    }

    onResize = function() {
        setDrawerOnResize();
    }
    onResize();
    let resizeTimeout;
    window.addEventListener('resize', (e)=>{
        clearTimeout(resizeTimeout);
        setTimeout(onResize, 100);
    });
});
