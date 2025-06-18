<?php
require_once 'config.php';

global $pdo;
$stmt = $pdo->query("SELECT id, city, street FROM offers WHERE lat IS NULL OR lng IS NULL");
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($offers as $offer) {
    $address = urlencode($offer['city'] . ', ' . $offer['street']);
    $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ApartmentRentalApp/1.0 (fabian.szkudlarski@gmail.com)');
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (!empty($data)) {
        $lat = $data[0]['lat'];
        $lng = $data[0]['lon'];
        
        $update_stmt = $pdo->prepare("UPDATE offers SET lat = ?, lng = ? WHERE id = ?");
        $update_stmt->execute([$lat, $lng, $offer['id']]);
        echo "Updated offer ID {$offer['id']} with lat: {$lat}, lng: {$lng}\n";
    } else {
        echo "Failed to geocode offer ID {$offer['id']}\n";
    }
    sleep(1);
    function searchOffers($userLat, $userLng, $maxDistance, $filters = []) {
    global $pdo;
    
    // Podstawowe zapytanie
    $sql = "SELECT *, 
            (6371 * ACOS(
                COS(RADIANS(:user_lat)) * COS(RADIANS(lat)) * 
                COS(RADIANS(lng) - RADIANS(:user_lng)) + 
                SIN(RADIANS(:user_lat)) * SIN(RADIANS(lat))
            ) AS distance
            FROM offers
            WHERE lat IS NOT NULL AND lng IS NOT NULL";
    
    // Filtry
    $params = [
        ':user_lat' => $userLat,
        ':user_lng' => $userLng
    ];
    
    if (!empty($filters['price_min'])) {
        $sql .= " AND price >= :price_min";
        $params[':price_min'] = $filters['price_min'];
    }
    
    if (!empty($filters['price_max'])) {
        $sql .= " AND price <= :price_max";
        $params[':price_max'] = $filters['price_max'];
    }
    
    // Dodaj inne filtry według potrzeb...
    
    // Filtrowanie po odległości
    $sql .= " HAVING distance <= :max_distance ORDER BY distance";
    $params[':max_distance'] = $maxDistance;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
?>