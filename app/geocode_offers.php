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
}
?>