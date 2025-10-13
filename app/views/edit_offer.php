<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj ogłoszenie - Apartment Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">

</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    <?php include 'header.php'; ?>
    <main class="container mx-auto px-4 py-8">
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
            <div class="mb-6 p-4 rounded-lg shadow-md <?php echo $flash['type'] === 'error' ? 'bg-red-50 text-red-800 border-l-4 border-red-500' : 'bg-green-50 text-green-800 border-l-4 border-green-500'; ?>">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="font-medium"><?php echo htmlspecialchars($flash['message']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Edytuj ogłoszenie</h1>
                <p class="text-gray-600 mt-1">Zaktualizuj szczegóły ogłoszenia</p>
            </div>

            <?php
            if (!isLoggedIn()) {
                echo '<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                                <p class="text-red-700 font-medium">Musisz być zalogowany, aby edytować ogłoszenia.</p>
                        </div>
                    </div>';
            } else {
                $offer = getOfferDetails($_GET['offer_id']);
                if (!$offer || $offer['user_id'] != $_SESSION['user_id']) {
                    echo '<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-red-700 font-medium">Ogłoszenie nie istnieje lub nie masz uprawnień do jego edycji.</p>
                            </div>
                        </div>';
                } else {
            ?>
            <form method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                <input type="hidden" name="primary_image" id="primary_image" value="0">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information Section -->
                    <div class="md:col-span-2">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Informacje podstawowe</h2>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Title*</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($offer['title']); ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition placeholder-gray-400"
                               placeholder="Modern apartment in city center" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">City*</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($offer['city']); ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition placeholder-gray-400"
                               placeholder="Warsaw" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Street*</label>
                        <input type="text" name="street" value="<?php echo htmlspecialchars($offer['street']); ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition placeholder-gray-400"
                               placeholder="Main Street 123" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Price (PLN)*</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">PLN</span>
                            <input type="number" step="1" name="price" value="<?php echo htmlspecialchars($offer['price']); ?>"
                                   class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition" required>
                        </div>
                    </div>

                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Description*</label>
                        <textarea name="description" rows="4"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition placeholder-gray-400"
                                  required><?php echo htmlspecialchars($offer['description']); ?></textarea>
                    </div>

                    <!-- Property Details Section -->
                    <div class="md:col-span-2 mt-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Szczegóły nieruchomości</h2>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Size (m²)*</label>
                        <div class="relative">
                            <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">m²</span>
                            <input type="number" name="size" value="<?php echo htmlspecialchars($offer['size']); ?>"
                                   class="w-full pr-10 pl-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Floor</label>
                        <input type="number" name="floor" value="<?php echo htmlspecialchars($offer['floor'] ?? ''); ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Building Type*</label>
                        <select name="building_type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition appearance-none bg-white" required>
                            <option value="apartment" <?php echo $offer['building_type'] == 'apartment' ? 'selected' : ''; ?>>Apartment Building</option>
                            <option value="block" <?php echo $offer['building_type'] == 'block' ? 'selected' : ''; ?>>Residential Block</option>
                            <option value="house" <?php echo $offer['building_type'] == 'house' ? 'selected' : ''; ?>>Detached House</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Rooms*</label>
                        <input type="number" name="rooms" value="<?php echo htmlspecialchars($offer['rooms']); ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Bathrooms*</label>
                        <input type="number" name="bathrooms" value="<?php echo htmlspecialchars($offer['bathrooms']); ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Year Built</label>
                        <input type="number" name="year_built" value="<?php echo htmlspecialchars($offer['year_built'] ?? ''); ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Condition*</label>
                        <select name="condition_type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition appearance-none bg-white" required>
                            <option value="new" <?php echo $offer['condition_type'] == 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="renovated" <?php echo $offer['condition_type'] == 'renovated' ? 'selected' : ''; ?>>Renovated</option>
                            <option value="used" <?php echo $offer['condition_type'] == 'used' ? 'selected' : ''; ?>>Used</option>
                            <option value="to_renovate" <?php echo $offer['condition_type'] == 'to_renovate' ? 'selected' : ''; ?>>To Renovate</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Available From</label>
                        <input type="date" name="available_from" value="<?php echo htmlspecialchars($offer['available_from'] ?? ''); ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition">
                    </div>

                    <!-- Amenities Section -->
                    <div class="md:col-span-2 mt-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Udogodnienia</h2>
                    </div>

                    <div class="mb-4 md:col-span-2 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="has_balcony" name="has_balcony" value="1" <?php echo $offer['has_balcony'] ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="has_balcony" class="ml-2 text-gray-700 text-sm">Balcony</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="has_elevator" name="has_elevator" value="1" <?php echo $offer['has_elevator'] ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="has_elevator" class="ml-2 text-gray-700 text-sm">Elevator</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="parking" name="parking" value="1" <?php echo $offer['parking'] ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="parking" class="ml-2 text-gray-700 text-sm">Parking</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="garage" name="garage" value="1" <?php echo $offer['garage'] ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="garage" class="ml-2 text-gray-700 text-sm">Garage</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="garden" name="garden" value="1" <?php echo $offer['garden'] ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="garden" class="ml-2 text-gray-700 text-sm">Garden</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="furnished" name="furnished" value="1" <?php echo $offer['furnished'] ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="furnished" class="ml-2 text-gray-700 text-sm">Furnished</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="pets_allowed" name="pets_allowed" value="1" <?php echo $offer['pets_allowed'] ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="pets_allowed" class="ml-2 text-gray-700 text-sm">Pets Allowed</label>
                        </div>
                    </div>

                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Heating Type*</label>
                        <select name="heating_type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition appearance-none bg-white" required>
                            <option value="gas" <?php echo $offer['heating_type'] == 'gas' ? 'selected' : ''; ?>>Gas</option>
                            <option value="electric" <?php echo $offer['heating_type'] == 'electric' ? 'selected' : ''; ?>>Electric</option>
                            <option value="district" <?php echo $offer['heating_type'] == 'district' ? 'selected' : ''; ?>>District</option>
                            <option value="other" <?php echo $offer['heating_type'] == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <!-- Images Section -->
                    <div class="md:col-span-2 mt-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Zdjęcia</h2>
                    </div>

                    <div class="mb-4 md:col-span-2">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Dodaj nowe zdjęcia</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                            <input type="file" id="images" name="images[]" accept="image/jpeg,image/png" multiple onchange="updatePrimaryImageOptions()"
                                   class="hidden">
                            <label for="images" class="cursor-pointer">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <p class="mt-1 text-sm text-gray-600">Kliknij, aby przesłać zdjęcia (JPEG/PNG, maks. 5)</p>
                                <p class="mt-1 text-xs text-gray-500">Pozostaw puste, aby zachować obecne zdjęcia</p>
                            </label>
                        </div>
                        <div id="image-preview" class="flex flex-wrap gap-4 mt-4"></div>
                        <p class="mt-2 text-sm text-gray-500">Kliknij zdjęcie, aby ustawić je jako główne (wyświetlane jako pierwsze)</p>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <button type="submit" class="w-full bg-accent-500 hover:bg-accent-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 shadow-md">
                        Zaktualizuj ogłoszenie
                    </button>
                </div>
            </form>
            <?php
                }
            }
            ?>
        </div>
    </main>
</body>
</html>

<script>
    function updatePrimaryImageOptions() {
        const imageInput = document.getElementById('images');
        const previewContainer = document.getElementById('image-preview');
        const primaryImageInput = document.getElementById('primary_image');
        previewContainer.innerHTML = '';
        const files = imageInput.files;

        if (files.length === 0) {
            previewContainer.innerHTML = '<p class="text-gray-500 text-sm">Nie wybrano zdjęć</p>';
            return;
        }

        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.createElement('div');
                imgContainer.className = 'relative group cursor-pointer';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-32 h-32 object-cover rounded-lg border-2 ' +
                                (i === 0 ? 'border-primary' : 'border-transparent group-hover:border-gray-300');
                img.alt = 'Preview image ' + (i + 1);

                const overlay = document.createElement('div');
                overlay.className = 'absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition';

                const checkIcon = document.createElement('div');
                checkIcon.className = 'absolute top-2 right-2 bg-white rounded-full p-1 ' +
                                     (i === 0 ? 'block' : 'hidden group-hover:block');
                checkIcon.innerHTML = '<svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>';

                imgContainer.appendChild(img);
                imgContainer.appendChild(overlay);
                imgContainer.appendChild(checkIcon);

                imgContainer.addEventListener('click', () => {
                    // Remove border from all images
                    previewContainer.querySelectorAll('img').forEach(image => {
                        image.classList.remove('border-primary');
                        image.classList.add('border-transparent');
                    });
                    // Hide all check icons
                    previewContainer.querySelectorAll('[class*="group-hover:block"]').forEach(icon => {
                        icon.classList.add('hidden');
                    });
                    // Add border to clicked image and show its check icon
                    img.classList.remove('border-transparent');
                    img.classList.add('border-primary');
                    checkIcon.classList.remove('hidden');
                    // Update hidden input
                    primaryImageInput.value = i;
                });

                previewContainer.appendChild(imgContainer);
            };
            reader.readAsDataURL(files[i]);
        }
        // Set first image as primary by default
        if (files.length > 0) {
            primaryImageInput.value = 0;
        }
    }
</script>