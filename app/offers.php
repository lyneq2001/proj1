<?php
require_once 'config.php';
require_once 'auth.php';
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/AI/UserPreferencesService.php';

use App\AI\UserPreferencesService;

function columnExists(string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$table, $column]);
    $exists = (bool)$stmt->fetchColumn();
    $cache[$key] = $exists;

    return $exists;
}

function ensureOfferStatusColumn(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }

    global $pdo;
    if (!columnExists('offers', 'status')) {
        try {
            $pdo->exec("ALTER TABLE offers ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        } catch (PDOException $e) {
            // Column may already exist due to race condition.
        }
    }

    if (!columnExists('offers', 'status_updated_at')) {
        try {
            $pdo->exec("ALTER TABLE offers ADD COLUMN status_updated_at DATETIME NULL");
        } catch (PDOException $e) {
            // Column may already exist.
        }
    }

    $checked = true;
}

function ensureReportsTable(): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    global $pdo;
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            offer_id INT NOT NULL,
            reporter_id INT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            reason TEXT NOT NULL,
            admin_note TEXT NULL,
            handled_by INT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_offer (offer_id),
            INDEX idx_reporter (reporter_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $initialized = true;
}

function ensureOfferViewsTable(): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    global $pdo;
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS offer_views (
            id INT AUTO_INCREMENT PRIMARY KEY,
            offer_id INT NOT NULL,
            viewer_identifier VARCHAR(64) NULL,
            viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_offer_viewed_at (offer_id, viewed_at),
            CONSTRAINT fk_offer_views_offer FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $initialized = true;
}

function ensureAiOfferUsageTable(): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    global $pdo;
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS ai_offer_usages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            offer_id INT NOT NULL,
            user_id INT NULL,
            viewer_identifier VARCHAR(64) NULL,
            source VARCHAR(32) NOT NULL DEFAULT 'search',
            used_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ai_offer_usage (offer_id, used_at),
            INDEX idx_ai_offer_user (user_id),
            CONSTRAINT fk_ai_offer_usage_offer FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,
            CONSTRAINT fk_ai_offer_usage_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $initialized = true;
}

function ensureAiOfferReactionsTable(): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    global $pdo;
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS ai_offer_reactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            offer_id INT NOT NULL,
            user_id INT NOT NULL,
            reaction VARCHAR(8) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_ai_offer_reaction (offer_id, user_id),
            INDEX idx_ai_offer_reaction_offer (offer_id),
            CONSTRAINT fk_ai_offer_reaction_offer FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,
            CONSTRAINT fk_ai_offer_reaction_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $initialized = true;
}

