<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DatabaseBackupService
{
    /**
     * Menghasilkan dump SQL penuh dari database aktif dan menyimpannya di storage.
     *
     * @return string Path absolut ke file backup yang baru dibuat
     */
    public static function createBackup(): string
    {
        $backupDir = storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $fileName = "sinfas_backup_{$timestamp}.sql";
        $filePath = "{$backupDir}/{$fileName}";

        $pdo = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();

        $handle = fopen($filePath, 'w');

        // Header SQL
        fwrite($handle, "-- ============================================================\n");
        fwrite($handle, "-- SINFAS - Sistem Informasi Fasilitas Database Backup\n");
        fwrite($handle, "-- Database: {$dbName}\n");
        fwrite($handle, "-- Generated: " . Carbon::now()->format('Y-m-d H:i:s') . " WIB\n");
        fwrite($handle, "-- ============================================================\n\n");
        fwrite($handle, "SET NAMES utf8mb4;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

        // Dapatkan semua tabel
        $tables = [];
        $stmt = $pdo->query('SHOW TABLES');
        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            // Drop Table
            fwrite($handle, "\n-- ------------------------------------------------------------\n");
            fwrite($handle, "-- Table structure for `{$table}`\n");
            fwrite($handle, "-- ------------------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            // Create Table
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt->fetch(\PDO::FETCH_NUM);
            fwrite($handle, $createRow[1] . ";\n\n");

            // Dump data
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $totalRows = (int) $countStmt->fetchColumn();

            if ($totalRows > 0) {
                fwrite($handle, "-- Data for table `{$table}` ({$totalRows} rows)\n");
                $dataStmt = $pdo->query("SELECT * FROM `{$table}`");

                $rowsBatch = [];
                while ($row = $dataStmt->fetch(\PDO::FETCH_ASSOC)) {
                    $escapedValues = array_map(function ($val) use ($pdo) {
                        if (is_null($val)) {
                            return 'NULL';
                        }
                        return $pdo->quote($val);
                    }, array_values($row));

                    $rowsBatch[] = '(' . implode(', ', $escapedValues) . ')';

                    // Flush batch per 100 rows untuk efisiensi memori
                    if (count($rowsBatch) >= 100) {
                        $columns = array_keys($row);
                        $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
                        $sql = "INSERT INTO `{$table}` (" . implode(', ', $escapedColumns) . ") VALUES\n" . implode(",\n", $rowsBatch) . ";\n";
                        fwrite($handle, $sql);
                        $rowsBatch = [];
                    }
                }

                if (!empty($rowsBatch)) {
                    $columns = array_keys($row);
                    $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
                    $sql = "INSERT INTO `{$table}` (" . implode(', ', $escapedColumns) . ") VALUES\n" . implode(",\n", $rowsBatch) . ";\n";
                    fwrite($handle, $sql);
                }
            }
        }

        fwrite($handle, "\nSET FOREIGN_KEY_CHECKS = 1;\n");
        fwrite($handle, "-- Backup completed at " . Carbon::now()->format('Y-m-d H:i:s') . "\n");

        fclose($handle);

        // Update settings
        SystemSettingsService::set('last_backup_at', Carbon::now()->toIso8601String());
        SystemSettingsService::set('last_backup_file', $fileName);

        return $filePath;
    }

    /**
     * Memulihkan database dari file SQL yang diunggah.
     *
     * @param string $uploadedFilePath
     * @return void
     */
    public static function restoreFromSql(string $uploadedFilePath): void
    {
        if (!File::exists($uploadedFilePath)) {
            throw new \RuntimeException('File SQL tidak ditemukan.');
        }

        $sql = File::get($uploadedFilePath);

        DB::unprepared("SET FOREIGN_KEY_CHECKS = 0;");
        DB::unprepared($sql);
        DB::unprepared("SET FOREIGN_KEY_CHECKS = 1;");

        SystemSettingsService::set('last_restore_at', Carbon::now()->toIso8601String());
    }
}
