document.addEventListener('DOMContentLoaded', () => {
    const userId = localStorage.getItem('userId');
    
    // Load cart items
    fetch(`chocolaterie-backend/api/cart/view.php?userId=${userId}`)
        .then(response => response.json())
        .then(data => {
            const orderItems = document.getElementById('order-items');
            let total = 0;
            
            data.cart.forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.className = 'order-item';
                itemElement.innerHTML = `
                    <p>${item.nombre} x ${item.quantity}</p>
                    <p>$${(item.precio * item.quantity).toFixed(2)}</p>
                `;
                orderItems.appendChild(itemElement);
                total += item.precio * item.quantity;
            });
            
            document.getElementById('order-total').innerHTML = `
                <div class="total-line">
                    <h3>Total:</h3>
                    <h3>$${total.toFixed(2)}</h3>
                </div>
            `;
        });

    // Handle form submission
    document.getElementById('checkout-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const orderData = {
            userId: userId,
            cardName: document.getElementById('card-name').value,
            cardNumber: document.getElementById('card-number').value,
            expiry: document.getElementById('expiry').value,
            cvv: document.getElementById('cvv').value,
            address: document.getElementById('address').value,
            city: document.getElementById('city').value,
            zip: document.getElementById('zip').value
        };
        
        // Submit order
        const response = await fetch('chocolaterie-backend/api/orders/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });
        
        const result = await response.json();
        if (response.ok) {
            alert('Order placed successfully!');
            window.location.href = 'index.html';
        } else {
            alert('Error: ' + (result.error || 'Failed to place order'));
        }
    });
});