function recordOfferView(int $offerId): void
{
    global $pdo;

    ensureOfferViewsTable();

    $stmt = $pdo->prepare("UPDATE offers SET visits = visits + 1 WHERE id = ?");
    $stmt->execute([$offerId]);

    $viewerIdentifier = null;
    if (isset($_SESSION['user_id'])) {
        $viewerIdentifier = 'user:' . (int)$_SESSION['user_id'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $viewerIdentifier = 'ip:' . $_SERVER['REMOTE_ADDR'];
    }

    $stmt = $pdo->prepare("INSERT INTO offer_views (offer_id, viewer_identifier) VALUES (?, ?)");
    $stmt->execute([$offerId, $viewerIdentifier]);

    if (isset($_SESSION['user_id'])) {
        try {
            $preferences = new UserPreferencesService($pdo);
            $preferences->recordAction((int)$_SESSION['user_id'], $offerId, 'view');
        } catch (Throwable $e) {
            error_log('Could not record view in user history: ' . $e->getMessage());
        }
    }
}

function recordAiOfferUsage(int $offerId, string $source = 'search'): void
{
    global $pdo;

    ensureAiOfferUsageTable();

    $viewerIdentifier = null;
    $userId = null;
    if (isset($_SESSION['user_id'])) {
        $userId = (int)$_SESSION['user_id'];
        $viewerIdentifier = 'user:' . $userId;
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $viewerIdentifier = 'ip:' . $_SERVER['REMOTE_ADDR'];
    }

    $stmt = $pdo->prepare("INSERT INTO ai_offer_usages (offer_id, user_id, viewer_identifier, source) VALUES (?, ?, ?, ?)");
    $stmt->execute([$offerId, $userId, $viewerIdentifier, $source]);
}

function setAiOfferReaction(int $offerId, int $userId, string $reaction): void
{
    global $pdo;

    ensureAiOfferReactionsTable();

    $reaction = strtolower(trim($reaction));
    if (!in_array($reaction, ['like', 'dislike'], true)) {
        return;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO ai_offer_reactions (offer_id, user_id, reaction)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE reaction = VALUES(reaction), updated_at = NOW()"
    );
    $stmt->execute([$offerId, $userId, $reaction]);
}

function getAiOfferReactionCounts(array $offerIds): array
{
    if (empty($offerIds)) {
        return [];
    }

    global $pdo;
    ensureAiOfferReactionsTable();

    $placeholders = implode(',', array_fill(0, count($offerIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT offer_id,
                SUM(CASE WHEN reaction = 'like' THEN 1 ELSE 0 END) AS likes,
                SUM(CASE WHEN reaction = 'dislike' THEN 1 ELSE 0 END) AS dislikes
         FROM ai_offer_reactions
         WHERE offer_id IN ({$placeholders})
         GROUP BY offer_id"
    );
    $stmt->execute(array_values($offerIds));

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $counts = [];
    foreach ($rows as $row) {
        $offerId = (int)($row['offer_id'] ?? 0);
        if ($offerId) {
            $counts[$offerId] = [
                'likes' => (int)($row['likes'] ?? 0),
                'dislikes' => (int)($row['dislikes'] ?? 0)
            ];
        }
    }

    return $counts;
}

function getAiOfferReactionsForUser(int $userId, array $offerIds): array
{
    if (empty($offerIds)) {
        return [];
    }

    global $pdo;
    ensureAiOfferReactionsTable();

    $placeholders = implode(',', array_fill(0, count($offerIds), '?'));
    $params = array_merge([$userId], array_values($offerIds));
    $stmt = $pdo->prepare(
        "SELECT offer_id, reaction
         FROM ai_offer_reactions
         WHERE user_id = ?
           AND offer_id IN ({$placeholders})"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $reactions = [];
    foreach ($rows as $row) {
        $offerId = (int)($row['offer_id'] ?? 0);
        if ($offerId) {
            $reactions[$offerId] = $row['reaction'];
        }
    }

    return $reactions;
}

function getAiOfferUsageSummary(int $limit = 20): array
{
    global $pdo;

    ensureAiOfferUsageTable();
    ensureAiOfferReactionsTable();

    $limit = max(1, $limit);
    $query = "
        SELECT o.id,
               o.title,
               usage_stats.usage_count,
               usage_stats.unique_users,
               usage_stats.last_used_at,
               COALESCE(reaction_stats.likes, 0) AS likes,
               COALESCE(reaction_stats.dislikes, 0) AS dislikes
        FROM offers o
        JOIN (
            SELECT offer_id,
                   COUNT(*) AS usage_count,
                   COUNT(DISTINCT user_id) AS unique_users,
                   MAX(used_at) AS last_used_at
            FROM ai_offer_usages
            GROUP BY offer_id
        ) usage_stats ON o.id = usage_stats.offer_id
        LEFT JOIN (
            SELECT offer_id,
                   SUM(CASE WHEN reaction = 'like' THEN 1 ELSE 0 END) AS likes,
                   SUM(CASE WHEN reaction = 'dislike' THEN 1 ELSE 0 END) AS dislikes
            FROM ai_offer_reactions
            GROUP BY offer_id
        ) reaction_stats ON o.id = reaction_stats.offer_id
        ORDER BY usage_stats.usage_count DESC, reaction_stats.likes DESC
        LIMIT {$limit}";

    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAiOfferUsageByUser(int $limit = 20): array
{
    global $pdo;

    ensureAiOfferUsageTable();

    $limit = max(1, $limit);
    $query = "
        SELECT u.id,
               u.username,
               u.email,
               COUNT(au.id) AS usage_count,
               MAX(au.used_at) AS last_used_at
        FROM ai_offer_usages au
        JOIN users u ON au.user_id = u.id
        GROUP BY au.user_id
        ORDER BY usage_count DESC
        LIMIT {$limit}";

    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function filterOffersBySearchFilters(array $offers, array $filters): array
{
    return array_values(array_filter($offers, function ($offer) use ($filters) {
        if (!empty($filters['city']) && stripos((string)($offer['city'] ?? ''), (string)$filters['city']) === false) {
            return false;
        }
        if (!empty($filters['street']) && stripos((string)($offer['street'] ?? ''), (string)$filters['street']) === false) {
            return false;
        }
        if (!empty($filters['min_price']) && (float)($offer['price'] ?? 0) < (float)$filters['min_price']) {
            return false;
        }
        if (!empty($filters['max_price']) && (float)($offer['price'] ?? 0) > (float)$filters['max_price']) {
            return false;
        }
        if (!empty($filters['min_size']) && (float)($offer['size'] ?? 0) < (float)$filters['min_size']) {
            return false;
        }
        if (!empty($filters['min_floor']) && (float)($offer['floor'] ?? 0) < (float)$filters['min_floor']) {
            return false;
        }
        if (!empty($filters['max_floor']) && (float)($offer['floor'] ?? 0) > (float)$filters['max_floor']) {
            return false;
        }
        if (isset($filters['has_balcony']) && $filters['has_balcony'] == 1 && empty($offer['has_balcony'])) {
            return false;
        }
        if (isset($filters['has_elevator']) && $filters['has_elevator'] == 1 && empty($offer['has_elevator'])) {
            return false;
        }
        if (!empty($filters['building_type']) && ($offer['building_type'] ?? '') !== $filters['building_type']) {
            return false;
        }
        if (!empty($filters['min_rooms']) && (int)($offer['rooms'] ?? 0) < (int)$filters['min_rooms']) {
            return false;
        }
        if (!empty($filters['min_bathrooms']) && (int)($offer['bathrooms'] ?? 0) < (int)$filters['min_bathrooms']) {
            return false;
        }
        if (isset($filters['parking']) && $filters['parking'] == 1 && empty($offer['parking'])) {
            return false;
        }
        if (isset($filters['furnished']) && $filters['furnished'] == 1 && empty($offer['furnished'])) {
            return false;
        }

        return true;
    }));
}

function addOffer($title, $description, $city, $street, $price, $size, $floor, $has_balcony, $has_elevator, $building_type, $rooms, $bathrooms, $parking, $garage, $garden, $furnished, $pets_allowed, $heating_type, $year_built, $condition_type, $available_from, $images, $primary_image_index) {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Unauthorized.');
        return;
    }
    global $pdo;

    // Validate inputs
    $errors = [];
    if (strlen($title) < 3 || strlen($title) > 100) {
        $errors['title'] = 'Title must be between 3 and 100 characters.';
    }
    if (empty($description)) {
        $errors['description'] = 'Description is required.';
    }
    if (strlen($city) < 3 || strlen($city) > 100) {
        $errors['city'] = 'City must be between 3 and 100 characters.';
    }
    if (strlen($street) < 3 || strlen($street) > 100) {
        $errors['street'] = 'Street must be between 3 and 100 characters.';
    }
    if (!is_numeric($price) || $price <= 0 || $price > 1000000 || $price != floor($price)) {
        $errors['price'] = 'Price must be a whole number between 1 and 1,000,000 PLN.';
    }
    if ($size <= 0 || $size > 10000) {
        $errors['size'] = 'Size must be between 0 and 10,000 m².';
    }
    if (!in_array($building_type, ['apartment', 'block', 'house', 'studio', 'loft'])) {
        $errors['building_type'] = 'Invalid building type.';
    }
    if ($rooms < 1 || $rooms > 50) {
        $errors['rooms'] = 'Rooms must be between 1 and 50.';
    }
    if ($bathrooms < 1 || $bathrooms > 20) {
        $errors['bathrooms'] = 'Bathrooms must be between 1 and 20.';
    }
    if (!in_array($heating_type, ['gas', 'electric', 'district', 'other'])) {
        $errors['heating_type'] = 'Invalid heating type.';
    }
    if (!in_array($condition_type, ['new', 'renovated', 'used', 'to_renovate'])) {
        $errors['condition_type'] = 'Invalid condition type.';
    }

    if (!empty($errors)) {
        setFormErrors($errors);
        setOldInput($_POST);
        setFlashMessage('error', 'Please correct the errors below.');
        return;
    }

    ensureOfferStatusColumn();

    // Geocode address using Nominatim
    $address = urlencode($city . ', ' . $street);
    $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ApartmentRentalApp/1.0 (your.email@example.com)'); // Replace with your email
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    $lat = null;
    $lng = null;
    if (!empty($data)) {
        $lat = $data[0]['lat'];
        $lng = $data[0]['lon'];
    }

    // Insert offer
    $stmt = $pdo->prepare("INSERT INTO offers (user_id, title, description, city, street, lat, lng, price, size, floor, has_balcony, has_elevator, building_type, rooms, bathrooms, parking, garage, garden, furnished, pets_allowed, heating_type, year_built, condition_type, available_from, status, status_updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    try {
        $stmt->execute([$_SESSION['user_id'], $title, $description, $city, $street, $lat, $lng, $price, $size, $floor, $has_balcony, $has_elevator, $building_type, $rooms, $bathrooms, $parking, $garage, $garden, $furnished, $pets_allowed, $heating_type, $year_built, $condition_type, $available_from, 'active']);
        $offer_id = $pdo->lastInsertId();

        // Handle image uploads
        if (!empty($images['name'][0])) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $image_count = count(array_filter($images['name']));
            if ($image_count > 5) {
                setFlashMessage('error', 'Maximum 5 images allowed.');
                return;
            }
            foreach ($images['name'] as $index => $name) {
                if ($images['error'][$index] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        setFlashMessage('error', 'Only JPEG or PNG images allowed.');
                        return;
                    }
                    if ($images['size'][$index] > 5 * 1024 * 1024) {
                        setFlashMessage('error', 'Image size must be less than 5MB.');
                        return;
                    }
                    $filename = uniqid() . '.' . $ext;
                    $destination = $upload_dir . $filename;
                    if (move_uploaded_file($images['tmp_name'][$index], $destination)) {
                        $is_primary = ($index == $primary_image_index) ? 1 : 0;
                        $stmt = $pdo->prepare("INSERT INTO images (offer_id, file_path, is_primary) VALUES (?, ?, ?)");
                        $stmt->execute([$offer_id, $destination, $is_primary]);
                    } else {
                        setFlashMessage('error', 'Failed to upload image.');
                        return;
                    }
                }
            }
        }

        clearOldInput();
        setFlashMessage('success', 'Offer added successfully.');
        header("Location: index.php");
    } catch (PDOException $e) {
        setFlashMessage('error', 'Failed to add offer: ' . $e->getMessage());
    }
}

function searchOffers($filters, $page = 1, $perPage = 10) {
    global $pdo;
    ensureOfferViewsTable();
    $offset = ($page - 1) * $perPage;
    $query = "SELECT o.*, COALESCE(img.primary_image, img.first_image) AS primary_image, COALESCE(v.views_last_24h, 0) AS views_last_24h";
    $countQuery = "SELECT COUNT(*)";
    $params = [];
    $countParams = [];

    // Haversine formula for distance calculation
    $center_lat = null;
    $center_lng = null;
    if (!empty($filters['city']) && !empty($filters['distance_km'])) {
        $address = urlencode($filters['city']);
        $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ApartmentRentalApp/1.0 (your.email@example.com)');
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        if (!empty($data)) {
            $center_lat = $data[0]['lat'];
            $center_lng = $data[0]['lon'];
        }
        
        if ($center_lat && $center_lng) {
            $query .= ", (6371 * acos(cos(radians(?)) * cos(radians(o.lat)) * cos(radians(o.lng) - radians(?)) + sin(radians(?)) * sin(radians(o.lat)))) AS distance";
            $params[] = $center_lat;
            $params[] = $center_lng;
            $params[] = $center_lat;
        }
    }
    
    $query .= " FROM offers o
               LEFT JOIN (
                   SELECT offer_id,
                          MAX(CASE WHEN is_primary = 1 THEN file_path END) AS primary_image,
                          MIN(file_path) AS first_image
                   FROM images
                   GROUP BY offer_id
               ) img ON o.id = img.offer_id
               LEFT JOIN (
                   SELECT offer_id, COUNT(*) AS views_last_24h
                   FROM offer_views
                   WHERE viewed_at >= (NOW() - INTERVAL 24 HOUR)
                   GROUP BY offer_id
               ) v ON o.id = v.offer_id
               WHERE 1=1";
    $countQuery .= " FROM offers o WHERE 1=1";
    
    if (!empty($filters['city']) && !empty($filters['distance_km']) && isset($center_lat, $center_lng)) {
        $query .= " AND (6371 * acos(cos(radians(?)) * cos(radians(o.lat)) * cos(radians(o.lng) - radians(?)) + sin(radians(?)) * sin(radians(o.lat)))) <= ?";
        $countQuery .= " AND (6371 * acos(cos(radians(?)) * cos(radians(o.lat)) * cos(radians(o.lng) - radians(?)) + sin(radians(?)) * sin(radians(o.lat)))) <= ?";
        $params[] = $center_lat;
        $params[] = $center_lng;
        $params[] = $center_lat;
        $params[] = $filters['distance_km'];
        $countParams[] = $center_lat;
        $countParams[] = $center_lng;
        $countParams[] = $center_lat;
        $countParams[] = $filters['distance_km'];
    }

    if (!empty($filters['city'])) {
        $query .= " AND o.city LIKE ?";
        $countQuery .= " AND o.city LIKE ?";
        $params[] = "%{$filters['city']}%";
        $countParams[] = "%{$filters['city']}%";
    }
    if (!empty($filters['street'])) {
        $query .= " AND o.street LIKE ?";
        $countQuery .= " AND o.street LIKE ?";
        $params[] = "%{$filters['street']}%";
        $countParams[] = "%{$filters['street']}%";
    }
    if (!empty($filters['min_price'])) {
        $query .= " AND o.price >= ?";
        $countQuery .= " AND o.price >= ?";
        $params[] = $filters['min_price'];
        $countParams[] = $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $query .= " AND o.price <= ?";
        $countQuery .= " AND o.price <= ?";
        $params[] = $filters['max_price'];
        $countParams[] = $filters['max_price'];
    }
    if (!empty($filters['min_size'])) {
        $query .= " AND o.size >= ?";
        $countQuery .= " AND o.size >= ?";
        $params[] = $filters['min_size'];
        $countParams[] = $filters['min_size'];
    }
    if (!empty($filters['min_floor'])) {
        $query .= " AND o.floor >= ?";
        $countQuery .= " AND o.floor >= ?";
        $params[] = $filters['min_floor'];
        $countParams[] = $filters['min_floor'];
    }
    if (!empty($filters['max_floor'])) {
        $query .= " AND o.floor <= ?";
        $countQuery .= " AND o.floor <= ?";
        $params[] = $filters['max_floor'];
        $countParams[] = $filters['max_floor'];
    }
    if (isset($filters['has_balcony']) && $filters['has_balcony'] == 1) {
        $query .= " AND o.has_balcony = 1";
        $countQuery .= " AND o.has_balcony = 1";
    }
    if (isset($filters['has_elevator']) && $filters['has_elevator'] == 1) {
        $query .= " AND o.has_elevator = 1";
        $countQuery .= " AND o.has_elevator = 1";
    }
    if (!empty($filters['building_type'])) {
        $query .= " AND o.building_type = ?";
        $countQuery .= " AND o.building_type = ?";
        $params[] = $filters['building_type'];
        $countParams[] = $filters['building_type'];
    }
    if (!empty($filters['min_rooms'])) {
        $query .= " AND o.rooms >= ?";
        $countQuery .= " AND o.rooms >= ?";
        $params[] = $filters['min_rooms'];
        $countParams[] = $filters['min_rooms'];
    }
    if (!empty($filters['min_bathrooms'])) {
        $query .= " AND o.bathrooms >= ?";
        $countQuery .= " AND o.bathrooms >= ?";
        $params[] = $filters['min_bathrooms'];
        $countParams[] = $filters['min_bathrooms'];
    }
    if (isset($filters['parking']) && $filters['parking'] == 1) {
        $query .= " AND o.parking = 1";
        $countQuery .= " AND o.parking = 1";
    }
    if (isset($filters['furnished']) && $filters['furnished'] == 1) {
        $query .= " AND o.furnished = 1";
        $countQuery .= " AND o.furnished = 1";
    }

    // Handle sorting
    $sort = $filters['sort'] ?? 'date_desc';
    switch ($sort) {
        case 'price_asc':
            $query .= " ORDER BY o.price ASC";
            break;
        case 'price_desc':
            $query .= " ORDER BY o.price DESC";
            break;
        case 'date_asc':
            $query .= " ORDER BY o.created_at ASC";
            break;
        case 'date_desc':
            $query .= " ORDER BY o.created_at DESC";
            break;
        case 'size_asc':
            $query .= " ORDER BY o.size ASC";
            break;
        case 'size_desc':
            $query .= " ORDER BY o.size DESC";
            break;
        case 'popularity_desc':
            $query .= " ORDER BY COALESCE(v.views_last_24h, 0) DESC, o.visits DESC";
            break;
        case 'popularity_asc':
            $query .= " ORDER BY COALESCE(v.views_last_24h, 0) ASC, o.visits ASC";
            break;
        default:
            $query .= " ORDER BY o.created_at DESC";
    }

    $query .= " LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    try {
        $stmt = $pdo->prepare($query);
        foreach ($params as $index => $param) {
            $stmt->bindValue($index + 1, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countStmt = $pdo->prepare($countQuery);
        foreach ($countParams as $index => $param) {
            $countStmt->bindValue($index + 1, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        return ['offers' => $offers, 'total' => $total];
    } catch (PDOException $e) {
        setFlashMessage('error', 'Search failed: ' . $e->getMessage());
        return ['offers' => [], 'total' => 0];
    }
}

function searchOffersMapData($filters): array
{
    global $pdo;

    $query = "SELECT o.id, o.title, o.lat, o.lng, o.price, o.city, o.street FROM offers o WHERE 1=1";
    $params = [];

    $center_lat = null;
    $center_lng = null;
    if (!empty($filters['city']) && !empty($filters['distance_km'])) {
        $address = urlencode($filters['city']);
        $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ApartmentRentalApp/1.0 (your.email@example.com)');
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!empty($data)) {
            $center_lat = $data[0]['lat'];
            $center_lng = $data[0]['lon'];
        }
    }

    if (!empty($filters['city']) && !empty($filters['distance_km']) && isset($center_lat, $center_lng)) {
        $query .= " AND (6371 * acos(cos(radians(?)) * cos(radians(o.lat)) * cos(radians(o.lng) - radians(?)) + sin(radians(?)) * sin(radians(o.lat)))) <= ?";
        $params[] = $center_lat;
        $params[] = $center_lng;
        $params[] = $center_lat;
        $params[] = $filters['distance_km'];
    }

    if (!empty($filters['city'])) {
        $query .= " AND o.city LIKE ?";
        $params[] = "%{$filters['city']}%";
    }
    if (!empty($filters['street'])) {
        $query .= " AND o.street LIKE ?";
        $params[] = "%{$filters['street']}%";
    }
    if (!empty($filters['min_price'])) {
        $query .= " AND o.price >= ?";
        $params[] = $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $query .= " AND o.price <= ?";
        $params[] = $filters['max_price'];
    }
    if (!empty($filters['min_size'])) {
        $query .= " AND o.size >= ?";
        $params[] = $filters['min_size'];
    }
    if (!empty($filters['min_floor'])) {
        $query .= " AND o.floor >= ?";
        $params[] = $filters['min_floor'];
    }
    if (!empty($filters['max_floor'])) {
        $query .= " AND o.floor <= ?";
        $params[] = $filters['max_floor'];
    }
    if (isset($filters['has_balcony']) && $filters['has_balcony'] == 1) {
        $query .= " AND o.has_balcony = 1";
    }
    if (isset($filters['has_elevator']) && $filters['has_elevator'] == 1) {
        $query .= " AND o.has_elevator = 1";
    }
    if (!empty($filters['building_type'])) {
        $query .= " AND o.building_type = ?";
        $params[] = $filters['building_type'];
    }
    if (!empty($filters['min_rooms'])) {
        $query .= " AND o.rooms >= ?";
        $params[] = $filters['min_rooms'];
    }
    if (!empty($filters['min_bathrooms'])) {
        $query .= " AND o.bathrooms >= ?";
        $params[] = $filters['min_bathrooms'];
    }
    if (isset($filters['parking']) && $filters['parking'] == 1) {
        $query .= " AND o.parking = 1";
    }
    if (isset($filters['furnished']) && $filters['furnished'] == 1) {
        $query .= " AND o.furnished = 1";
    }

    try {
        $stmt = $pdo->prepare($query);
        foreach ($params as $index => $param) {
            $stmt->bindValue($index + 1, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $geocodeCache = [];

        foreach ($offers as &$offer) {
            if (!empty($offer['lat']) && !empty($offer['lng'])) {
                continue;
            }

            $city = trim((string)($offer['city'] ?? ''));
            $street = trim((string)($offer['street'] ?? ''));
            if ($city === '' || $street === '') {
                continue;
            }

            $cacheKey = mb_strtolower($city . '|' . $street);
            if (array_key_exists($cacheKey, $geocodeCache)) {
                $coords = $geocodeCache[$cacheKey];
            } else {
                $address = urlencode($city . ', ' . $street);
                $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'ApartmentRentalApp/1.0 (your.email@example.com)');
                $response = curl_exec($ch);
                curl_close($ch);

                $data = json_decode($response, true);
                $coords = null;
                if (!empty($data)) {
                    $coords = [
                        'lat' => (float)$data[0]['lat'],
                        'lng' => (float)$data[0]['lon'],
                    ];
                }
                $geocodeCache[$cacheKey] = $coords;
            }

            if (is_array($coords)) {
                $offer['lat'] = $coords['lat'];
                $offer['lng'] = $coords['lng'];
                $updateStmt = $pdo->prepare("UPDATE offers SET lat = ?, lng = ? WHERE id = ?");
                $updateStmt->execute([$coords['lat'], $coords['lng'], $offer['id']]);
            }
        }
        unset($offer);

        return $offers;
    } catch (PDOException $e) {
        setFlashMessage('error', 'Search failed: ' . $e->getMessage());
        return [];
    }
}

function getUserOffers($userId, $page = 1, $perPage = 10) {
    global $pdo;
    ensureOfferViewsTable();
    $offset = ($page - 1) * $perPage;
    $stmt = $pdo->prepare("SELECT o.*, COALESCE(img.primary_image, img.first_image) AS primary_image, COALESCE(v.views_last_24h, 0) AS views_last_24h
                           FROM offers o
                           LEFT JOIN (
                               SELECT offer_id,
                                      MAX(CASE WHEN is_primary = 1 THEN file_path END) AS primary_image,
                                      MIN(file_path) AS first_image
                               FROM images
                               GROUP BY offer_id
                           ) img ON o.id = img.offer_id
                           LEFT JOIN (
                               SELECT offer_id, COUNT(*) AS views_last_24h
                               FROM offer_views
                               WHERE viewed_at >= (NOW() - INTERVAL 24 HOUR)
                               GROUP BY offer_id
                           ) v ON o.id = v.offer_id
                           WHERE o.user_id = ?
                           LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $valid_offers = [];
    foreach ($offers as $offer) {
        if (is_array($offer) && isset($offer['id'], $offer['title'], $offer['city'], $offer['street'], $offer['price'], $offer['size'])) {
            $valid_offers[] = $offer;
        }
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM offers WHERE user_id = ?");
    $stmt->execute([$userId]);
    $total = $stmt->fetchColumn();

    return ['offers' => $valid_offers, 'total' => $total];
}

function getConversations($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.*, o.title AS offer_title, o.user_id AS offer_owner_id,
               u_sender.username AS sender_username, 
               u_receiver.username AS receiver_username,
               SUM(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) OVER (PARTITION BY m.offer_id, LEAST(m.sender_id, m.receiver_id), GREATEST(m.sender_id, m.receiver_id)) AS unread_count
        FROM messages m
        JOIN offers o ON m.offer_id = o.id
        JOIN users u_sender ON m.sender_id = u_sender.id
        JOIN users u_receiver ON m.receiver_id = u_receiver.id
        WHERE m.sender_id = ? OR m.receiver_id = ?
        ORDER BY m.sent_at ASC
    ");
    $stmt->execute([$userId, $userId, $userId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $conversations = [];
    foreach ($messages as $msg) {
        if (!is_array($msg) || !isset($msg['offer_id'], $msg['sender_id'], $msg['receiver_id'], $msg['offer_owner_id'])) {
            continue;
        }
        $other_user_id = ($msg['sender_id'] == $userId) ? $msg['receiver_id'] : $msg['sender_id'];
        $other_user = ($msg['sender_id'] == $userId) ? $msg['receiver_username'] : $msg['sender_username'];
        $is_owner = ($msg['offer_owner_id'] == $userId);
        
        $user_pair = [$userId, $other_user_id];
        sort($user_pair);
        $conversation_key = $msg['offer_id'] . '_' . implode('_', $user_pair);
        
        if (!isset($conversations[$conversation_key])) {
            $conversations[$conversation_key] = [
                'offer_id' => $msg['offer_id'],
                'offer_title' => $msg['offer_title'] ?? 'Unknown Offer',
                'other_user_id' => $other_user_id,
                'other_user' => $other_user ?? 'Unknown User',
                'is_owner' => $is_owner,
                'unread_count' => $msg['unread_count'] ?? 0,
                'messages' => []
            ];
        }
        $conversations[$conversation_key]['messages'][] = $msg;
    }

    return array_values($conversations);
}

function getOfferDetails($offerId) {
    global $pdo;
    ensureOfferViewsTable();
    ensureUserPhoneColumn();
    $stmt = $pdo->prepare("
        SELECT o.*, u.username AS owner_username, u.phone AS owner_phone, COALESCE(v.views_last_24h, 0) AS views_last_24h
        FROM offers o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN (
            SELECT offer_id, COUNT(*) AS views_last_24h
            FROM offer_views
            WHERE viewed_at >= (NOW() - INTERVAL 24 HOUR)
            GROUP BY offer_id
        ) v ON o.id = v.offer_id
        WHERE o.id = ?
    ");
    $stmt->execute([$offerId]);
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($offer) {
        $stmt = $pdo->prepare("SELECT file_path, is_primary FROM images WHERE offer_id = ? ORDER BY is_primary DESC");
        $stmt->execute([$offerId]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $offer['images'] = $images;
        // Set primary_image
        $offer['primary_image'] = null;
        foreach ($images as $image) {
            if ($image['is_primary']) {
                $offer['primary_image'] = $image['file_path'];
                break;
            }
        }
        if (!$offer['primary_image'] && !empty($images)) {
            $offer['primary_image'] = $images[0]['file_path'];
        }
    }

    return $offer;
}

function generateRandomOffers(int $count, int $userId): int
{
    global $pdo;

    if ($count < 1) {
        return 0;
    }

    ensureOfferStatusColumn();

    $cities = [
        [
            'name' => 'Warszawa',
            'lat' => 52.2297,
            'lng' => 21.0122,
            'streets' => ['Marszałkowska', 'Puławska', 'Grójecka', 'Aleje Jerozolimskie', 'Nowy Świat'],
            'price_range' => [80, 140],
        ],
        [
            'name' => 'Kraków',
            'lat' => 50.0647,
            'lng' => 19.9450,
            'streets' => ['Długa', 'Karmelicka', 'Dietla', 'Zakopiańska', 'Wielicka'],
            'price_range' => [70, 120],
        ],
        [
            'name' => 'Wrocław',
            'lat' => 51.1079,
            'lng' => 17.0385,
            'streets' => ['Legnicka', 'Grabiszyńska', 'Kazimierza Wielkiego', 'Powstańców Śląskich', 'Oławska'],
            'price_range' => [65, 110],
        ],
        [
            'name' => 'Gdańsk',
            'lat' => 54.3520,
            'lng' => 18.6466,
            'streets' => ['Grunwaldzka', 'Kartuska', 'Słowackiego', 'Chmielna', 'Wita Stwosza'],
            'price_range' => [70, 115],
        ],
        [
            'name' => 'Poznań',
            'lat' => 52.4064,
            'lng' => 16.9252,
            'streets' => ['Głogowska', 'Grunwaldzka', 'Polna', 'Hetmańska', 'Dąbrowskiego'],
            'price_range' => [60, 100],
        ],
        [
            'name' => 'Łódź',
            'lat' => 51.7592,
            'lng' => 19.4560,
            'streets' => ['Piotrkowska', 'Zgierska', 'Pabianicka', 'Widzewska', 'Narutowicza'],
            'price_range' => [45, 85],
        ],
    ];

    $buildingTypes = ['apartment', 'block', 'house', 'studio', 'loft'];
    $heatingTypes = ['gas', 'electric', 'district', 'other'];
    $conditionTypes = ['new', 'renovated', 'used', 'to_renovate'];
    $adjectives = ['Nowoczesne', 'Przestronne', 'Przytulne', 'Jasne', 'Stylowe', 'Komfortowe'];
    $highlights = [
        'blisko komunikacji miejskiej',
        'w pobliżu terenów zielonych',
        'z szybkim dojazdem do centrum',
        'w spokojnej okolicy',
        'z pełną infrastrukturą usługową',
        'w sąsiedztwie kawiarni i restauracji',
    ];

    $stmt = $pdo->prepare(
        "INSERT INTO offers (user_id, title, description, city, street, lat, lng, price, size, floor, has_balcony, has_elevator, building_type, rooms, bathrooms, parking, garage, garden, furnished, pets_allowed, heating_type, year_built, condition_type, available_from, status, status_updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())"
    );

    $created = 0;

    for ($i = 0; $i < $count; $i++) {
        $city = $cities[array_rand($cities)];
        $buildingType = $buildingTypes[array_rand($buildingTypes)];
        $heatingType = $heatingTypes[array_rand($heatingTypes)];
        $conditionType = $conditionTypes[array_rand($conditionTypes)];
        $street = $city['streets'][array_rand($city['streets'])];
        $adjective = $adjectives[array_rand($adjectives)];
        $highlight = $highlights[array_rand($highlights)];

        switch ($buildingType) {
            case 'studio':
                $size = random_int(22, 40);
                $rooms = 1;
                $floor = random_int(0, 6);
                break;
            case 'house':
                $size = random_int(85, 220);
                $rooms = random_int(3, 6);
                $floor = random_int(0, 2);
                break;
            case 'loft':
                $size = random_int(45, 120);
                $rooms = random_int(2, 4);
                $floor = random_int(1, 8);
                break;
            case 'block':
                $size = random_int(35, 85);
                $rooms = random_int(2, 4);
                $floor = random_int(0, 10);
                break;
            default:
                $size = random_int(30, 100);
                $rooms = random_int(2, 4);
                $floor = random_int(0, 8);
                break;
        }

        $bathrooms = $rooms > 3 ? random_int(2, 3) : 1;
        $hasElevator = $buildingType !== 'house' && $floor >= 3 ? 1 : (random_int(0, 100) > 60 ? 1 : 0);
        $hasBalcony = $buildingType !== 'house' ? (random_int(0, 100) > 30 ? 1 : 0) : 0;
        $parking = random_int(0, 100) > 50 ? 1 : 0;
        $garage = $buildingType === 'house' ? (random_int(0, 100) > 40 ? 1 : 0) : (random_int(0, 100) > 80 ? 1 : 0);
        $garden = $buildingType === 'house' ? (random_int(0, 100) > 20 ? 1 : 0) : (random_int(0, 100) > 90 ? 1 : 0);
        $furnished = random_int(0, 100) > 45 ? 1 : 0;
        $petsAllowed = random_int(0, 100) > 55 ? 1 : 0;
        $yearBuilt = random_int(1975, 2024);
        $availableDays = random_int(0, 60);
        $availableFrom = date('Y-m-d', strtotime("+{$availableDays} days"));

        $pricePerMeter = random_int($city['price_range'][0], $city['price_range'][1]);
        if ($buildingType === 'studio') {
            $pricePerMeter = (int)round($pricePerMeter * 1.15);
        } elseif ($buildingType === 'loft') {
            $pricePerMeter = (int)round($pricePerMeter * 1.1);
        } elseif ($buildingType === 'house') {
            $pricePerMeter = (int)round($pricePerMeter * 0.9);
        }

        $price = (int)round($size * $pricePerMeter);
        $price = max(1200, $price);

        $title = sprintf(
            '%s %d-pokojowe %s w %s',
            $adjective,
            $rooms,
            $buildingType === 'house' ? 'dom' : 'mieszkanie',
            $city['name']
        );

        $description = sprintf(
            '%s %s o powierzchni %d m². Lokalizacja: %s, ul. %s. Oferta %s. ' .
            'Udogodnienia: %s %s %s.',
            $adjective,
            $buildingType === 'house' ? 'dom' : 'mieszkanie',
            $size,
            $city['name'],
            $street,
            $highlight,
            $hasBalcony ? 'balkon,' : 'brak balkonu,',
            $hasElevator ? 'winda,' : 'bez windy,',
            $parking ? 'miejsce parkingowe' : 'parking na ulicy'
        );

        try {
            $stmt->execute([
                $userId,
                $title,
                $description,
                $city['name'],
                $street,
                $city['lat'],
                $city['lng'],
                $price,
                $size,
                $floor,
                $hasBalcony,
                $hasElevator,
                $buildingType,
                $rooms,
                $bathrooms,
                $parking,
                $garage,
                $garden,
                $furnished,
                $petsAllowed,
                $heatingType,
                $yearBuilt,
                $conditionType,
                $availableFrom,
            ]);
            $created++;
        } catch (PDOException $e) {
            error_log('Failed to generate offer: ' . $e->getMessage());
        }
    }

    return $created;
}

function getAiRecommendedOffers(int $userId, int $currentOfferId, int $limit = 3): array
{
    global $pdo;

    new UserPreferencesService($pdo);
    $historyStmt = $pdo->prepare(
        "SELECT o.size, o.rooms, o.city, o.building_type
         FROM user_offer_history h
         JOIN offers o ON h.offer_id = o.id
         WHERE h.user_id = ?
         ORDER BY h.created_at DESC
         LIMIT 50"
    );
    $historyStmt->execute([$userId]);
    $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($history)) {
        return [];
    }

    $sizes = array_filter(array_map('floatval', array_column($history, 'size')));
    $rooms = array_filter(array_map('intval', array_column($history, 'rooms')));
    $buildingCounts = [];
    $cityCounts = [];
    foreach ($history as $row) {
        if (!empty($row['building_type'])) {
            $buildingCounts[$row['building_type']] = ($buildingCounts[$row['building_type']] ?? 0) + 1;
        }
        if (!empty($row['city'])) {
            $cityCounts[$row['city']] = ($cityCounts[$row['city']] ?? 0) + 1;
        }
    }

    arsort($buildingCounts);
    arsort($cityCounts);
    $preferredBuilding = array_key_first($buildingCounts);
    $preferredCity = array_key_first($cityCounts);
    $avgSize = !empty($sizes) ? array_sum($sizes) / count($sizes) : null;
    $avgRooms = !empty($rooms) ? array_sum($rooms) / count($rooms) : null;

    $filters = [];
    $params = [$currentOfferId, $userId];

    if ($preferredBuilding) {
        $filters[] = "o.building_type = ?";
        $params[] = $preferredBuilding;
    }

    if ($avgSize) {
        $sizeMin = max(1, $avgSize * 0.8);
        $sizeMax = $avgSize * 1.2;
        $filters[] = "o.size BETWEEN ? AND ?";
        $params[] = $sizeMin;
        $params[] = $sizeMax;
    }

    if ($avgRooms) {
        $roomMin = max(1, floor($avgRooms - 1));
        $roomMax = ceil($avgRooms + 1);
        $filters[] = "o.rooms BETWEEN ? AND ?";
        $params[] = $roomMin;
        $params[] = $roomMax;
    }

    if ($preferredCity) {
        $filters[] = "o.city = ?";
        $params[] = $preferredCity;
    }

    $filterSql = $filters ? ' AND ' . implode(' AND ', $filters) : '';
    $limit = max(1, $limit);
    $orderBy = $avgSize ? "ABS(o.size - ?)" : "o.created_at DESC";
    if ($avgSize) {
        $params[] = $avgSize;
    }

    $query = "
        SELECT o.*, COALESCE(img.primary_image, img.first_image) AS primary_image
        FROM offers o
        LEFT JOIN (
            SELECT offer_id,
                   MAX(CASE WHEN is_primary = 1 THEN file_path END) AS primary_image,
                   MIN(file_path) AS first_image
            FROM images
            GROUP BY offer_id
        ) img ON o.id = img.offer_id
        WHERE o.id != ?
          AND o.user_id != ?
          {$filterSql}
        ORDER BY {$orderBy}
        LIMIT {$limit}";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($offers) >= $limit) {
        return $offers;
    }

    $fallbackLimit = $limit - count($offers);
    $fallbackQuery = "
        SELECT o.*, COALESCE(img.primary_image, img.first_image) AS primary_image
        FROM offers o
        LEFT JOIN (
            SELECT offer_id,
                   MAX(CASE WHEN is_primary = 1 THEN file_path END) AS primary_image,
                   MIN(file_path) AS first_image
            FROM images
            GROUP BY offer_id
        ) img ON o.id = img.offer_id
        WHERE o.id != ?
          AND o.user_id != ?
        ORDER BY o.created_at DESC
        LIMIT {$fallbackLimit}";
    $fallbackStmt = $pdo->prepare($fallbackQuery);
    $fallbackStmt->execute([$currentOfferId, $userId]);
    $fallbackOffers = $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);

    return array_merge($offers, $fallbackOffers);
}

function editOffer($offerId, $title, $description, $city, $street, $price, $size, $floor, $has_balcony, $has_elevator, $building_type, $rooms, $bathrooms, $parking, $garage, $garden, $furnished, $pets_allowed, $heating_type, $year_built, $condition_type, $available_from, $images, $primary_image_index) {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Unauthorized.');
        return;
    }
    global $pdo;

    // Validate inputs
    if (strlen($title) < 3 || strlen($title) > 100) {
        setFlashMessage('error', 'Title must be between 3 and 100 characters.');
        return;
    }
    if (empty($description)) {
        setFlashMessage('error', 'Description is required.');
        return;
    }
    if (strlen($city) < 3 || strlen($city) > 100) {
        setFlashMessage('error', 'City must be between 3 and 100 characters.');
        return;
    }
    if (strlen($street) < 3 || strlen($street) > 100) {
        setFlashMessage('error', 'Street must be between 3 and 100 characters.');
        return;
    }
    if (!is_numeric($price) || $price <= 0 || $price > 1000000 || $price != floor($price)) {
        setFlashMessage('error', 'Price must be a whole number between 1 and 1,000,000 PLN.');
        return;
    }
    if ($size <= 0 || $size > 10000) {
        setFlashMessage('error', 'Size must be between 0 and 10,000 m².');
        return;
    }
    if (!in_array($building_type, ['apartment', 'block', 'house'])) {
        setFlashMessage('error', 'Invalid building type.');
        return;
    }
    if ($rooms < 1 || $rooms > 50) {
        setFlashMessage('error', 'Rooms must be between 1 and 50.');
        return;
    }
    if ($bathrooms < 1 || $bathrooms > 20) {
        setFlashMessage('error', 'Bathrooms must be between 1 and 20.');
        return;
    }
    if (!in_array($heating_type, ['gas', 'electric', 'district', 'other'])) {
        setFlashMessage('error', 'Invalid heating type.');
        return;
    }
    if (!in_array($condition_type, ['new', 'renovated', 'used', 'to_renovate'])) {
        setFlashMessage('error', 'Invalid condition type.');
        return;
    }

    $isAdmin = isAdmin();
    if ($isAdmin) {
        $stmt = $pdo->prepare("SELECT user_id, city, street, lat, lng FROM offers WHERE id = ?");
        $stmt->execute([$offerId]);
    } else {
        $stmt = $pdo->prepare("SELECT user_id, city, street, lat, lng FROM offers WHERE id = ? AND user_id = ?");
        $stmt->execute([$offerId, $_SESSION['user_id']]);
    }
    $existingOffer = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existingOffer) {
        setFlashMessage('error', 'Offer not found or unauthorized.');
        return;
    }

    $existingCity = mb_strtolower(trim($existingOffer['city'] ?? ''));
    $existingStreet = mb_strtolower(trim($existingOffer['street'] ?? ''));
    $newCity = mb_strtolower(trim($city));
    $newStreet = mb_strtolower(trim($street));

    $lat = $existingOffer['lat'];
    $lng = $existingOffer['lng'];

    if ($existingCity !== $newCity || $existingStreet !== $newStreet || $lat === null || $lng === null) {
        // Geocode address using Nominatim
        $address = urlencode($city . ', ' . $street);
        $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ApartmentRentalApp/1.0 (your.email@example.com)');
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!empty($data)) {
            $lat = $data[0]['lat'];
            $lng = $data[0]['lon'];
        } else {
            $lat = null;
            $lng = null;
        }
    }

    // Update offer
    if ($isAdmin) {
        $stmt = $pdo->prepare("UPDATE offers SET title = ?, description = ?, city = ?, street = ?, lat = ?, lng = ?, price = ?, size = ?, floor = ?, has_balcony = ?, has_elevator = ?, building_type = ?, rooms = ?, bathrooms = ?, parking = ?, garage = ?, garden = ?, furnished = ?, pets_allowed = ?, heating_type = ?, year_built = ?, condition_type = ?, available_from = ? WHERE id = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE offers SET title = ?, description = ?, city = ?, street = ?, lat = ?, lng = ?, price = ?, size = ?, floor = ?, has_balcony = ?, has_elevator = ?, building_type = ?, rooms = ?, bathrooms = ?, parking = ?, garage = ?, garden = ?, furnished = ?, pets_allowed = ?, heating_type = ?, year_built = ?, condition_type = ?, available_from = ? WHERE id = ? AND user_id = ?");
    }
    try {
        $params = [$title, $description, $city, $street, $lat, $lng, $price, $size, $floor, $has_balcony, $has_elevator, $building_type, $rooms, $bathrooms, $parking, $garage, $garden, $furnished, $pets_allowed, $heating_type, $year_built, $condition_type, $available_from, $offerId];
        if (!$isAdmin) {
            $params[] = $_SESSION['user_id'];
        }
        $stmt->execute($params);

        // Handle image uploads
        if (!empty($images['name'][0])) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $image_count = count(array_filter($images['name']));
            if ($image_count > 5) {
                setFlashMessage('error', 'Maximum 5 images allowed.');
                return;
            }
            $stmt = $pdo->prepare("DELETE FROM images WHERE offer_id = ?");
            $stmt->execute([$offerId]);

            foreach ($images['name'] as $index => $name) {
                if ($images['error'][$index] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        setFlashMessage('error', 'Only JPEG or PNG images allowed.');
                        return;
                    }
                    if ($images['size'][$index] > 5 * 1024 * 1024) {
                        setFlashMessage('error', 'Image size must be less than 5MB.');
                        return;
                    }
                    $filename = uniqid() . '.' . $ext;
                    $destination = $upload_dir . $filename;
                    if (move_uploaded_file($images['tmp_name'][$index], $destination)) {
                        $is_primary = ($index == $primary_image_index) ? 1 : 0;
                        $stmt = $pdo->prepare("INSERT INTO images (offer_id, file_path, is_primary) VALUES (?, ?, ?)");
                        $stmt->execute([$offerId, $destination, $is_primary]);
                    } else {
                        setFlashMessage('error', 'Failed to upload image.');
                        return;
                    }
                }
            }
        }

        setFlashMessage('success', 'Offer updated successfully.');
        $redirectAction = $isAdmin ? 'admin_dashboard' : 'dashboard';
        header("Location: index.php?action=" . $redirectAction);
    } catch (PDOException $e) {
        setFlashMessage('error', 'Failed to edit offer: ' . $e->getMessage());
    }
}

function deleteOffer($offerId) {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Unauthorized.');
        return;
    }
    global $pdo;

    ensureReportsTable();

    $stmt = $pdo->prepare("DELETE FROM offers WHERE id = ? AND user_id = ?");
    try {
        $pdo->beginTransaction();
        
        // Delete associated images from filesystem
        // $stmt_images = $pdo->prepare("SELECT file_path FROM images WHERE id = ?");
        // $stmt_images->execute([$offerId]);
        // $images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);
        // foreach ($images as $image) {
        //     if (file_exists($image['file_path'])) {
        //         unlink($image['file_path']);
        //     }
        // }

        // Delete images from database
        $stmt_delete_images = $pdo->prepare("DELETE FROM images WHERE offer_id = ?");
        $stmt_delete_images->execute([$offerId]);

        // Delete favorites
        $stmt_delete_favorites = $pdo->prepare("DELETE FROM favorites WHERE offer_id = ?");
        $stmt_delete_favorites->execute([$offerId]);

        // Delete messages
        $stmt_delete_messages = $pdo->prepare("DELETE FROM messages WHERE offer_id = ?");
        $stmt_delete_messages->execute([$offerId]);

        // Delete reports
        $stmt_delete_reports = $pdo->prepare("DELETE FROM reports WHERE offer_id = ?");
        $stmt_delete_reports->execute([$offerId]);

        // Delete the offer
        $stmt->execute([$offerId, $_SESSION['user_id']]);
        
        $pdo->commit();
        setFlashMessage('success', 'Offer deleted successfully.');
        header("Location: index.php?action=dashboard");
    } catch (PDOException $e) {
        $pdo->rollBack();
        setFlashMessage('error', 'Failed to delete offer: ' . $e->getMessage());
    }
}

function toggleFavorite($userId, $offerId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND offer_id = ?");
    $stmt->execute([$userId, $offerId]);
    $favorite = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($favorite) {
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND offer_id = ?");
        $stmt->execute([$userId, $offerId]);
        return false; // Unfavorited
    } else {
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, offer_id) VALUES (?, ?)");
        $stmt->execute([$userId, $offerId]);

        try {
            $preferences = new UserPreferencesService($pdo);
            $preferences->recordAction((int)$userId, (int)$offerId, 'favorite');
        } catch (Throwable $e) {
            error_log('Could not record favorite in user history: ' . $e->getMessage());
        }

        return true; // Favorited
    }
}

function getUserFavorites($userId, $page = 1, $perPage = 10) {
    global $pdo;
    ensureOfferViewsTable();
    $offset = ($page - 1) * $perPage;
    $stmt = $pdo->prepare("
        SELECT o.*, COALESCE(img.primary_image, img.first_image) AS primary_image, COALESCE(v.views_last_24h, 0) AS views_last_24h
        FROM favorites f
        JOIN offers o ON f.offer_id = o.id
        LEFT JOIN (
            SELECT offer_id,
                   MAX(CASE WHEN is_primary = 1 THEN file_path END) AS primary_image,
                   MIN(file_path) AS first_image
            FROM images
            GROUP BY offer_id
        ) img ON o.id = img.offer_id
        LEFT JOIN (
            SELECT offer_id, COUNT(*) AS views_last_24h
            FROM offer_views
            WHERE viewed_at >= (NOW() - INTERVAL 24 HOUR)
            GROUP BY offer_id
        ) v ON o.id = v.offer_id
        WHERE f.user_id = ?
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $valid_offers = [];
    foreach ($offers as $offer) {
        if (is_array($offer) && isset($offer['id'], $offer['title'], $offer['city'], $offer['street'], $offer['price'], $offer['size'])) {
            $valid_offers[] = $offer;
        }
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
    $stmt->execute([$userId]);
    $total = $stmt->fetchColumn();

    return ['offers' => $valid_offers, 'total' => $total];
}

function isFavorite($userId, $offerId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND offer_id = ?");
    $stmt->execute([$userId, $offerId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function sendMessage($receiver_id, $offerId, $message) {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Unauthorized.');
        return;
    }
    global $pdo;

    // Validate inputs
    if (empty($message) || strlen($message) > 1000) {
        setFlashMessage('error', 'Message must be between 1 and 1000 characters.');
        return;
    }
    if (!is_numeric($receiver_id) || !is_numeric($offerId)) {
        setFlashMessage('error', 'Invalid receiver or offer ID.');
        return;
    }

    // Verify offer exists and get owner details
    $stmt = $pdo->prepare("SELECT o.user_id, o.title, u.email AS owner_email, u.username AS owner_username FROM offers o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$offerId]);
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$offer) {
        setFlashMessage('error', 'Invalid offer ID.');
        return;
    }

    // Prevent messaging self
    if ($receiver_id == $_SESSION['user_id']) {
        setFlashMessage('error', 'You cannot message yourself.');
        return;
    }

    // Verify receiver exists
    $stmt = $pdo->prepare("SELECT id, email, username FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    $receiver = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$receiver) {
        setFlashMessage('error', 'Invalid recipient.');
        return;
    }

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, offer_id, message, is_read) VALUES (?, ?, ?, ?, 0)");
    try {
        $stmt->execute([$_SESSION['user_id'], $receiver_id, $offerId, $message]);

        if ((int)$offer['user_id'] === (int)$receiver_id && !empty($receiver['email'])) {
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $threadLink = "http://$host/index.php?action=dashboard&offer_id=$offerId&receiver_id=" . (int)$_SESSION['user_id'];
            $emailSubject = 'Nowa wiadomość dotycząca Twojego ogłoszenia';
            $emailBody = "Cześć " . ($offer['owner_username'] ?? '') . ",\n\n" .
                "Otrzymałeś nową wiadomość w sprawie ogłoszenia: " . ($offer['title'] ?? 'Twoja oferta') . ".\n" .
                "Treść wiadomości:\n" . trim($message) . "\n\n" .
                "Odpowiedz bezpośrednio w panelu: $threadLink\n\n" .
                "Pozdrawiamy,\nZespół Luxury Apartments";
            sendSystemEmail($receiver['email'], $emailSubject, $emailBody, 'messages');
        }

        setFlashMessage('success', 'Message sent successfully.');
        header("Location: index.php?action=dashboard");
    } catch (PDOException $e) {
        setFlashMessage('error', 'Failed to send message: ' . $e->getMessage());
    }
}

function markMessagesAsRead($userId, $offerId, $otherUserId) {
    if (!isLoggedIn()) {
        error_log("markMessagesAsRead: Unauthorized access attempt");
        return false;
    }
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET is_read = 1 
            WHERE offer_id = ? 
            AND receiver_id = ? 
            AND sender_id = ? 
            AND is_read = 0
        ");
        $stmt->execute([$offerId, $userId, $otherUserId]);
        $affected_rows = $stmt->rowCount();
        error_log("markMessagesAsRead: Updated $affected_rows messages for user $userId, offer $offerId, sender $otherUserId");
        return true;
    } catch (PDOException $e) {
        error_log("markMessagesAsRead: Error - " . $e->getMessage());
        return false;
    }
}

// Admin-specific functions
function getAllUsers() {
    global $pdo;
    ensureUserPhoneColumn();
    $stmt = $pdo->query("SELECT id, username, email, role, phone, created_at FROM users ORDER BY id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserForAdmin($userId) {
    if (!isAdmin()) {
        setFlashMessage('error', 'Unauthorized.');
        return null;
    }
    global $pdo;
    ensureUserPhoneColumn();
    $stmt = $pdo->prepare("SELECT id, username, email, role, phone, is_verified, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateUserAdmin($userId, $username, $email, $countryCode, $phoneNumber, $role, $isVerified): void {
    if (!isAdmin()) {
        setFlashMessage('error', 'Unauthorized.');
        return;
    }
    global $pdo;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('error', 'Invalid email format.');
        return;
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        setFlashMessage('error', 'Username can only contain letters, numbers, and underscores.');
        return;
    }
    $phoneData = buildPhoneNumber((string)$countryCode, (string)$phoneNumber);
    if ($phoneData['error']) {
        setFlashMessage('error', $phoneData['error']);
        return;
    }
    $phone = $phoneData['phone'];
    if (!in_array($role, ['user', 'admin'], true)) {
        setFlashMessage('error', 'Invalid role value.');
        return;
    }

    ensureUserPhoneColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetchColumn() > 0) {
        setFlashMessage('error', 'Adres email jest już zajęty.');
        return;
    }

    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ?, role = ?, is_verified = ? WHERE id = ?");
    try {
        $stmt->execute([$username, $email, $phone, $role, $isVerified ? 1 : 0, $userId]);
        setFlashMessage('success', 'Konto użytkownika zostało zaktualizowane.');
    } catch (PDOException $e) {
        setFlashMessage('error', 'Failed to update user: ' . $e->getMessage());
    }
}

function deleteUser($userId) {
    if (!isAdmin()) {
        setFlashMessage('error', 'Unauthorized.');
        return;
    }
    global $pdo;
    try {
        $pdo->beginTransaction();

        // Delete user's offers
        $stmt_offers = $pdo->prepare("SELECT id FROM offers WHERE user_id = ?");
        $stmt_offers->execute([$userId]);
        $offers = $stmt_offers->fetchAll(PDO::FETCH_ASSOC);
        foreach ($offers as $offer) {
            deleteOfferAdmin($offer['id']);
        }

        // Delete user's favorites
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Delete user's messages
        $stmt = $pdo->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
        $stmt->execute([$userId, $userId]);

        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$userId]);

        $pdo->commit();
        setFlashMessage('success', 'User deleted successfully.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        setFlashMessage('error', 'Failed to delete user: ' . $e->getMessage());
    }
}

function getAllOffers() {
    global $pdo;
    ensureOfferStatusColumn();
    $stmt = $pdo->query("SELECT o.id, o.title, o.city, o.price, o.status, o.created_at, o.visits, u.username AS owner_username FROM offers o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteOfferAdmin($offerId) {
    if (!isAdmin()) {
        setFlashMessage('error', 'Unauthorized.');
        return;
    }
    global $pdo;
    ensureReportsTable();
    try {
        $pdo->beginTransaction();

        // Delete images from database
        $stmt_delete_images = $pdo->prepare("DELETE FROM images WHERE offer_id = ?");
        $stmt_delete_images->execute([$offerId]);

        // Delete favorites
        $stmt_delete_favorites = $pdo->prepare("DELETE FROM favorites WHERE offer_id = ?");
        $stmt_delete_favorites->execute([$offerId]);

        // Delete messages
        $stmt_delete_messages = $pdo->prepare("DELETE FROM messages WHERE offer_id = ?");
        $stmt_delete_messages->execute([$offerId]);

        // Delete reports
        $stmt_delete_reports = $pdo->prepare("DELETE FROM reports WHERE offer_id = ?");
        $stmt_delete_reports->execute([$offerId]);

        // Delete offer
        $stmt = $pdo->prepare("DELETE FROM offers WHERE id = ?");
        $stmt->execute([$offerId]);

        $pdo->commit();
        setFlashMessage('success', 'Offer deleted successfully.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        setFlashMessage('error', 'Failed to delete offer: ' . $e->getMessage());
    }
}

function getAllMessages() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT m.id, u_sender.username AS sender_username, u_receiver.username AS receiver_username, o.title AS offer_title, m.message, m.sent_at AS created_at
        FROM messages m
        JOIN users u_sender ON m.sender_id = u_sender.id
        JOIN users u_receiver ON m.receiver_id = u_receiver.id
        JOIN offers o ON m.offer_id = o.id
        ORDER BY m.sent_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteMessage($messageId) {
    if (!isAdmin()) {
        setFlashMessage('error', 'Unauthorized.');
        return;
    }
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$messageId]);
        setFlashMessage('success', 'Message deleted successfully.');
    } catch (PDOException $e) {
        setFlashMessage('error', 'Failed to delete message: ' . $e->getMessage());
    }
}

function updateOfferStatus($offerId, $status, $adminId = null): bool
{
    if (!isAdmin()) {
        setFlashMessage('error', 'Unauthorized.');
        return false;
    }

    $allowedStatuses = ['active', 'pending', 'inactive', 'archived', 'suspended'];
    if (!in_array($status, $allowedStatuses, true)) {
        setFlashMessage('error', 'Invalid offer status value.');
        return false;
    }

    global $pdo;
    ensureOfferStatusColumn();

    $stmt = $pdo->prepare("UPDATE offers SET status = ?, status_updated_at = NOW() WHERE id = ?");
    try {
        $stmt->execute([$status, $offerId]);
        if ($stmt->rowCount() === 0) {
            setFlashMessage('error', 'Offer not found or status unchanged.');
            return false;
        }

        $ownerStmt = $pdo->prepare("SELECT u.email, u.username, o.title FROM offers o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $ownerStmt->execute([$offerId]);
        $owner = $ownerStmt->fetch(PDO::FETCH_ASSOC);
        if ($owner && !empty($owner['email'])) {
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $dashboardLink = "http://$host/index.php?action=dashboard&offer_id=" . (int)$offerId;
            $subject = 'Aktualizacja statusu Twojego ogłoszenia';
            $body = "Cześć " . ($owner['username'] ?? '') . ",\n\n" .
                "Status ogłoszenia \"" . ($owner['title'] ?? 'Twoja oferta') . "\" został zmieniony na: " . strtoupper($status) . ".\n" .
                "Sprawdź szczegóły i historię zmian w panelu: $dashboardLink\n\n" .
                "Pozdrawiamy,\nZespół Luxury Apartments";
            sendSystemEmail($owner['email'], $subject, $body, 'status_change');
        }

        if ($adminId) {
            $historyStmt = $pdo->prepare("INSERT INTO offer_status_history (offer_id, status, changed_by, changed_at) VALUES (?, ?, ?, NOW())");
            try {
                $historyStmt->execute([$offerId, $status, $adminId]);
            } catch (PDOException $ignored) {
                // History table is optional. Ignore if it does not exist.
            }
        }

        setFlashMessage('success', 'Offer status updated.');
        return true;
    } catch (PDOException $e) {
        setFlashMessage('error', 'Failed to update status: ' . $e->getMessage());
        return false;
    }
}

function getUserStatistics($userId): array
{
    global $pdo;
    ensureOfferStatusColumn();
    ensureReportsTable();

    $stats = [
        'active_offers' => 0,
        'inactive_offers' => 0,
        'favorites' => 0,
        'unread_messages' => 0,
        'total_views' => 0,
        'pending_reports' => 0
    ];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM offers WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$userId]);
    $stats['active_offers'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM offers WHERE user_id = ? AND status != 'active'");
    $stmt->execute([$userId]);
    $stats['inactive_offers'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats['favorites'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    $stats['unread_messages'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(visits), 0) FROM offers WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats['total_views'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE reporter_id = ? AND status IN ('pending', 'in_review')");
    $stmt->execute([$userId]);
    $stats['pending_reports'] = (int)$stmt->fetchColumn();

    return $stats;
}

function getPlatformStatistics(): array
{
    global $pdo;
    ensureOfferStatusColumn();
    ensureReportsTable();

    $stats = [
        'active_offers' => (int)$pdo->query("SELECT COUNT(*) FROM offers WHERE status = 'active'")->fetchColumn(),
        'pending_offers' => (int)$pdo->query("SELECT COUNT(*) FROM offers WHERE status != 'active'")->fetchColumn(),
        'pending_reports' => (int)$pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn(),
        'messages_week' => (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn(),
        'favorites_total' => (int)$pdo->query("SELECT COUNT(*) FROM favorites")->fetchColumn(),
        'new_users_week' => 0
    ];

    if (columnExists('users', 'created_at')) {
        $stats['new_users_week'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
    }

    return $stats;
}

function reportOffer($offerId, $reporterId, $reason): void
{
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Musisz być zalogowany, aby zgłosić ofertę.');
        return;
    }

    $reason = trim($reason);
    if (strlen($reason) < 10) {
        setFlashMessage('error', 'Uzasadnienie musi mieć co najmniej 10 znaków.');
        return;
    }

    global $pdo;
    ensureReportsTable();

    $stmt = $pdo->prepare("SELECT user_id, title FROM offers WHERE id = ?");
    $stmt->execute([$offerId]);
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$offer) {
        setFlashMessage('error', 'Ogłoszenie nie istnieje.');
        return;
    }

    if ((int)$offer['user_id'] === (int)$reporterId) {
        setFlashMessage('error', 'Nie możesz zgłosić własnego ogłoszenia.');
        return;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE offer_id = ? AND reporter_id = ? AND status IN ('pending', 'in_review')");
    $stmt->execute([$offerId, $reporterId]);
    if ((int)$stmt->fetchColumn() > 0) {
        setFlashMessage('error', 'To ogłoszenie zostało już zgłoszone i jest w trakcie weryfikacji.');
        return;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO reports (offer_id, reporter_id, reason) VALUES (?, ?, ?)");
        $stmt->execute([$offerId, $reporterId, $reason]);
        setFlashMessage('success', 'Dziękujemy za zgłoszenie. Administratorzy przyjrzą się sprawie.');
    } catch (PDOException $e) {
        setFlashMessage('error', 'Nie udało się zapisać zgłoszenia: ' . $e->getMessage());
    }
}

function getReports(?string $statusFilter = null): array
{
    global $pdo;
    ensureReportsTable();

    $query = "SELECT r.*, o.title AS offer_title, u_owner.username AS owner_username, u_reporter.username AS reporter_username, u_admin.username AS handled_by_username FROM reports r JOIN offers o ON r.offer_id = o.id JOIN users u_owner ON o.user_id = u_owner.id JOIN users u_reporter ON r.reporter_id = u_reporter.id LEFT JOIN users u_admin ON r.handled_by = u_admin.id";
    $params = [];
    if ($statusFilter) {
        $query .= " WHERE r.status = ?";
        $params[] = $statusFilter;
    }
    $query .= " ORDER BY r.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateReportStatus($reportId, $status, $adminId, string $note = ''): bool
{
    if (!isAdmin()) {
        setFlashMessage('error', 'Unauthorized.');
        return false;
    }

    $allowed = ['pending', 'in_review', 'resolved'];
    if (!in_array($status, $allowed, true)) {
        setFlashMessage('error', 'Nieprawidłowy status zgłoszenia.');
        return false;
    }

    global $pdo;
    ensureReportsTable();

    $stmt = $pdo->prepare("UPDATE reports SET status = ?, admin_note = ?, handled_by = ?, updated_at = NOW() WHERE id = ?");
    try {
        $stmt->execute([$status, $note !== '' ? $note : null, $adminId, $reportId]);
        if ($stmt->rowCount() === 0) {
            setFlashMessage('error', 'Zgłoszenie nie istnieje lub nie zmieniono statusu.');
            return false;
        }

        if ($status === 'resolved') {
            $detailsStmt = $pdo->prepare("SELECT r.reason, r.admin_note, u.email, u.username, o.title FROM reports r JOIN users u ON r.reporter_id = u.id JOIN offers o ON r.offer_id = o.id WHERE r.id = ?");
            $detailsStmt->execute([$reportId]);
            $details = $detailsStmt->fetch(PDO::FETCH_ASSOC);
            if ($details && !empty($details['email'])) {
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $link = "http://$host/index.php?action=dashboard";
                $subject = 'Aktualizacja zgłoszenia oferty';
                $body = "Cześć " . ($details['username'] ?? '') . ",\n\n" .
                    "Twoje zgłoszenie dotyczące ogłoszenia \"" . ($details['title'] ?? '') . "\" zostało rozpatrzone.\n" .
                    "Notatka administratora: " . ($details['admin_note'] ?? 'brak dodatkowych informacji') . "\n\n" .
                    "Dziękujemy za dbanie o jakość ogłoszeń.\n\n" .
                    "Panel użytkownika: $link";
                sendSystemEmail($details['email'], $subject, $body, 'moderation');
            }
        }

        setFlashMessage('success', 'Status zgłoszenia został zaktualizowany.');
        return true;
    } catch (PDOException $e) {
        setFlashMessage('error', 'Nie udało się zaktualizować zgłoszenia: ' . $e->getMessage());
        return false;
    }
}
?>
