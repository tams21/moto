app_ready().then(()=>{
    const fuelList = document.querySelector('#fuel_list');
    const fuelInput = document.querySelector('#inp-fuel');
    const addFuelButton = document.querySelector('#addFuelButton');
    addFuelButton.addEventListener('click', (e)=>{
        const fuelId = fuelInput.value;
        if (fuelId === '') {
            return;
        }
        let alreadyAdded = false;
        fuelList.querySelectorAll('input').forEach((el)=>{
            if (el.value === fuelId) {
                alreadyAdded = true;
            }
        });
        if(alreadyAdded) {
            return;
        }
        const fuelName = fuelInput.selectedOptions[0].text;
        const newFuel = document.createElement('li');
        newFuel.innerHTML = `<span>${fuelName}</span>`
            +`<input type="hidden" name="fuel[]" value="${fuelId}">`
            +` <span class="material-symbols-sharp remove-item">close</span>`;
        fuelList.appendChild(newFuel);

        fuelInput.selectedIndex = 0;
    });

    fuelList.addEventListener('click', (e)=>{
        if (!e.target.closest('.remove-item')) {
            return;
        }

        const li = e.target.closest('li');
        li.remove();
    });
});
