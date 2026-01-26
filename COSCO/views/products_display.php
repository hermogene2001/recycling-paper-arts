<?php
// Fetch active products for display
$products_query = "SELECT * FROM products WHERE status = 'active' ORDER BY id DESC";
$products_result = mysqli_query($conn, $products_query);

// Count products
$product_count = mysqli_num_rows($products_result);
?>

<style>
    .products-section {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin: 30px 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .section-title {
        text-align: center;
        margin-bottom: 30px;
        color: #2d3748;
        font-size: 28px;
        font-weight: 700;
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
    }
    
    .product-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-bottom: 1px solid #e9ecef;
    }
    
    .product-info {
        padding: 25px;
    }
    
    .product-name {
        font-size: 20px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 15px;
    }
    
    .product-details {
        margin-bottom: 20px;
    }
    
    .detail-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f3f5;
    }
    
    .detail-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .detail-label {
        color: #718096;
        font-size: 14px;
    }
    
    .detail-value {
        font-weight: 600;
        color: #2d3748;
    }
    
    .buy-button {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .buy-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
    }
    
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: #718096;
    }
    
    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .section-title {
            font-size: 24px;
        }
    }
</style>

<div class="products-section">
    <h2 class="section-title">
        <i class="fas fa-newspaper me-2"></i>Available Paper Art Kits
    </h2>
    
    <?php if ($product_count > 0): ?>
        <div class="products-grid">
            <?php 
            mysqli_data_seek($products_result, 0);
            while($product = mysqli_fetch_assoc($products_result)):
                $total_return = $product['price'] + ($product['daily_earning'] * $product['cycle']);
                $roi_percentage = (($total_return - $product['price']) / $product['price']) * 100;
            ?>
                <div class="product-card">
                    <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image">
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        
                        <div class="product-details">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-dollar-sign me-1"></i>Price</span>
                                <span class="detail-value">$<?php echo number_format($product['price'], 2); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-percentage me-1"></i>Daily Return</span>
                                <span class="detail-value text-success">$<?php echo number_format($product['daily_earning'], 2); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-calendar-alt me-1"></i>Cycle</span>
                                <span class="detail-value"><?php echo $product['cycle']; ?> days</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-chart-line me-1"></i>Total Return</span>
                                <span class="detail-value text-primary">$<?php echo number_format($total_return, 2); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-trophy me-1"></i>ROI</span>
                                <span class="detail-value text-warning"><?php echo number_format($roi_percentage, 1); ?>%</span>
                            </div>
                        </div>
                        
                        <button class="buy-button" onclick="buyProduct(<?php echo $product['id']; ?>, <?php echo $product['price']; ?>)">
                            <i class="fas fa-shopping-cart me-2"></i>Get This Kit
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No Products Available</h3>
            <p>Check back later for new paper art kit releases!</p>
        </div>
    <?php endif; ?>
</div>