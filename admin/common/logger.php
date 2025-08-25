<?php
class Logger {
    private static $instance = null;
    private $logPath;
    private $logFile;
    private $maxFileSize = 10485760; // 10MB
    private $maxFiles = 5;
    
    private function __construct() {
        $this->logPath = dirname(__DIR__, 2) . '/logs';
        $this->ensureLogDirectory();
        $this->logFile = $this->logPath . '/app_' . date('Y-m-d') . '.log';
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function ensureLogDirectory() {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
        
        // 创建.htaccess文件防止直接访问日志
        $htaccess = $this->logPath . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all\n");
        }
    }
    
    private function rotateLog() {
        if (file_exists($this->logFile) && filesize($this->logFile) >= $this->maxFileSize) {
            $timestamp = date('Y-m-d_H-i-s');
            rename($this->logFile, $this->logPath . "/app_{$timestamp}.log");
            
            // 清理旧日志文件
            $this->cleanupOldLogs();
        }
    }
    
    private function cleanupOldLogs() {
        $pattern = $this->logPath . '/app_*.log';
        $files = glob($pattern);
        
        if (count($files) > $this->maxFiles) {
            // 按修改时间排序，删除最旧的文件
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            $filesToDelete = array_slice($files, 0, count($files) - $this->maxFiles);
            foreach ($filesToDelete as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
    
    private function write($level, $message, $context = []) {
        $this->rotateLog();
        
        $timestamp = date('Y-m-d H:i:s');
        $level = strtoupper($level);
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        
        $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";
        
        // 写入日志文件
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // 同时写入PHP错误日志，便于开发调试
        error_log($logEntry);
    }
    
    public function info($message, $context = []) {
        $this->write('INFO', $message, $context);
    }
    
    public function error($message, $context = []) {
        $this->write('ERROR', $message, $context);
    }
    
    public function warning($message, $context = []) {
        $this->write('WARNING', $message, $context);
    }
    
    public function debug($message, $context = []) {
        $this->write('DEBUG', $message, $context);
    }
    
    public function logException($exception, $context = []) {
        if (!is_array($context)) {
            $context = ['message' => $context];
        }
        $logData = [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
        $context = array_merge($context, $logData);
        $this->error('EXCEPTION', $context);
    }
    
    public function logModelAction($model, $action, $data = []) {
        $this->info("Model {$model} action: {$action}", $data);
    }
}

// 快捷函数
function log_info($message, $context = []) {
    Logger::getInstance()->info($message, $context);
}

function log_error($message, $context = []) {
    Logger::getInstance()->error($message, $context);
}

function log_warning($message, $context = []) {
    Logger::getInstance()->warning($message, $context);
}

function log_debug($message, $context = []) {
    Logger::getInstance()->debug($message, $context);
}

function log_exception($exception, $context = []) {
    Logger::getInstance()->logException($exception, $context);
}

function log_model($model, $action, $data = []) {
    Logger::getInstance()->logModelAction($model, $action, $data);
}