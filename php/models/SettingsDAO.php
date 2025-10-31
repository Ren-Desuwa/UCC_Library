<?php
class SettingsDAO {
    private $conn;
    private $settings = null; // Simple cache

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Gets all settings from the database and returns them as an
     * associative array (e.g., ['max_books_per_user' => '5']).
     */
    public function getAllSettings() {
        if ($this->settings === null) {
            $sql = "SELECT setting_key, setting_value FROM settings";
            $result = $this->conn->query($sql);
            $this->settings = [];
            while ($row = $result->fetch_assoc()) {
                $this->settings[$row['setting_key']] = $row['setting_value'];
            }
        }
        return $this->settings;
    }

    /**
     * Gets a single setting value by its key.
     */
    public function getSetting($key) {
        $allSettings = $this->getAllSettings();
        return $allSettings[$key] ?? null;
    }

    /**
     * Updates a single setting.
     * Note: This is a single query, so no transaction is needed here.
     */
    public function updateSetting($key, $value) {
        $sql = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $value, $key);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to update setting: " . $stmt->error);
        }
        $this->settings = null; // Clear cache
        return $stmt->affected_rows > 0;
    }
}