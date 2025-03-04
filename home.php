<?php
// Include necessary files
require_once 'db.php';
require_once 'dashboard-data.php';

// Initialize dashboard data
$dashboard = new DashboardData($db);
$data = $dashboard->getAllDashboardData();

// Format currency function
function formatCurrency($amount) {
    return '$' . number_format((float)$amount, 2, '.', ',');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Widget</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .widgets {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .widget {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px;
        }
        .widget-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .widget-title {
            font-weight: bold;
            font-size: 18px;
        }
        .widget-content {
            min-height: 100px;
        }
        .bg-orange {
            background-color: rgb(255, 153, 0);
            color: #fff;
        }
        .bg-purple {
            background-color: rgb(144, 19, 254);
            color: #fff;
        }
        .stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.8;
        }
        .chart-placeholder {
            background: #f5f5f5;
            border-radius: 4px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
        }
        .recent-list {
            list-style: none;
            padding: 0;
        }
        .recent-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .recent-item:last-child {
            border-bottom: none;
        }
        .date-info {
            color: #777;
            font-size: 12px;
        }
        .warning {
            color: #f44336;
            font-weight: bold;
        }
    </style>
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    
    <nav class="nav">
        <h1>POS Dashboard</h1>
        <ul class="flex">
            <li>Admin User</li>
            <li>
                <a class="logout" href="logout.php">Logout</a>
            </li>
        </ul>
    </nav>

    <div class="dashboard">
        <!-- Stats Overview -->
        <ul class="flex">
            <li class="card bg-green">
                <div class="stat">
                    <div class="stat-value"><?php echo $data['total_products']; ?></div>
                    <div class="stat-label">Products</div>
                </div>
            </li>
            <li class="card bg-blue">
                <div class="stat">
                    <div class="stat-value"><?php echo $data['total_users']; ?></div>
                    <div class="stat-label">Users</div>
                </div>
            </li>
            <li class="card bg-orange">
                <div class="stat">
                    <div class="stat-value"><?php echo formatCurrency($data['month_sales']); ?></div>
                    <div class="stat-label">Sales This Month</div>
                </div>
            </li>
            <li class="card bg-purple">
                <div class="stat">
                    <div class="stat-value"><?php echo $data['month_orders']; ?></div>
                    <div class="stat-label">Orders</div>
                </div>
            </li>
        </ul>

        <div class="widgets">
            <!-- Chart Widget -->
            <div class="widget">
                <div class="widget-header">
                    <div class="widget-title">Sales Overview</div>
                    <div>Last 30 days</div>
                </div>
                <div class="widget-content">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Recent Orders Widget -->
            <div class="widget">
                <div class="widget-header">
                    <div class="widget-title">Recent Orders</div>
                    <div><a href="orders.php">View All</a></div>
                </div>
                <div class="widget-content">
                    <ul class="recent-list">
                        <?php if (empty($data['recent_orders'])): ?>
                            <li class="recent-item">No recent orders found</li>
                        <?php else: ?>
                            <?php foreach ($data['recent_orders'] as $order): ?>
                                <li class="recent-item">
                                    Order #<?php echo htmlspecialchars($order['order_number']); ?> - 
                                    <?php echo formatCurrency($order['total_amount']); ?>
                                    <div class="date-info">
                                        <?php 
                                        echo htmlspecialchars($order['customer_name'] ?: 'Guest'); 
                                        echo ' - ' . date('M d, Y', strtotime($order['created_at']));
                                        ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Top Products Widget -->
            <div class="widget">
                <div class="widget-header">
                    <div class="widget-title">Top Products</div>
                    <div>This Month</div>
                </div>
                <div class="widget-content">
                    <ul class="recent-list">
                        <?php if (empty($data['top_selling_products'])): ?>
                            <li class="recent-item">No data available</li>
                        <?php else: ?>
                            <?php foreach ($data['top_selling_products'] as $product): ?>
                                <li class="recent-item">
                                    <?php echo htmlspecialchars($product['name']); ?> - 
                                    <?php echo $product['total_sold']; ?> units
                                    <div class="date-info">
                                        Revenue: <?php echo formatCurrency($product['total_revenue']); ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Inventory Status Widget -->
            <div class="widget">
                <div class="widget-header">
                    <div class="widget-title">Inventory Status</div>
                    <div>Low Stock Items</div>
                </div>
                <div class="widget-content">
                    <ul class="recent-list">
                        <?php if (empty($data['low_stock_products'])): ?>
                            <li class="recent-item">No low stock items</li>
                        <?php else: ?>
                            <?php foreach ($data['low_stock_products'] as $product): ?>
                                <li class="recent-item">
                                    <?php echo htmlspecialchars($product['name']); ?> - 
                                    <span class="<?php echo ($product['stock_quantity'] <= 5) ? 'warning' : ''; ?>">
                                        <?php echo $product['stock_quantity']; ?> units left
                                    </span>
                                    <div class="date-info">
                                        Threshold: <?php echo $product['low_stock_threshold']; ?> units
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Category Distribution Widget -->
            <div class="widget">
                <div class="widget-header">
                    <div class="widget-title">Product Categories</div>
                    <div>Distribution</div>
                </div>
                <div class="widget-content">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
            
            <!-- Payment Methods Widget -->
            <div class="widget">
                <div class="widget-header">
                    <div class="widget-title">Payment Methods</div>
                    <div>Last 30 days</div>
                </div>
                <div class="widget-content">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div style="margin: 20px; text-align: center;">
        <a href="index.php">Back to Home</a>
    </div>
    
    <script>
        // Sales chart data
        const salesData = <?php echo json_encode(array_reverse($data['daily_sales'])); ?>;
        
        // Format dates and extract data for chart
        const dates = salesData.map(item => new Date(item.sale_date).toLocaleDateString());
        const sales = salesData.map(item => item.total_sales);
        
        // Create sales chart
        const salesChart = new Chart(
            document.getElementById('salesChart'),
            {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Daily Sales',
                        data: sales,
                        fill: false,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            }
        );
        
        // Category distribution data
        const categoryData = <?php echo json_encode($data['category_distribution']); ?>;
        
        // Create category chart
        const categoryChart = new Chart(
            document.getElementById('categoryChart'),
            {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(item => item.name),
                    datasets: [{
                        label: 'Products',
                        data: categoryData.map(item => item.count),
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 206, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true
                }
            }
        );
        
        // Payment methods data
        const paymentData = <?php echo json_encode($data['payment_methods']); ?>;
        
        // Format payment method labels
        const formatPaymentMethod = (method) => {
            return method.split('_').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');
        };
        
        // Create payment methods chart
        const paymentChart = new Chart(
            document.getElementById('paymentChart'),
            {
                type: 'pie',
                data: {
                    labels: paymentData.map(item => formatPaymentMethod(item.payment_method)),
                    datasets: [{
                        label: 'Orders',
                        data: paymentData.map(item => item.count),
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 206, 86)',
                            'rgb(75, 192, 192)'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true
                }
            }
        );
    </script>
</body>
</html>