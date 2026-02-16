async function loadFavs(){
  const out = document.getElementById('favList');
  try {
    const res = await apiGet('/api/favorites/list.php');
    const favs = res.favorites || [];

    if (!favs.length) {
      out.textContent = 'No favorites yet.';
      return;
    }

    out.innerHTML = `
      <table class="table">
        <thead><tr><th>Company</th><th>Car</th><th>Price</th><th></th></tr></thead>
        <tbody>
          ${favs.map(c => `
            <tr>
              <td>${escapeHtml(c.company_name)}</td>
              <td><a href="car.php?id=${c.car_id}">${escapeHtml(c.car_name)}</a></td>
              <td>$${Number(c.price).toLocaleString()}</td>
              <td><button onclick="removeFav(${c.car_id})">Remove</button></td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    `;
  } catch(e){
    out.textContent = e.message;
  }
}

async function removeFav(carId){
  try {
    await apiPost('/api/favorites/delete.php', { car_id: carId });
    loadFavs();
  } catch(e){
    alert(e.message);
  }
}

loadFavs();

