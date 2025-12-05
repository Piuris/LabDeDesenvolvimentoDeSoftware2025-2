
function getApiPath(endpoint) {
  const currentPath = window.location.pathname
  const basePath = currentPath.substring(0, currentPath.lastIndexOf('/') + 1)
  return basePath + 'api/' + endpoint
}

/**
 * Adiciona uma mentoria ao carrinho
 * @param {number} mentoriaId - ID da mentoria a ser adicionada
 * @param {HTMLElement} button - Elemento do botão que foi clicado
 */
function addToCart(mentoriaId, button) {
  if (!mentoriaId || !button) {
    showNotification("❌ Erro: parâmetros inválidos", "error");
    return;
  }

  const originalText = button.textContent;
  button.disabled = true;
  button.textContent = "Adicionando...";

  const requestData = {
    mentoria_id: parseInt(mentoriaId, 10)
  };

  fetch(getApiPath('carrinho-add.php'), {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(requestData),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Erro HTTP: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        showNotification("✅ " + (data.message || "Adicionado ao carrinho!"), "success");
        updateCartCount();
        button.textContent = "✓ Adicionado";
        
        setTimeout(() => {
          button.textContent = originalText;
          button.disabled = false;
        }, 2000);
      } else {
        showNotification("❌ " + (data.message || "Erro ao adicionar ao carrinho"), "error");
        button.textContent = originalText;
        button.disabled = false;
      }
    })
    .catch((error) => {
      console.error("Erro ao adicionar ao carrinho:", error);
      showNotification("❌ Erro ao conectar com o servidor. Tente novamente.", "error");
      button.textContent = originalText;
      button.disabled = false;
    });
}

window.addToCart = addToCart;

function removeFromCart(mentoriaId, element) {
  if (!confirm("Deseja remover este item do carrinho?")) {
    return
  }

  fetch(getApiPath('carrinho-remove.php'), {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ mentoria_id: mentoriaId }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        element.closest(".cart-item").remove()
        updateCartCount()
        location.reload()
      } else {
        showNotification("❌ " + data.message, "error")
      }
    })
    .catch((error) => {
      console.error("Erro:", error)
      showNotification("❌ Erro ao remover do carrinho", "error")
    })
}

