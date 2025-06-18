<?php
require_once 'config.php';
require_once 'auth.php';

function addOffer($title, $description, $city, $street, $price, $size, $floor, $has_balcony, $has_elevator, $building_type, $rooms, $bathrooms, $parking, $garage, $garden, $furnished, $pets_allowed, $heating_type, $year_built, $condition_type, $available_from, $images, $primary_image_index) {
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
    } else {
        setFlashMessage('error', 'Failed to geocode address.');
        return;
    }

    // Insert offer
    $stmt = $pdo->prepare("INSERT INTO offers (user_id, title, description, city, street, lat, lng, price, size, floor, has_balcony, has_elevator, building_type, rooms, bathrooms, parking, garage, garden, furnished, pets_allowed, heating_type, year_built, condition_type, available_from) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$_SESSION['user_id'], $title, $description, $city, $street, $lat, $lng, $price, $size, $floor, $has_balcony, $has_elevator, $building_type, $rooms, $bathrooms, $parking, $garage, $garden, $furnished, $pets_allowed, $heating_type, $year_built, $condition_type, $available_from]);
        $offer_id = $pdo->lastInsertId();

        // Handle image uploads
        if (!empty($images['name'][0])) {
            $upload_dir = 'Uploads/';
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

        setFlashMessage('success', 'Offer added successfully.');
        header("Location: index.php");
    } catch (PDOException $e) {
        setFlashMessage('error', 'Failed to add offer: ' . $e->getMessage());
    }
}

function searchOffers($filters, $page = 1, $perPage = 10) {
    global $pdo;
    $offset = ($page - 1) * $perPage;
    $query = "SELECT o.*, i.file_path AS primary_image";
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
               LEFT JOIN images i ON o.id = i.offer_id AND i.is_primary = 1 
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
        case 'visits_desc':
            $query .= " ORDER BY o.visits DESC";
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

function getUserOffers($userId, $page = 1, $perPage = 10) {
    global $pdo;
    $offset = ($page - 1) * $perPage;
    $stmt = $pdo->prepare("SELECT o.*, i.file_path AS primary_image 
                           FROM offers o 
                           LEFT JOIN images i ON o.id = i.offer_id AND i.is_primary = 1 
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
    $stmt = $pdo->prepare("
        SELECT o.*, u.username AS owner_username
        FROM offers o
        JOIN users u ON o.user_id = u.id
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
    $lat = null;
    $lng = null;
    if (!empty($data)) {
        $lat = $data[0]['lat'];
        $lng = $data[0]['lon'];
    } else {
        setFlashMessage('error', 'Failed to geocode address.');
        return;
    }

    // Update offer
    $stmt = $pdo->prepare("UPDATE offers SET title = ?, description = ?, city = ?, street = ?, lat = ?, lng = ?, price = ?, size = ?, floor = ?, has_balcony = ?, has_elevator = ?, building_type = ?, rooms = ?, bathrooms = ?, parking = ?, garage = ?, garden = ?, furnished = ?, pets_allowed = ?, heating_type = ?, year_built = ?, condition_type = ?, available_from = ? WHERE id = ? AND user_id = ?");
    try {
        $stmt->execute([$title, $description, $city, $street, $lat, $lng, $price, $size, $floor, $has_balcony, $has_elevator, $building_type, $rooms, $bathrooms, $parking, $garage, $garden, $furnished, $pets_allowed, $heating_type, $year_built, $condition_type, $available_from, $offerId, $_SESSION['user_id']]);

        // Handle image uploads
        if (!empty($images['name'][0])) {
            $upload_dir = 'Uploads/';
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
        header("Location: index.php?action=dashboard");
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

    $stmt = $pdo->prepare("DELETE FROM offers WHERE id = ? AND user_id = ?");
    try {
        $stmt->beginTransaction();
        
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

        // Delete the offer
        $stmt->execute([$offerId, $_SESSION['user_id']]);
        
        $stmt->commit();
        setFlashMessage('success', 'Offer deleted successfully.');
        header("Location: index.php?action=dashboard");
    } catch (PDOException $e) {
        $stmt->rollBack();
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
        return true; // Favorited
    }
}

function getUserFavorites($userId, $page = 1, $perPage = 10) {
    global $pdo;
    $offset = ($page - 1) * $perPage;
    $stmt = $pdo->prepare("
        SELECT o.*, i.file_path AS primary_image
        FROM favorites f
        JOIN offers o ON f.offer_id = o.id
        LEFT JOIN images i ON o.id = i.offer_id
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

    // Verify offer exists and get owner
    $stmt = $pdo->prepare("SELECT user_id FROM offers WHERE id = ?");
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
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    if (!$stmt->fetch()) {
        setFlashMessage('error', 'Invalid recipient.');
        return;
    }

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, offer_id, message, is_read) VALUES (?, ?, ?, ?, 0)");
    try {
        $stmt->execute([$_SESSION['user_id'], $receiver_id, $offerId, $message]);
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
    $stmt = $pdo->query("SELECT id, username, email, role FROM users ORDER BY id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    $stmt = $pdo->query("SELECT o.id, o.title, o.city, o.price FROM offers o ORDER BY o.created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteOfferAdmin($offerId) {
    if (!isAdmin()) {
        setFlashMessage('error', 'Unauthorized.');
        return;
    }
    global $pdo;
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
        SELECT m.id, u_sender.username AS sender_username, u_receiver.username AS receiver_username, o.title AS offer_title, m.message
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
?>