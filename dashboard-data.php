<?php
// Include database connection
require_once 'db.php';

/**
 * Dashboard data functions
 */
class DashboardData {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get total number of products
     */
    public function getTotalProducts() {
        $sql = "SELECT COUNT(*) as total FROM products WHERE is_active = TRUE";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
     * Get total number of users
     */
    public function getTotalUsers() {
        $sql = "SELECT COUNT(*) as total FROM users WHERE is_active = TRUE";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
     * Get total number of customers
     */
    public function getTotalCustomers() {
        $sql = "SELECT COUNT(*) as total FROM customers";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
     * Get total sales amount for current month
     */
    public function getMonthSales() {
        $sql = "SELECT SUM(total_amount) as total FROM orders 
                WHERE status = 'completed' 
                AND MONTH(created_at) = MONTH(CURRENT_DATE())
                AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Get total number of orders for current month
     */
    public function getMonthOrders() {
        $sql = "SELECT COUNT(*) as total FROM orders 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
                AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts($limit = 5) {
        $sql = "SELECT * FROM low_stock_products LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }

    /**
     * Get top selling products
     */
    public function getTopSellingProducts($limit = 5) {
        $sql = "SELECT * FROM top_selling_products LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders($limit = 5) {
        $sql = "SELECT o.id, o.order_number, o.total_amount, o.created_at, 
                CONCAT(c.first_name, ' ', c.last_name) as customer_name
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.id
                ORDER BY o.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        return $orders;
    }

    /**
     * Get daily sales data for chart (last 30 days)
     */
    public function getDailySalesData() {
        $sql = "SELECT * FROM daily_sales LIMIT 30";
        $result = $this->db->query($sql);
        
        $salesData = [];
        while ($row = $result->fetch_assoc()) {
            $salesData[] = $row;
        }
        
        return $salesData;
    }

    /**
     * Get payment methods distribution
     */
    public function getPaymentMethodsDistribution() {
        $sql = "SELECT payment_method, COUNT(*) as count 
                FROM orders 
                WHERE status = 'completed' 
                AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
                GROUP BY payment_method";
        
        $result = $this->db->query($sql);
        
        $paymentData = [];
        while ($row = $result->fetch_assoc()) {
            $paymentData[] = $row;
        }
        
        return $paymentData;
    }

    /**
     * Get category distribution for products
     */
    public function getCategoryDistribution() {
        $sql = "SELECT c.name, COUNT(p.id) as count 
                FROM categories c
                JOIN products p ON c.id = p.category_id
                WHERE p.is_active = TRUE
                GROUP BY c.id, c.name";
        
        $result = $this->db->query($sql);
        
        $categoryData = [];
        while ($row = $result->fetch_assoc()) {
            $categoryData[] = $row;
        }
        
        return $categoryData;
    }

    /**
     * Get all dashboard data in one call
     */
    public function getAllDashboardData() {
        return [
            'total_products' => $this->getTotalProducts(),
            'total_users' => $this->getTotalUsers(),
            'total_customers' => $this->getTotalCustomers(),
            'month_sales' => $this->getMonthSales(),
            'month_orders' => $this->getMonthOrders(),
            'low_stock_products' => $this->getLowStockProducts(),
            'top_selling_products' => $this->getTopSellingProducts(),
            'recent_orders' => $this->getRecentOrders(),
            'daily_sales' => $this->getDailySalesData(),
            'payment_methods' => $this->getPaymentMethodsDistribution(),
            'category_distribution' => $this->getCategoryDistribution()
        ];
    }
}

// Example usage
// $dashboard = new DashboardData($db);
// $data = $dashboard->getAllDashboardData();
?>