function showNotification(message, type = "info") {
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.textContent = message
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === "success" ? "#10b981" : "#ef4444"};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `

  document.body.appendChild(notification)

  setTimeout(() => {
    notification.style.animation = "slideOut 0.3s ease-in"
    setTimeout(() => notification.remove(), 300)
  }, 3000)
}

function updateCartCount() {
  fetch(getApiPath('carrinho-count.php'))
    .then((response) => response.json())
    .then((data) => {
      const badge = document.getElementById("cart-count")
      if (badge && data.count !== undefined) {
        badge.textContent = data.count
        badge.style.display = data.count > 0 ? "flex" : "none"
      }
    })
    .catch((error) => console.error("Erro ao atualizar carrinho:", error))
}

/**
 * Inicializa os event listeners para os botões de adicionar ao carrinho
 */
function initAddToCartListeners() {
  const buttons = document.querySelectorAll('.js-add-to-cart');

  buttons.forEach(button => {
    const newButton = button.cloneNode(true);
    button.parentNode.replaceChild(newButton, button);
    
    newButton.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const mentoriaId = parseInt(this.getAttribute('data-mentoria-id'), 10);
      
      if (!mentoriaId || isNaN(mentoriaId)) {
        showNotification("❌ Erro: ID da mentoria inválido", "error");
        return;
      }
      
      addToCart(mentoriaId, this);
    });
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initMobileMenu()
  initFormValidation()
  updateCartCount()
  initAjaxSearch()
  initAddToCartListeners()
})

function initMobileMenu() {
  const toggle = document.querySelector(".mobile-menu-toggle")
  const menu = document.querySelector(".nav-menu")

  if (toggle) {
    toggle.addEventListener("click", () => {
      menu.classList.toggle("active")
    })
  }
}

function initFormValidation() {
  const forms = document.querySelectorAll("form[data-validate]")

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!validateForm(this)) {
        e.preventDefault()
      }
    })

    const inputs = form.querySelectorAll(".form-control")
    inputs.forEach((input) => {
      input.addEventListener("blur", function () {
        validateField(this)
      })
    })
  })
}

function validateForm(form) {
  let isValid = true
  const inputs = form.querySelectorAll(".form-control[required]")

  inputs.forEach((input) => {
    if (!validateField(input)) {
      isValid = false
    }
  })

  return isValid
}

function validateField(field) {
  const value = field.value.trim()
  const type = field.type
  const name = field.name
  let isValid = true
  let errorMsg = ""

  if (field.hasAttribute("required") && value === "") {
    isValid = false
    errorMsg = "Este campo é obrigatório"
  }

  else if (type === "email" && value !== "") {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailRegex.test(value)) {
      isValid = false
      errorMsg = "Email inválido"
    }
  }

  else if (name === "senha" && value !== "" && value.length < 6) {
    isValid = false
    errorMsg = "A senha deve ter no mínimo 6 caracteres"
  }

  else if (name === "confirmar_senha" && value !== "") {
    const senha = document.querySelector('input[name="senha"]').value
    if (value !== senha) {
      isValid = false
      errorMsg = "As senhas não coincidem"
    }
  }

  else if (name === "telefone" && value !== "") {
    const telefoneRegex = /^$$?[0-9]{2}$$?[\s-]?[0-9]{4,5}[\s-]?[0-9]{4}$/
    if (!telefoneRegex.test(value)) {
      isValid = false
      errorMsg = "Telefone inválido"
    }
  }

  const errorElement = field.nextElementSibling
  if (errorElement && errorElement.classList.contains("error-message")) {
    if (!isValid) {
      field.classList.add("error")
      errorElement.textContent = errorMsg
      errorElement.classList.add("show")
    } else {
      field.classList.remove("error")
      errorElement.classList.remove("show")
    }
  }

  return isValid
}

function initAjaxSearch() {
  const searchForm = document.getElementById("ajax-search-form")
  const resultsContainer = document.getElementById("search-results")

  if (searchForm && resultsContainer) {
    searchForm.addEventListener("submit", function (e) {
      e.preventDefault()

      const formData = new FormData(this)
      resultsContainer.innerHTML = '<div class="spinner"></div>'

      fetch(getApiPath('mentoria-search.php'), {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          displaySearchResults(data, resultsContainer)
        })
        .catch((error) => {
          console.error("Erro:", error)
          resultsContainer.innerHTML = "<p>Erro ao buscar mentorias.</p>"
        })
    })

    const searchInput = searchForm.querySelector('input[name="busca"]')
    let searchTimeout

    searchInput.addEventListener("input", function () {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        if (this.value.length >= 3) {
          searchForm.dispatchEvent(new Event("submit"))
        }
      }, 500)
    })
  }
}

function displaySearchResults(data, container) {
  if (!data.success || data.mentorias.length === 0) {
    container.innerHTML = '<p class="no-results">Nenhuma mentoria encontrada.</p>'
    return
  }

  let html = '<div class="cards-grid">'

  data.mentorias.forEach((mentoria) => {
    html += `
            <div class="card">
                <div class="card-image">${mentoria.categoria_icone || "📚"}</div>
                <div class="card-content">
                    <h3 class="card-title">${mentoria.titulo}</h3>
                    <p class="card-description">${mentoria.descricao.substring(0, 100)}...</p>
                    <div class="card-meta">
                        <span class="card-price">R$ ${Number.parseFloat(mentoria.preco).toFixed(2)}</span>
                        <a href="mentoria-detalhes.php?id=${mentoria.id}" class="btn btn-primary">Ver Detalhes</a>
                    </div>
                </div>
            </div>
        `
  })

  html += "</div>"
  container.innerHTML = html
}

const style = document.createElement("style")
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`
document.head.appendChild(style)
