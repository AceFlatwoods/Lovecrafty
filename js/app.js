// Only run these if the forms exist on the page
document.addEventListener('DOMContentLoaded', function() {
  // Register Form
  if (document.getElementById('registerForm')) {
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
      e.preventDefault();
    
      const username = document.getElementById('username').value;
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
      const messageElement = document.getElementById('registerMessage');

      try {
        const response = await fetch('chocolaterie-backend/api/users/register.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, email, password }),
        });

        const result = await response.json();
        messageElement.textContent = result.message || result.error;
        messageElement.style.color = response.ok ? 'green' : '#d9534f';

        if (response.ok) {
          document.getElementById('registerForm').reset();
          setTimeout(() => switchTab('login'), 1500);
        }
      } catch (error) {
        messageElement.textContent = 'Network error. Please try again.';
        messageElement.style.color = '#d9534f';
      }
    });
  }

//--------------------------------------------------------------------------------------------------------Register form submission, these are the names from the code above the comment btw

  // Login Form
  if (document.getElementById('loginForm')) {
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      const email = document.getElementById('loginEmail').value;
      const password = document.getElementById('loginPassword').value;
      const messageElement = document.getElementById('loginMessage');

      try {
        const response = await fetch('chocolaterie-backend/api/users/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password }),
        });

        const result = await response.json();
        console.log('Login response:', result);
        messageElement.textContent = result.message || result.error;
        messageElement.style.color = response.ok ? 'green' : '#d9534f';

        if (response.ok) {
          if (result.user && result.user.id_user) {
            localStorage.setItem('userId', result.user.id_user);
            console.log('Login successful, userId:', result.user.id_user);
            setTimeout(() => window.location.href = 'index.php', 500);
          } else {
            console.error('User ID not found in response:', result);
          }
        }
      } catch (error) {
        messageElement.textContent = 'Network error. Please try again.';
        messageElement.style.color = '#d9534f';
      }
    });
  }
});

//-------------------------------------------------------------------------------------------------------- Login form submission

async function addToCart(productId) {
  console.log('addToCart called with productId:', productId);
  const userId = localStorage.getItem('userId');
  console.log('userId:', userId);
  if (!userId) {
    alert('Please log in to add items to your cart.');
    return;
  }

  const response = await fetch('chocolaterie-backend/api/cart/add.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ userId, productId, quantity: 1 }),
  });

  const result = await response.json();
  alert(result.message || result.error);
}
//--------------------------------------------------------------------------------------------------------Add to cart button

async function removeFromCart(cartId) {
  try {
    const response = await fetch(`chocolaterie-backend/api/cart/remove.php/${cartId}`, {
      method: 'DELETE',
    });
    
    const result = await response.json();
    if (response.ok) {
      displayCart(); // Refresh the cart display
    } else {
      alert(result.error || 'Failed to remove item');
    }
  } catch (error) {
    console.error('Error removing item:', error);
    alert('Error removing item. Please try again.');
  }
}
//--------------------------------------------------------------------------------------------------------Delete from cart button

async function searchProducts() {
  const query = document.getElementById('searchQuery').value;
  const response = await fetch(`chocolaterie-backend/api/products/search.php?query=${query}`);
  const result = await response.json();

  const searchResultsDiv = document.getElementById('searchResults');
  searchResultsDiv.innerHTML = result.products.map(product => `
    <div class="product-card">
      <h3>${product.nombre}</h3>
      <p>$${product.precio}</p>
      <button onclick="addToCart(${product.id_producto})">Add to Cart</button>
    </div>
  `).join('');
}
//--------------------------------------------------------------------------------------------------------Search products

async function displayCart() {
  const userId = localStorage.getItem('userId');
  if (!userId) {
    document.getElementById('cartItems').innerHTML = `
      <div class="empty-cart">
        <p>Please log in to view your cart</p>
        <a href="testing.html" class="btn">Login</a>
      </div>
    `;
    return;
  }

  try {
    const response = await fetch(`chocolaterie-backend/api/cart/view.php?userId=${userId}`);
    const result = await response.json();

    const cartItemsDiv = document.getElementById('cartItems');
    
    if (result.cart && result.cart.length > 0) {
      let subtotal = 0;
      
      cartItemsDiv.innerHTML = result.cart.map(item => {
        const itemTotal = item.precio * item.quantity;
        subtotal += itemTotal;
        
        return `
          <div class="cart-item">
            <img src="${item.image_url || 'img/default-product.png'}" alt="${item.nombre}">
            <div class="item-details">
              <h4>${item.nombre}</h4>
              <p>$${item.precio.toFixed(2)} × ${item.quantity}</p>
              <p class="item-total">$${itemTotal.toFixed(2)}</p>
              <button onclick="removeFromCart(${item.id_cart})" class="remove-btn">Remove</button>
            </div>
          </div>
        `;
      }).join('');

      // Update summary
      document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
      const shipping = 5.99;
      document.getElementById('total').textContent = `$${(subtotal + shipping).toFixed(2)}`;
      
      // Update cart count
      const totalItems = result.cart.reduce((sum, item) => sum + item.quantity, 0);
      document.getElementById('cart-count').textContent = totalItems;
    } else {
      cartItemsDiv.innerHTML = `
        <div class="empty-cart">
          <p>Your cart is empty</p>
          <a href="index.html" class="btn">Browse Products</a>
        </div>
      `;
      document.getElementById('cart-count').textContent = '0';
    }
  } catch (error) {
    console.error('Error loading cart:', error);
    document.getElementById('cartItems').innerHTML = `
      <div class="empty-cart">
        <p>Error loading your cart. Please try again.</p>
      </div>
    `;
  }
}

// Add this near your other cart functions
function setupIndexPage() {
  // Check if we're on the index page
  if (document.querySelector('.product-grid')) {
    // Add click handlers to all product buttons
    document.querySelectorAll('.product-card .btn').forEach(button => {
      button.addEventListener('click', function() {
        const productId = this.getAttribute('data-product-id');
        if (productId) {
          addToCart(parseInt(productId));
        }
      });
    });
  }
}

//--------------------------------------------------------------------------------------------------------Cart n stuff

// Initialize the right view based on page
document.addEventListener('DOMContentLoaded', function() {
  setupIndexPage();
  // For cart.html
  if (document.querySelector('.cart-items')) {
    displayCart();
  }
  // For testing.html
  else if (document.getElementById('cartItems')) {
    displayCart();
  }
});
//--------------------------------------------------------------------------------------------------------DOM content loader dunno what's that
// Add this to your existing app.js
async function handleLogin(email, password) {
  const btn = document.querySelector('#loginForm button[type="submit"]');
  const messageElement = document.getElementById('loginMessage');
  
  try {
    btn.classList.add('loading');
    btn.disabled = true;
    
    const response = await fetch('chocolaterie-backend/api/users/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    });

    const result = await response.json();
    messageElement.textContent = result.message || result.error;
    messageElement.style.color = response.ok ? 'green' : '#d9534f';

    if (response.ok) {
      localStorage.setItem('userId', result.user.id_user);
      window.location.href = 'index.html';
    }
  } catch (error) {
    messageElement.textContent = 'Network error. Please try again.';
    messageElement.style.color = '#d9534f';
  } finally {
    btn.classList.remove('loading');
    btn.disabled = false;
  }
}
//-------------------------------------------------------------------------------------------------------- Something for the login idk