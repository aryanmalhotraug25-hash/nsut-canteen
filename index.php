<?php
require_once 'auth.php';
include 'db_connect.php';

$user_id = require_login();
if (($_SESSION['role'] ?? '') === 'staff') {
    header('Location: dashboard.php');
    exit;
}

// Function to match menu item names with appropriate emojis
function get_food_emoji($name) {
    $name = strtolower($name);
    if (strpos($name, 'samosa') !== false || strpos($name, 'pattie') !== false) return '🥟';
    if (strpos($name, 'coffee') !== false) return '☕';
    if (strpos($name, 'tea') !== false || strpos($name, 'chai') !== false) return '🍵';
    if (strpos($name, 'burger') !== false) return '🍔';
    if (strpos($name, 'pizza') !== false) return '🍕';
    if (strpos($name, 'chole') !== false || strpos($name, 'bhature') !== false || strpos($name, 'rajma') !== false) return '🍛';
    if (strpos($name, 'sandwich') !== false) return '🥪';
    if (strpos($name, 'maggi') !== false || strpos($name, 'noodles') !== false || strpos($name, 'chowmein') !== false) return '🍝';
    if (strpos($name, 'roll') !== false || strpos($name, 'wrap') !== false) return '🌯';
    if (strpos($name, 'fries') !== false || strpos($name, 'potato') !== false) return '🍟';
    if (strpos($name, 'shake') !== false || strpos($name, 'juice') !== false || strpos($name, 'mojito') !== false) return '🥤';
    if (strpos($name, 'cake') !== false || strpos($name, 'pastry') !== false || strpos($name, 'brownie') !== false) return '🍰';
    if (strpos($name, 'ice cream') !== false) return '🍦';
    if (strpos($name, 'paratha') !== false) return '🫓';
    if (strpos($name, 'thali') !== false) return '🍱';
    return '🍲'; 
}

