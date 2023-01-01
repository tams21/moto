app_ready().then(()=>{
    const driverRoleWrapper = document.querySelector('#driver-role');
    const inpRole = document.querySelector('#inp-role');

    inpRole.addEventListener('change', (e)=>{
        if(inpRole.value === 'driver') {
            driverRoleWrapper.classList.add('--opened');
        } else {
            driverRoleWrapper.classList.remove('--opened');
        }
    });
});
