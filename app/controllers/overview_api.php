<?php

class Overview_api extends Controller {
    public function save() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
            return;
        }
        $userId = $_SESSION['auth']['id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['ok'=>false,'error'=>'Unauthorized']);
            return;
        }
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) { $data = []; }

        $month = $data['month'] ?? date('Y-m');
        $currency = $data['currency'] ?? 'USD';
        $totals = $data['totals'] ?? [];
        $catsNormal = $data['categories_normal'] ?? [];
        $catsTravel = $data['categories_travel'] ?? [];
        $kpis = $data['kpis'] ?? [];

        try {
            // Ensure table exists (idempotent). This is safe for MySQL/MariaDB; adjust for your DB if needed.
            $sqlCreate = "CREATE TABLE IF NOT EXISTS monthly_summaries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                year_month CHAR(7) NOT NULL,
                currency VARCHAR(8) NOT NULL,
                totals_json TEXT NOT NULL,
                categories_normal_json TEXT NOT NULL,
                categories_travel_json TEXT NOT NULL,
                kpis_json TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_user_month (user_id, year_month)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            db()->exec($sqlCreate);

            $st = db()->prepare("INSERT INTO monthly_summaries (user_id, year_month, currency, totals_json, categories_normal_json, categories_travel_json, kpis_json)
                                 VALUES (?, ?, ?, ?, ?, ?, ?)
                                 ON DUPLICATE KEY UPDATE currency=VALUES(currency), totals_json=VALUES(totals_json), categories_normal_json=VALUES(categories_normal_json), categories_travel_json=VALUES(categories_travel_json), kpis_json=VALUES(kpis_json), updated_at=CURRENT_TIMESTAMP");
            $ok = $st->execute([
                $userId,
                $month,
                $currency,
                json_encode($totals, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                json_encode($catsNormal, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                json_encode($catsTravel, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                json_encode($kpis, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            ]);
            echo json_encode(['ok'=>$ok===true]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
        }
    }
}