// New function to assign categories based on item names
function get_food_category($name) {
    $name = strtolower($name);
    if (strpos($name, 'coffee') !== false || strpos($name, 'tea') !== false || strpos($name, 'chai') !== false || strpos($name, 'shake') !== false || strpos($name, 'juice') !== false) return 'Beverages';
    if (strpos($name, 'cake') !== false || strpos($name, 'pastry') !== false || strpos($name, 'ice cream') !== false || strpos($name, 'brownie') !== false) return 'Desserts';
    if (strpos($name, 'chole') !== false || strpos($name, 'rajma') !== false || strpos($name, 'thali') !== false || strpos($name, 'paratha') !== false || strpos($name, 'bhature') !== false) return 'Meals';
    return 'Snacks'; // Default for burger, pizza, samosa, sandwich, fries, etc.
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSUT Canteen Pre-Order</title>
    <link rel="stylesheet" href="style.css?v=6">
</head>
<body>
    <form action="cart.php" method="POST" id="order-form">
        <div class="page-shell">
            
            <!-- LEFT SIDEBAR -->
            <aside class="sidebar">
                <a href="index.php" class="brand-mark">♨</a>
                <div class="brand-small">NSUT<br><span>CANTEEN</span></div>
                
                <nav class="side-nav">
                    <a href="index.php" class="nav-link active"><span>🍽️</span> <b>Home</b></a>
                    <a href="my_orders.php" class="nav-link"><span>📋</span> <b>My Orders</b></a>
                    <?php if(($_SESSION['role'] ?? '') === 'staff'): ?>
                        <a href="dashboard.php" class="nav-link"><span>👨‍🍳</span> <b>Staff Orders</b></a>
                    <?php endif; ?>
                </nav>
                
                <div class="sidebar-bottom">
                    <div class="tiny-dot"></div> Systems online
                </div>
            </aside>

            <!-- MAIN CONTENT AREA -->
            <main class="main-area">
                
                <header class="topbar">
                    <div class="mobile-brand">
                        <div class="brand-mark mini">♨</div>
                        <b>NSUT Canteen</b>
                    </div>
                    
                    <div class="search-box">
                        <span>🔍</span>
                        <input type="text" placeholder="Search for food items...">
                    </div>
                    
                    <div class="profile">
                        <div class="bell">🔔<i></i></div>
                        <div class="avatar">👨‍🎓</div>
                        <div>
                            <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>
                            <small><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Student')); ?> • NSUT</small>
                        </div>
                        <a href="logout.php" style="margin-left: 10px; font-size: 11px; font-weight: bold; color: #e86f00; text-decoration: none;">Logout</a>
                    </div>
                </header>

                <div class="content-grid">
                    
                    <!-- MIDDLE COLUMN: MENU -->
                    <div class="menu-column">
                        <div class="hero">
                            <div class="hero-copy">
                                <div class="eyebrow">YOUR CAMPUS, YOUR CANTEEN</div>
                                <h1>Good food,<br>good mood <span>😋</span></h1>
                                <p>Beat the lecture break hunger.<br>Order your favourites in a few clicks.</p>
                                <a href="#menu-grid" class="primary-btn">Explore the menu →</a>
                            </div>
                            <div class="hero-art">
                                <div class="sun-disc"></div>
                                <div class="plate"><span>🍛</span></div>
                                <div class="art-spark spark-one">✨</div>
                                <div class="art-spark spark-two">✨</div>
                                <div class="art-note">FRESH & TASTY</div>
                            </div>
                        </div>

                        <div class="section-heading category-heading">
                            <div>
                                <div class="eyebrow">WHAT ARE YOU CRAVING?</div>
                                <h2>Food categories</h2>
                            </div>
                            <div class="subtle-text">Pick your mood ✨</div>
                        </div>
                        <!-- CATEGORY BUTTONS -->
                        <div class="category-row">
                            <div class="category-card selected" style="cursor: pointer;"><span>🍱</span><b>All</b></div>
                            <div class="category-card" style="cursor: pointer;"><span>🍟</span><b>Snacks</b></div>
                            <div class="category-card" style="cursor: pointer;"><span>☕</span><b>Beverages</b></div>
                            <div class="category-card" style="cursor: pointer;"><span>🍛</span><b>Meals</b></div>
                            <div class="category-card" style="cursor: pointer;"><span>🍰</span><b>Desserts</b></div>
                        </div>

                        <div class="section-heading menu-heading" id="menu-grid">
                            <h2>Today's Menu <span class="smile">😊</span></h2>
                            <div class="menu-count">Freshly prepared</div>
                        </div>

                        <div class="food-grid">
                            <?php
                            $sql = "SELECT item_id, item_name, price FROM Menu_Items WHERE is_available = 1";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $item_name = htmlspecialchars($row['item_name']);
                                    $item_price = htmlspecialchars($row['price']);
                                    $emoji = get_food_emoji($item_name);
                                    $category = get_food_category($item_name); // Get category
                                    
                                    // Notice data-category added here
                                    ?>
                                    <div class="food-card" data-id="<?php echo $row['item_id']; ?>" data-name="<?php echo $item_name; ?>" data-price="<?php echo $item_price; ?>" data-emoji="<?php echo $emoji; ?>" data-category="<?php echo $category; ?>">
                                        <div class="food-image">
                                            <div class="fresh-tag">Fresh</div>
                                            <div class="food-emoji"><?php echo $emoji; ?></div>
                                        </div>
                                        <div class="food-info">
                                            <div class="food-title-line">
                                                <h3><?php echo $item_name; ?></h3>
                                                <div class="rating">⭐⭐⭐⭐⭐</div>
                                            </div>
                                            <p>Delicious & hot</p>
                                            
                                            <div class="food-bottom">
                                                <strong class="price">₹<?php echo number_format($item_price, 2); ?></strong>
                                                
                                                <div class="quantity-control">
                                                    <button type="button" class="qty-btn" onclick="updateQuantity(this, -1)">-</button>
                                                    <input type="number" class="qty-input" name="items[<?php echo $row['item_id']; ?>]" value="0" min="0" max="10" readonly>
                                                    <button type="button" class="qty-btn" onclick="updateQuantity(this, 1)">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                echo "<div class='empty-state'><span>🍽️</span><h3>No items currently available.</h3></div>";
                            }
                            $conn->close();
                            ?>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: LIVE CART -->
                    <div class="right-column">
                        <div class="cart-card">
                            <div class="cart-head">
                                <div>
                                    <span class="cart-icon">🛒</span>
                                    <div>
                                        <h2>Your cart</h2>
                                        <small id="cart-count">0 items selected</small>
                                    </div>
                                </div>
                                <button type="button" class="clear-cart" onclick="clearCart()">Clear all</button>
                            </div>
                            
                            <div class="cart-items" id="cart-items-container">
                                <div class="cart-empty" id="empty-cart-msg">
                                    <span>🛍️</span>
                                    <strong>Your cart is waiting</strong>
                                    <p>Add something delicious from the menu.</p>
                                </div>
                            </div>
                            
                            <div class="cart-summary">
                                <div><span>Subtotal</span> <strong id="cart-subtotal">₹0.00</strong></div>
                                <div><span>Convenience fee</span> <strong class="fee">₹0.00</strong></div>
                                
                                <div class="total-line">
                                    <span>Total amount</span>
                                    <strong id="cart-total">₹0.00</strong>
                                </div>
                                
                                <button type="submit" class="checkout-btn" id="checkout-btn" disabled style="opacity: 0.5; cursor: not-allowed;">Place your order ➔</button>
                                <div class="cart-note">⚡ Freshly prepared on campus</div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>

        <!-- MOBILE STICKY CHECKOUT -->
        <div class="mobile-checkout">
            <div>
                <span>Cart Total</span>
                <strong id="mobile-cart-total">₹0.00</strong>
            </div>
            <button type="submit" class="primary-btn" id="mobile-checkout-btn" disabled>Checkout ➔</button>
        </div>
    </form>

    <!-- JavaScript block handling everything -->
    <script>
        // 1. Search Bar Filtering
        document.querySelector('.search-box input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const foodCards = document.querySelectorAll('.food-card');
            
            // Reset categories to 'All' when searching
            document.querySelectorAll('.category-card').forEach(c => c.classList.remove('selected'));
            document.querySelector('.category-card').classList.add('selected');

            foodCards.forEach(card => {
                const foodName = card.getAttribute('data-name').toLowerCase();
                if (foodName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // 2. Category Filtering
        const categoryCards = document.querySelectorAll('.category-card');
        categoryCards.forEach(card => {
            card.addEventListener('click', function() {
                // Change visually selected card
                categoryCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');

                const selectedCategory = this.querySelector('b').innerText;
                const foodCards = document.querySelectorAll('.food-card');

                foodCards.forEach(food => {
                    const foodCategory = food.getAttribute('data-category');
                    // Show if 'All' is clicked, OR if the category matches
                    if (selectedCategory === 'All' || foodCategory === selectedCategory) {
                        food.style.display = 'block';
                    } else {
                        food.style.display = 'none';
                    }
                });
                
                // Clear the search box when clicking a category to prevent double-filtering conflicts
                document.querySelector('.search-box input').value = '';
            });
        });

        // 3. Cart Quantity Controls
        function updateQuantity(btn, change) {
            const input = change === 1 ? btn.previousElementSibling : btn.nextElementSibling;
            let currentVal = parseInt(input.value) || 0;
            let newVal = currentVal + change;
            
            if (newVal >= 0 && newVal <= 10) {
                input.value = newVal;
                renderCart();
            }
        }

        function clearCart() {
            document.querySelectorAll('.qty-input').forEach(input => {
                input.value = 0;
            });
            renderCart();
        }

        // 4. Live Cart Rendering
        function renderCart() {
            const container = document.getElementById('cart-items-container');
            const emptyMsg = document.getElementById('empty-cart-msg');
            const foodCards = document.querySelectorAll('.food-card');
            
            let cartHTML = '';
            let totalAmount = 0;
            let totalItems = 0;

            foodCards.forEach(card => {
                const qty = parseInt(card.querySelector('.qty-input').value) || 0;
                
                if (qty > 0) {
                    const name = card.getAttribute('data-name');
                    const price = parseFloat(card.getAttribute('data-price'));
                    const emoji = card.getAttribute('data-emoji');
                    const rowTotal = price * qty;
                    
                    totalAmount += rowTotal;
                    totalItems += qty;
                    
                    cartHTML += `
                        <div class="cart-row">
                            <div class="cart-thumb">${emoji}</div>
                            <div class="cart-row-info">
                                <strong>${name}</strong>
                                <small>₹${price.toFixed(2)} x ${qty}</small>
                            </div>
                            <strong class="cart-row-price">₹${rowTotal.toFixed(2)}</strong>
                        </div>
                    `;
                }
            });

            if (totalItems > 0) {
                container.innerHTML = cartHTML;
                document.getElementById('checkout-btn').disabled = false;
                document.getElementById('checkout-btn').style.opacity = '1';
                document.getElementById('checkout-btn').style.cursor = 'pointer';
                document.getElementById('mobile-checkout-btn').disabled = false;
            } else {
                container.innerHTML = `
                    <div class="cart-empty" id="empty-cart-msg">
                        <span>🛍️</span>
                        <strong>Your cart is waiting</strong>
                        <p>Add something delicious from the menu.</p>
                    </div>
                `;
                document.getElementById('checkout-btn').disabled = true;
                document.getElementById('checkout-btn').style.opacity = '0.5';
                document.getElementById('checkout-btn').style.cursor = 'not-allowed';
                document.getElementById('mobile-checkout-btn').disabled = true;
            }

            document.getElementById('cart-count').innerText = `${totalItems} items selected`;
            document.getElementById('cart-subtotal').innerText = `₹${totalAmount.toFixed(2)}`;
            document.getElementById('cart-total').innerText = `₹${totalAmount.toFixed(2)}`;
            document.getElementById('mobile-cart-total').innerText = `₹${totalAmount.toFixed(2)}`;
        }
    </script>
</body>
</html>