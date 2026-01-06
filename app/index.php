<?php
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'offers.php';
require_once __DIR__ . '/AI/LLMClient.php';
require_once __DIR__ . '/AI/AISortingService.php';
require_once __DIR__ . '/AI/UserPreferencesService.php';

use App\AI\AISortingService;
use App\AI\LLMClient;
use App\AI\UserPreferencesService;

$action = isset($_GET['action']) ? $_GET['action'] : 'home';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Validate CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Invalid CSRF token.');
    header("Location: index.php");
    exit;
}

switch ($action) {
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            register($_POST['username'], $_POST['email'], $_POST['password'], $_POST['phone'] ?? '');
        }
        include 'views/register.php';
        break;
    case 'verify':
        if (isset($_GET['token'])) {
            verifyAccount($_GET['token']);
        } else {
            setFlashMessage('error', 'Brak tokenu weryfikacyjnego.');
            header("Location: index.php?action=login");
        }
        break;
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            login($_POST['email'], $_POST['password']);
        }
        include 'views/login.php';
        break;
    case 'logout':
        logout();
        break;
    case 'add_offer':
        if (!isLoggedIn()) {
            setFlashMessage('error', 'Please log in to add an offer.');
            header("Location: index.php?action=login");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            addOffer(
                $_POST['title'],
                $_POST['description'],
                $_POST['city'],
                $_POST['street'],
                $_POST['price'],
                $_POST['size'],
                $_POST['floor'] ?? null,
                isset($_POST['has_balcony']) ? 1 : 0,
                isset($_POST['has_elevator']) ? 1 : 0,
                $_POST['building_type'],
                $_POST['rooms'],
                $_POST['bathrooms'],
                isset($_POST['parking']) ? 1 : 0,
                isset($_POST['garage']) ? 1 : 0,
                isset($_POST['garden']) ? 1 : 0,
                isset($_POST['furnished']) ? 1 : 0,
                isset($_POST['pets_allowed']) ? 1 : 0,
                $_POST['heating_type'],
                $_POST['year_built'] ?? null,
                $_POST['condition_type'],
                $_POST['available_from'] ?? null,
                $_FILES['images'] ?? [],
                $_POST['primary_image'] ?? 0
            );
        }
        include 'views/add_offer.php';
        break;
    case 'generate_offers':
        if (!isLoggedIn()) {
            setFlashMessage('error', 'Please log in to generate offers.');
            header("Location: index.php?action=login");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestedCount = isset($_POST['offer_count']) ? (int)$_POST['offer_count'] : 0;
            if ($requestedCount < 1 || $requestedCount > 50) {
                setFlashMessage('error', 'Podaj liczbę ofert od 1 do 50.');
                header("Location: index.php?action=dashboard");
                exit;
            }

            $created = generateRandomOffers($requestedCount, (int)$_SESSION['user_id']);
            if ($created > 0) {
                setFlashMessage('success', "Dodano {$created} nowych ofert.");
            } else {
                setFlashMessage('error', 'Nie udało się dodać nowych ofert.');
            }
        }
        header("Location: index.php?action=dashboard");
        exit;
    case 'edit_offer':
        if (!isLoggedIn()) {
            setFlashMessage('error', 'Please log in to edit an offer.');
            header("Location: index.php?action=login");
            exit;
        }
        if (!isset($_GET['offer_id'])) {
            setFlashMessage('error', 'No offer ID provided.');
            header("Location: index.php?action=dashboard");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            editOffer(
                $_GET['offer_id'],
                $_POST['title'],
                $_POST['description'],
                $_POST['city'],
                $_POST['street'],
                $_POST['price'],
                $_POST['size'],
                $_POST['floor'] ?? null,
                isset($_POST['has_balcony']) ? 1 : 0,
                isset($_POST['has_elevator']) ? 1 : 0,
                $_POST['building_type'],
                $_POST['rooms'],
                $_POST['bathrooms'],
                isset($_POST['parking']) ? 1 : 0,
                isset($_POST['garage']) ? 1 : 0,
                isset($_POST['garden']) ? 1 : 0,
                isset($_POST['furnished']) ? 1 : 0,
                isset($_POST['pets_allowed']) ? 1 : 0,
                $_POST['heating_type'],
                $_POST['year_built'] ?? null,
                $_POST['condition_type'],
                $_POST['available_from'] ?? null,
                $_FILES['images'] ?? [],
                $_POST['primary_image'] ?? 0
            );
        }
        include 'views/edit_offer.php';
        break;
    case 'delete_offer':
        if (!isLoggedIn()) {
            setFlashMessage('error', 'Please log in to delete an offer.');
            header("Location: index.php?action=login");
            exit;
        }
        if (!isset($_GET['offer_id'])) {
            setFlashMessage('error', 'No offer ID provided.');
            header("Location: index.php?action=dashboard");
            exit;
        }
        deleteOffer($_GET['offer_id']);
        break;
    case 'toggle_favorite':
        if (!isLoggedIn() || !isset($_GET['offer_id'])) {
            setFlashMessage('error', 'Unauthorized or invalid offer ID.');
            header("Location: index.php?action=search");
            exit;
        }
        $is_favorited = toggleFavorite($_SESSION['user_id'], $_GET['offer_id']);
        setFlashMessage('success', $is_favorited ? 'Added to favorites.' : 'Removed from favorites.');
        header("Location: index.php?action=view_offer&offer_id=" . (int)$_GET['offer_id']);
        break;
    case 'search':
        $perPage = 10;
        $filters = [
            'city' => $_GET['city'] ?? '',
            'street' => $_GET['street'] ?? '',
            'distance_km' => $_GET['distance_km'] ?? '',
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? '',
            'min_size' => $_GET['min_size'] ?? '',
            'min_floor' => $_GET['min_floor'] ?? '',
            'max_floor' => $_GET['max_floor'] ?? '',
            'has_balcony' => $_GET['has_balcony'] ?? '',
            'has_elevator' => $_GET['has_elevator'] ?? '',
            'building_type' => $_GET['building_type'] ?? '',
            'min_rooms' => $_GET['min_rooms'] ?? '',
            'min_bathrooms' => $_GET['min_bathrooms'] ?? '',
            'parking' => $_GET['parking'] ?? '',
            'furnished' => $_GET['furnished'] ?? '',
            'sort' => $_GET['sort'] ?? 'date_desc'
        ];
        $result = searchOffers($filters, $page, $perPage);
        $offers = $result['offers'];
        $totalOffers = $result['total'];
        $totalPages = (int)ceil($totalOffers / $perPage);
        $mapOffers = searchOffersMapData($filters);

        $aiFilters = $_SESSION['ai_filters'] ?? [];
        $aiWeights = $_SESSION['ai_weights'] ?? [];

        if (in_array($filters['sort'], ['ai', 'ai_personalized'], true)) {
            try {
                $llmClient = new LLMClient();
                $sortingService = new AISortingService($llmClient);
                $userPreferences = [];

                if ($filters['sort'] === 'ai_personalized' && isset($_SESSION['user_id'])) {
                    $preferencesService = new UserPreferencesService($pdo);
                    $userPreferences = $preferencesService->getPreferences((int)$_SESSION['user_id']);
                }

                $offers = $sortingService->sortOffers($offers, $aiFilters, $aiWeights, $userPreferences);
            } catch (Throwable $e) {
                error_log('AI sorting failed: ' . $e->getMessage());
            }
        }
        $mapOffers = array_values(array_filter(array_map(function ($offer) {
            if (!isset($offer['lat'], $offer['lng'])) {
                return null;
            }
            return [
                'id' => (int)($offer['id'] ?? 0),
                'title' => $offer['title'] ?? 'Bez tytułu',
                'lat' => isset($offer['lat']) ? (float)$offer['lat'] : null,
                'lng' => isset($offer['lng']) ? (float)$offer['lng'] : null,
                'price' => isset($offer['price']) ? (float)$offer['price'] : null,
                'city' => $offer['city'] ?? '',
                'street' => $offer['street'] ?? ''
            ];
        }, $mapOffers)));
        include 'views/search.php';
        break;
    case 'search_users':
        if (!isset($_GET['query'])) {
            echo json_encode([]);
            exit;
        }
        $query = '%' . $_GET['query'] . '%';
        global $pdo;
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username LIKE ? AND id != ? LIMIT 5");
        $stmt->execute([$query, $_SESSION['user_id'] ?? 0]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
        exit;
    case 'report_offer':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php");
            exit;
        }
        if (!isLoggedIn()) {
            setFlashMessage('error', 'Musisz być zalogowany, aby zgłosić ofertę.');
            header("Location: index.php?action=login");
            exit;
        }
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Nieprawidłowy token bezpieczeństwa.');
            header("Location: index.php?action=view_offer&offer_id=" . (int)($_POST['offer_id'] ?? 0));
            exit;
        }
        $offerId = (int)($_POST['offer_id'] ?? 0);
        $reason = $_POST['reason'] ?? '';
        if ($offerId > 0) {
            reportOffer($offerId, $_SESSION['user_id'], $reason);
        } else {
            setFlashMessage('error', 'Niepoprawne ogłoszenie do zgłoszenia.');
        }
        header("Location: index.php?action=view_offer&offer_id=" . $offerId);
        break;
    case 'update_offer_status':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isAdmin()) {
            setFlashMessage('error', 'Brak uprawnień do zmiany statusu.');
            header("Location: index.php");
            exit;
        }
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Nieprawidłowy token bezpieczeństwa.');
            header("Location: index.php?action=admin_dashboard");
            exit;
        }
        $offerId = (int)($_POST['offer_id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        updateOfferStatus($offerId, $status, $_SESSION['user_id']);
        header("Location: index.php?action=admin_dashboard");
        break;
    case 'moderate_report':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isAdmin()) {
            setFlashMessage('error', 'Brak uprawnień do moderacji zgłoszeń.');
            header("Location: index.php");
            exit;
        }
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Nieprawidłowy token bezpieczeństwa.');
            header("Location: index.php?action=admin_dashboard");
            exit;
        }
        $reportId = (int)($_POST['report_id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $note = $_POST['admin_note'] ?? '';
        updateReportStatus($reportId, $status, $_SESSION['user_id'], $note);
        header("Location: index.php?action=admin_dashboard");
        break;
    case 'dashboard':
        if (!isLoggedIn()) {
            setFlashMessage('error', 'You must be logged in to view the dashboard.');
            header("Location: index.php?action=login");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
            sendMessage($_POST['receiver_id'], $_POST['offer_id'], $_POST['message']);
        }
        include 'views/dashboard.php';
        break;
    case 'admin_dashboard':
        if (!isAdmin()) {
            setFlashMessage('error', 'Access denied. Admin privileges required.');
            header("Location: index.php?action=home");
            exit;
        }
        include 'views/admin_dashboard.php';
        break;
    case 'view_offer':
        if (!isset($_GET['offer_id'])) {
            setFlashMessage('error', 'No offer ID provided.');
            header("Location: index.php?action=search");
            exit;
        }
        include 'views/view_offer.php';
        break;
    default:
        include 'views/home.php';
}
?>
