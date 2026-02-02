function editItem(id) {
  const _update_modal = this.document.getElementById("update_modal");
  const _update = this.document.getElementById("update");

  axios.get(`/api_get.php`, { params: { id } }).then(function (res) {
    const { status, data } = res;
    if (status == 200) {
      const { orta_suret, sethi_suret, tezlik, tarix } = data?.data;

      _update.orta_suret.value = orta_suret;
      _update.sethi_suret.value = sethi_suret;
      _update.tezlik.value = tezlik;

      _update_modal.classList.remove("hidden");

      _update.addEventListener("submit", function (e) {
        e.preventDefault();
        const sethi_suret = this.sethi_suret.value?.trim();
        const orta_suret = this.orta_suret.value?.trim();
        const tezlik = this.tezlik.value?.trim();

        axios
          .put(`/api_update.php/${id}`, { sethi_suret, orta_suret, tezlik })
          .then(function (res) {
            const { status, data } = res;
            if (status == 200) {
              message(data?.message);
              _update.reset();
              _update_modal.classList.add("hidden");

              const _tr = this.document.getElementById(id);

              _tr.innerHTML = `
              <td>${id}</td>
              <td>${sethi_suret} m/s</td>
              <td>${orta_suret} m/s</td>
              <td>${tezlik} Hz</td>
              <td>${tarix}</td>
              <td><button onclick="editItem(${id})">&#128221;</button></td>
              <td><button onclick="deleteItem(${id})">&#128465;</button></td>
              `;
            }
          });
      });
    }
  });
}

function deleteItem(itemId) {
  if (confirm("Elementi silmek isetyirsinizmi")) {
    const item = document.getElementById(itemId);
    const table = document.getElementById("table_body");

    axios.delete(`/api_delete.php/${itemId}`).then(({ status, data }) => {
      if (status == 200) {
        message(data?.message);
        table.removeChild(item);
      }
    });
  }
}

function message(msj, type = "success", time = 3) {
  const div = document.createElement("div");
  div.classList.add("alert");

  switch (type) {
    case "error": {
      div.classList.add("error");
      div.innerText = "✖ ";
      break;
    }
    case "warning": {
      div.classList.add("warning");
      div.innerText = "⚠ ";
      break;
    }
    case "info": {
      div.classList.add("info");
      div.innerText = "ℹ ";
      break;
    }
    default: {
      div.classList.add("success");
      div.innerText = "✔ ";
    }
  }

  div.innerText += msj;
  document.body.appendChild(div);

  setTimeout(() => {
    document.body.removeChild(div);
  }, time * 1000);
}

window.addEventListener("load", function () {
  const _table_body = this.document.getElementById("table_body");
  const _add_button = this.document.getElementById("add_button");
  const _add_modal = this.document.getElementById("add_modal");
  const _insert = this.document.getElementById("insert");

  getItems();

  _add_button.addEventListener("click", function () {
    _add_modal.classList.remove("hidden");
  });

  _insert.addEventListener("submit", function (e) {
    e.preventDefault();
    const sethi_suret = this.sethi_suret.value?.trim();
    const orta_suret = this.orta_suret.value?.trim();
    const tezlik = this.tezlik.value?.trim();

    axios
      .post(`/api_insert.php`, { sethi_suret, orta_suret, tezlik })
      .then(function (res) {
        const { status, data } = res;
        if (status == 201) {
          message(data?.message);
          _insert.reset();
          _add_modal.classList.add("hidden");
          getItems();
        }
      });
  });

  function getItems() {
    axios.get(`/api_get.php`).then(function (res) {
      const DATA = res?.data?.data || [];

      const dataUI = DATA.map(
        (d) =>
          ` <tr id="${d?.id}">
          <td>${d?.id}</td>
          <td>${d?.sethi_suret} m/s</td>
          <td>${d?.orta_suret} m/s</td>
          <td>${d?.tezlik} Hz</td>
          <td>${d?.tarix}</td>
          <td><button onclick="editItem(${d?.id})">&#128221;</button></td>
          <td><button onclick="deleteItem(${d?.id})">&#128465;</button></td>
        </tr>`,
      ).join("");
      _table_body.innerHTML = dataUI;
    });
  }
});
