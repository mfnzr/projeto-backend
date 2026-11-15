// Adiciona e remove acompanhamentos do pedido com Ajax (sem recarregar a página)

const addButton = document.getElementById("add_side_dish");
const select = document.getElementById("side_dish_select");
const list = document.getElementById("side_dish_list");
const noSideDishes = document.getElementById("no_side_dishes");
const message = document.getElementById("side_dish_message");

async function sendRequest(url, formData) {
  const response = await fetch(url, {
    method: "POST",
    headers: { Accept: "application/json" },
    body: formData,
  });

  const data = await response.json();

  if (!response.ok) {
    message.textContent = data.error;
    return null;
  }

  message.textContent = "";
  return data;
}

async function addSideDish() {
  const formData = new FormData();
  formData.append("side_dish_id", select.value);

  const data = await sendRequest(addButton.dataset.url, formData);
  if (data === null) {
    return;
  }

  const sideDish = data.side_dish;

  const item = document.createElement("li");
  item.id = "side_dish_" + sideDish.id;
  item.className = "list-group-item d-flex justify-content-between align-items-center";

  const text = document.createElement("span");
  text.textContent = sideDish.name + " - " + sideDish.price;
  item.appendChild(text);

  const removeButton = document.createElement("button");
  removeButton.type = "button";
  removeButton.className = "btn btn-sm btn-outline-danger remove-side-dish";
  removeButton.dataset.url = addButton.dataset.url + "/" + sideDish.id;
  removeButton.innerHTML = '<i class="bi bi-trash"></i> Remover';
  removeButton.addEventListener("click", removeSideDish);
  item.appendChild(removeButton);

  list.appendChild(item);
  noSideDishes.classList.add("d-none");
}

async function removeSideDish(event) {
  const button = event.currentTarget;

  const formData = new FormData();
  formData.append("_method", "DELETE");

  const data = await sendRequest(button.dataset.url, formData);
  if (data === null) {
    return;
  }

  document.getElementById("side_dish_" + data.side_dish_id).remove();

  if (list.children.length === 0) {
    noSideDishes.classList.remove("d-none");
  }
}

if (addButton !== null) {
  addButton.addEventListener("click", addSideDish);
}

const removeButtons = document.querySelectorAll(".remove-side-dish");
for (const button of removeButtons) {
  button.addEventListener("click", removeSideDish);
}
