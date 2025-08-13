<?php
/**
 * DG SPORTS - Database Class
 * Developer: DiziPortal.Com
 * Secure PDO database wrapper
 */

if (!defined('DG_SPORTS_APP')) {
    die('Direct access forbidden');
}

class Database {
    private static $instance = null;
    private $connection;
    private $config;
    
    private function __construct($config) {
        $this->config = $config;
        $this->connect();
    }
    
    public static function getInstance($config = []) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            $mysql_config = $this->config['connections']['mysql'];
            
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $mysql_config['host'],
                $mysql_config['port'],
                $mysql_config['database'],
                $mysql_config['charset']
            );
            
            $this->connection = new PDO(
                $dsn,
                $mysql_config['username'],
                $mysql_config['password'],
                $mysql_config['options']
            );
            
        } catch (PDOException $e) {
            log_activity('Database connection failed', 'error', ['error' => $e->getMessage()]);
            throw new Exception('Database connection failed');
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            log_activity('Database query failed', 'error', [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Database query failed');
        }
    }
    
    public function select($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function selectOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->connection->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function rowCount($stmt) {
        return $stmt->rowCount();
    }
    
    // Paginated results
    public function paginate($sql, $params = [], $page = 1, $perPage = 20) {
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as count_table";
        $totalRows = $this->selectOne($countSql, $params)['total'];
        
        // Calculate pagination
        $totalPages = ceil($totalRows / $perPage);
        $offset = ($page - 1) * $perPage;
        
        // Get paginated results
        $paginatedSql = $sql . " LIMIT {$perPage} OFFSET {$offset}";
        $results = $this->select($paginatedSql, $params);
        
        return [
            'data' => $results,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_rows' => $totalRows,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }
    
    // Check table exists
    public function tableExists($table) {
        $sql = "SHOW TABLES LIKE :table";
        $result = $this->selectOne($sql, ['table' => $table]);
        return !empty($result);
    }
    
    // Get table columns
    public function getTableColumns($table) {
        $sql = "DESCRIBE {$table}";
        return $this->select($sql);
    }
    
    // Execute raw SQL file
    public function executeSqlFile($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("SQL file not found: {$filePath}");
        }
        
        $sql = file_get_contents($filePath);
        $statements = array_filter(
            array_map('trim', explode(';', $sql)), 
            function($stmt) { return !empty($stmt); }
        );
        
        $this->beginTransaction();
        try {
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    $this->connection->exec($statement);
                }
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    // Database health check
    public function healthCheck() {
        try {
            $result = $this->selectOne("SELECT 1 as status");
            return $result['status'] == 1;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Backup database
    public function backup($tables = []) {
        $backup = '';
        $backup .= "-- DG SPORTS Database Backup\n";
        $backup .= "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";
        $backup .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $backup .= "SET AUTOCOMMIT = 0;\n";
        $backup .= "START TRANSACTION;\n\n";
        
        if (empty($tables)) {
            $result = $this->select("SHOW TABLES");
            foreach ($result as $row) {
                $tables[] = array_values($row)[0];
            }
        }
        
        foreach ($tables as $table) {
            // Table structure
            $createTable = $this->selectOne("SHOW CREATE TABLE {$table}");
            $backup .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $backup .= $createTable['Create Table'] . ";\n\n";
            
            // Table data
            $rows = $this->select("SELECT * FROM {$table}");
            if (!empty($rows)) {
                $backup .= "INSERT INTO `{$table}` VALUES\n";
                $values = [];
                foreach ($rows as $row) {
                    $escapedValues = array_map(function($value) {
                        return $value === null ? 'NULL' : $this->connection->quote($value);
                    }, array_values($row));
                    $values[] = '(' . implode(',', $escapedValues) . ')';
                }
                $backup .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        $backup .= "COMMIT;\n";
        return $backup;
    }
}