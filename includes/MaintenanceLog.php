<?php
/**
 * MaintenanceLog Model
 * Handles maintenance log-related database operations
 */

require_once __DIR__ . '/../config/config.php';

class MaintenanceLog {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Create a new maintenance log
     */
    public function create($serverName, $maintenanceDate, $startTime, $endTime, $description, $maintenanceType, $status, $outcome, $performedBy) {
        try {
            $sql = "INSERT INTO maintenance_logs (server_name, maintenance_date, start_time, end_time, description, maintenance_type, status, outcome, performed_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$serverName, $maintenanceDate, $startTime, $endTime, $description, $maintenanceType, $status, $outcome, $performedBy]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Maintenance log created successfully', 'log_id' => $this->db->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to create maintenance log'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get maintenance log by ID
     */
    public function getById($id) {
        try {
            $sql = "SELECT ml.*, u.full_name as performed_by_name, u.username as performed_by_username 
                    FROM maintenance_logs ml 
                    LEFT JOIN users u ON ml.performed_by = u.id 
                    WHERE ml.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get all maintenance logs with optional filtering
     */
    public function getAll($filters = [], $limit = null, $offset = 0, $orderBy = 'maintenance_date', $orderDir = 'DESC') {
        try {
            $sql = "SELECT ml.*, u.full_name as performed_by_name, u.username as performed_by_username 
                    FROM maintenance_logs ml 
                    LEFT JOIN users u ON ml.performed_by = u.id";
            
            $whereConditions = [];
            $params = [];
            
            // Apply filters
            if (!empty($filters['server_name'])) {
                $whereConditions[] = "ml.server_name LIKE ?";
                $params[] = '%' . $filters['server_name'] . '%';
            }
            
            if (!empty($filters['status'])) {
                $whereConditions[] = "ml.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['maintenance_type'])) {
                $whereConditions[] = "ml.maintenance_type = ?";
                $params[] = $filters['maintenance_type'];
            }
            
            if (!empty($filters['date_from'])) {
                $whereConditions[] = "ml.maintenance_date >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereConditions[] = "ml.maintenance_date <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['performed_by'])) {
                $whereConditions[] = "ml.performed_by = ?";
                $params[] = $filters['performed_by'];
            }
            
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(" AND ", $whereConditions);
            }
            
            $sql .= " ORDER BY ml.$orderBy $orderDir";
            
            if ($limit) {
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Update maintenance log
     */
    public function update($id, $serverName, $maintenanceDate, $startTime, $endTime, $description, $maintenanceType, $status, $outcome) {
        try {
            $sql = "UPDATE maintenance_logs SET server_name = ?, maintenance_date = ?, start_time = ?, end_time = ?, description = ?, maintenance_type = ?, status = ?, outcome = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$serverName, $maintenanceDate, $startTime, $endTime, $description, $maintenanceType, $status, $outcome, $id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Maintenance log updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update maintenance log'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Delete maintenance log
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM maintenance_logs WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Maintenance log deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete maintenance log'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get total count with optional filtering
     */
    public function getTotalCount($filters = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM maintenance_logs ml";
            
            $whereConditions = [];
            $params = [];
            
            // Apply same filters as getAll method
            if (!empty($filters['server_name'])) {
                $whereConditions[] = "ml.server_name LIKE ?";
                $params[] = '%' . $filters['server_name'] . '%';
            }
            
            if (!empty($filters['status'])) {
                $whereConditions[] = "ml.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['maintenance_type'])) {
                $whereConditions[] = "ml.maintenance_type = ?";
                $params[] = $filters['maintenance_type'];
            }
            
            if (!empty($filters['date_from'])) {
                $whereConditions[] = "ml.maintenance_date >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereConditions[] = "ml.maintenance_date <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($filters['performed_by'])) {
                $whereConditions[] = "ml.performed_by = ?";
                $params[] = $filters['performed_by'];
            }
            
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(" AND ", $whereConditions);
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Get unique server names
     */
    public function getServerNames() {
        try {
            $sql = "SELECT DISTINCT server_name FROM maintenance_logs ORDER BY server_name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get maintenance statistics
     */
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total logs
            $sql = "SELECT COUNT(*) as total FROM maintenance_logs";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_logs'] = $stmt->fetch()['total'];
            
            // Logs by status
            $sql = "SELECT status, COUNT(*) as count FROM maintenance_logs GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_status'] = $stmt->fetchAll();
            
            // Logs by type
            $sql = "SELECT maintenance_type, COUNT(*) as count FROM maintenance_logs GROUP BY maintenance_type";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_type'] = $stmt->fetchAll();
            
            // Recent logs (last 30 days)
            $sql = "SELECT COUNT(*) as count FROM maintenance_logs WHERE maintenance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['recent_logs'] = $stmt->fetch()['count'];
            
            return $stats;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Search maintenance logs
     */
    public function search($searchTerm, $limit = null, $offset = 0) {
        try {
            $sql = "SELECT ml.*, u.full_name as performed_by_name, u.username as performed_by_username 
                    FROM maintenance_logs ml 
                    LEFT JOIN users u ON ml.performed_by = u.id 
                    WHERE ml.server_name LIKE ? 
                    OR ml.description LIKE ? 
                    OR ml.outcome LIKE ? 
                    OR u.full_name LIKE ?
                    ORDER BY ml.maintenance_date DESC";
            
            $searchParam = '%' . $searchTerm . '%';
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
            
            if ($limit) {
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>

