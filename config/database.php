<?php
/**
 * PSB Online - Database Connection Class
 * 
 * This file contains the Database class for handling MySQL connections
 * using PDO with comprehensive error handling and security measures.
 * 
 * @author PSB Online Team
 * @version 1.0
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

/**
 * Database Class
 * 
 * Handles database connections using PDO with singleton pattern,
 * prepared statements, error handling, and logging.
 */
class Database
{
    /**
     * @var PDO|null Database connection instance
     */
    private static $instance = null;
    
    /**
     * @var PDO Database connection
     */
    private $connection;
    
    /**
     * @var array Connection statistics
     */
    private $stats = [
        'queries' => 0,
        'errors' => 0,
        'start_time' => null
    ];
    
    /**
     * @var array Query log for debugging
     */
    private $queryLog = [];
    
    /**
     * @var bool Debug mode
     */
    private $debug = false;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $this->debug = config('APP_DEBUG', false);
        $this->stats['start_time'] = microtime(true);
        $this->connect();
    }
    
    /**
     * Get database instance (Singleton pattern)
     * 
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     * 
     * @throws PDOException
     * @return void
     */
    private function connect()
    {
        try {
            // Build DSN
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                config('DB_HOST', 'localhost'),
                config('DB_PORT', '3306'),
                config('DB_NAME', 'psb_online'),
                config('DB_CHARSET', 'utf8mb4')
            );
            
            // Get PDO options
            $options = config('DB_OPTIONS', []);
            
            // Create PDO connection
            $this->connection = new PDO($dsn, config('DB_USER', 'root'), config('DB_PASS', ''), $options);
            
            // Set additional attributes for security
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            // Set charset and collation
            $this->connection->exec("SET NAMES " . config('DB_CHARSET', 'utf8mb4') . " COLLATE " . config('DB_COLLATION', 'utf8mb4_unicode_ci'));
            
            // Log successful connection
            $this->log('Database connection established successfully', 'INFO');
            
        } catch (PDOException $e) {
            $this->log('Database connection failed: ' . $e->getMessage(), 'ERROR');
            throw new PDOException('Database connection failed: ' . $e->getMessage(), (int)$e->getCode());
        }
    }
    
    /**
     * Get PDO connection
     * 
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * Execute a query with prepared statements
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @param int $fetchMode Fetch mode
     * @return PDOStatement
     */
    public function query($sql, $params = [], $fetchMode = PDO::FETCH_ASSOC)
    {
        try {
            // Log query if debug mode is enabled
            if ($this->debug && config('DB_LOG_QUERIES', false)) {
                $this->logQuery($sql, $params);
            }
            
            // Prepare statement
            $stmt = $this->connection->prepare($sql);
            
            // Execute with parameters
            $stmt->execute($params);
            
            // Set fetch mode
            $stmt->setFetchMode($fetchMode);
            
            // Update statistics
            $this->stats['queries']++;
            
            return $stmt;
            
        } catch (PDOException $e) {
            $this->stats['errors']++;
            $this->log('Query execution failed: ' . $e->getMessage() . ' | SQL: ' . $sql, 'ERROR');
            throw new PDOException('Query execution failed: ' . $e->getMessage(), (int)$e->getCode());
        }
    }
    
    /**
     * Execute a query and fetch all results
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @param int $fetchMode Fetch mode
     * @return array
     */
    public function fetchAll($sql, $params = [], $fetchMode = PDO::FETCH_ASSOC)
    {
        $stmt = $this->query($sql, $params, $fetchMode);
        return $stmt->fetchAll();
    }
    
    /**
     * Execute a query and fetch single row
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @param int $fetchMode Fetch mode
     * @return array|false
     */
    public function fetchOne($sql, $params = [], $fetchMode = PDO::FETCH_ASSOC)
    {
        $stmt = $this->query($sql, $params, $fetchMode);
        return $stmt->fetch();
    }
    
    /**
     * Execute a query and fetch single value
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return mixed
     */
    public function fetchValue($sql, $params = [])
    {
        $stmt = $this->query($sql, $params, PDO::FETCH_NUM);
        $result = $stmt->fetch();
        return $result ? $result[0] : null;
    }
    
    /**
     * Execute INSERT query and return last insert ID
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return int|string
     */
    public function insert($sql, $params = [])
    {
        $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }
    
    /**
     * Execute UPDATE/DELETE query and return affected rows
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return int
     */
    public function execute($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin transaction
     * 
     * @return bool
     */
    public function beginTransaction()
    {
        try {
            $result = $this->connection->beginTransaction();
            if ($result) {
                $this->log('Transaction started', 'INFO');
            }
            return $result;
        } catch (PDOException $e) {
            $this->log('Failed to start transaction: ' . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Commit transaction
     * 
     * @return bool
     */
    public function commit()
    {
        try {
            $result = $this->connection->commit();
            if ($result) {
                $this->log('Transaction committed', 'INFO');
            }
            return $result;
        } catch (PDOException $e) {
            $this->log('Failed to commit transaction: ' . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Rollback transaction
     * 
     * @return bool
     */
    public function rollback()
    {
        try {
            $result = $this->connection->rollback();
            if ($result) {
                $this->log('Transaction rolled back', 'INFO');
            }
            return $result;
        } catch (PDOException $e) {
            $this->log('Failed to rollback transaction: ' . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Test database connection
     * 
     * @return bool
     */
    public function testConnection()
    {
        try {
            $this->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            $this->log('Connection test failed: ' . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Get database statistics
     * 
     * @return array
     */
    public function getStats()
    {
        $endTime = microtime(true);
        $executionTime = $endTime - $this->stats['start_time'];
        
        return [
            'queries' => $this->stats['queries'],
            'errors' => $this->stats['errors'],
            'execution_time' => round($executionTime, 4),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }
    
    /**
     * Get query log
     * 
     * @return array
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }
    
    /**
     * Clear query log
     * 
     * @return void
     */
    public function clearQueryLog()
    {
        $this->queryLog = [];
    }
    
    /**
     * Log query for debugging
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return void
     */
    private function logQuery($sql, $params)
    {
        $this->queryLog[] = [
            'sql' => $sql,
            'params' => $params,
            'time' => microtime(true),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
        ];
    }
    
    /**
     * Log database events
     * 
     * @param string $message Log message
     * @param string $level Log level
     * @return void
     */
    private function log($message, $level = 'INFO')
    {
        if (!config('DB_LOG_ENABLED', true)) {
            return;
        }
        
        $logFile = config('DB_LOG_FILE', LOGS_PATH . '/database.log');
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        // Ensure log directory exists
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Write to log file
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Escape string for safe SQL usage
     * 
     * @param string $string String to escape
     * @return string
     */
    public function escape($string)
    {
        return $this->connection->quote($string);
    }
    
    /**
     * Get table information
     * 
     * @param string $tableName Table name
     * @return array
     */
    public function getTableInfo($tableName)
    {
        $sql = "DESCRIBE " . $this->escape($tableName);
        return $this->fetchAll($sql);
    }
    
    /**
     * Get table list
     * 
     * @return array
     */
    public function getTables()
    {
        $sql = "SHOW TABLES";
        $result = $this->fetchAll($sql, [], PDO::FETCH_NUM);
        return array_column($result, 0);
    }
    
    /**
     * Check if table exists
     * 
     * @param string $tableName Table name
     * @return bool
     */
    public function tableExists($tableName)
    {
        $sql = "SHOW TABLES LIKE ?";
        $result = $this->fetchOne($sql, [$tableName]);
        return !empty($result);
    }
    
    /**
     * Get database size
     * 
     * @return array
     */
    public function getDatabaseSize()
    {
        $sql = "
            SELECT 
                table_schema AS 'database',
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
            FROM information_schema.tables 
            WHERE table_schema = ?
            GROUP BY table_schema
        ";
        
        return $this->fetchOne($sql, [config('DB_NAME', 'psb_online')]);
    }
    
    /**
     * Get table sizes
     * 
     * @return array
     */
    public function getTableSizes()
    {
        $sql = "
            SELECT 
                table_name AS 'table',
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb',
                table_rows AS 'rows'
            FROM information_schema.tables 
            WHERE table_schema = ?
            ORDER BY (data_length + index_length) DESC
        ";
        
        return $this->fetchAll($sql, [config('DB_NAME', 'psb_online')]);
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of the instance
     * (magic method must be public for PHP 8+)
     */
    public function __wakeup() {}
}

/**
 * Database Helper Functions
 */

/**
 * Get database instance
 * 
 * @return Database
 */
function db()
{
    return Database::getInstance();
}

/**
 * Execute a query and fetch all results
 * 
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return array
 */
function db_fetch_all($sql, $params = [])
{
    return db()->fetchAll($sql, $params);
}

/**
 * Execute a query and fetch single row
 * 
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return array|false
 */
function db_fetch_one($sql, $params = [])
{
    return db()->fetchOne($sql, $params);
}

/**
 * Execute a query and fetch single value
 * 
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return mixed
 */
function db_fetch_value($sql, $params = [])
{
    return db()->fetchValue($sql, $params);
}

/**
 * Execute INSERT query
 * 
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return int|string
 */
function db_insert($sql, $params = [])
{
    return db()->insert($sql, $params);
}

/**
 * Execute UPDATE/DELETE query
 * 
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return int
 */
function db_execute($sql, $params = [])
{
    return db()->execute($sql, $params);
}

/**
 * Test database connection
 * 
 * @return bool
 */
function db_test_connection()
{
    return db()->testConnection();
}

/**
 * Get database statistics
 * 
 * @return array
 */
function db_get_stats()
{
    return db()->getStats();
}

/**
 * Escape string for SQL
 * 
 * @param string $string String to escape
 * @return string
 */
function db_escape($string)
{
    return db()->escape($string);
} 