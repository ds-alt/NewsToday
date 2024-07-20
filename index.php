<?php
$apiKey = 'pub_48517875129c003a7c5304ecc8f457552fc35'; // Replace with your actual API key
$cacheDir = __DIR__ . '/cache';
$cacheDuration = 600; // Cache duration in seconds (e.g., 10 minutes)
$sections = [
    'top-news' => 'top',
    'world-news' => 'world',
    'technology' => 'technology',
    'politics' => 'politics',
    'business' => 'business',
    'environment' => 'environment',
    'health' => 'health',
    'sports' => 'sports'
];

// Ensure the cache directory exists and is writable
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
} elseif (!is_writable($cacheDir)) {
    chmod($cacheDir, 0755);
}

function fetchNews($category, $retryCount = 3) {
    global $apiKey, $cacheDir, $cacheDuration;
    $apiEndpoint = "https://newsdata.io/api/1/news?apikey=$apiKey&category=$category&language=en";
    $cacheFile = $cacheDir . '/' . md5($apiEndpoint) . '.json';

    // Check if cached file exists and is still valid
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheDuration) {
        $response = file_get_contents($cacheFile);
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            // Cache the response
            file_put_contents($cacheFile, $response);
        } elseif ($httpCode == 429 && $retryCount > 0) {
            // Rate limit exceeded, wait and retry
            sleep(5); // Wait for 5 seconds before retrying
            return fetchNews($category, $retryCount - 1);
        } else {
            throw new Exception("Failed to fetch news. HTTP status code: $httpCode");
        }
    }

    return json_decode($response, true);
}

function renderArticles($articles) {
    $html = '';
    foreach (array_slice($articles, 0, 3) as $article) { // Display only the first 3 articles initially
        $title = htmlspecialchars($article['title'] ?? 'No title');
        $url = htmlspecialchars($article['link'] ?? '#');
        $urlToImage = htmlspecialchars($article['image_url'] ?? 'https://via.placeholder.com/150');
        $html .= "
            <div class='col-md-4 mb-4'>
                <div class='card product'>
                    <img src='$urlToImage' class='card-img-top' alt='$title'>
                    <div class='card-body'>
                        <h5 class='card-title'>$title</h5>
                        <a href='$url' class='btn btn-primary' target='_blank'>Read more</a>
                    </div>
                </div>
            </div>";
    }
    return $html;
}

function renderLoadMoreButton($sectionId, $articles) {
    if (count($articles) > 3) {
        return "
            <div class='text-center'>
                <button class='btn btn-secondary mt-3 load-more-btn' onclick='loadMore(\"$sectionId\")'>Load More</button>
            </div>";
    }
    return '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Today</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <style>
        .product {
            border: 1px solid #dee2e6;
            margin-bottom: 15px;
            padding: 10px;
            height: 100%;
        }
        .product img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product .card-body {
            padding: 10px;
        }
        .product .card-title {
            font-size: 1rem;
            font-family: 'Playfair Display', serif;
        }
        .product .card-text {
            font-size: 0.9rem;
        }
        .section-title {
            margin-top: 40px;
            font-family: 'Playfair Display', serif;
        }
        .ellipsis {
            font-size: 2rem;
        }
        .header-container h1,
        .header-container p {
            font-family: 'Playfair Display', serif;
        }
        .custom-bg {
            background-color: rgb(60, 68, 84) !important;
        }
        .load-more-btn {
            background-color: transparent !important;
            border: none !important;
            color: black !important;
            font-size: 1rem;
            padding: 0;
        }
        .load-more-btn:hover {
            color: grey !important;
        }
    </style>
</head>
<body>
<div class="container-fluid p-5 custom-bg text-white text-center header-container">
    <h1>News Today</h1>
    <p>Stay updated with the latest news!</p>
</div>

<!-- Navigation bar -->
<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
    <div class="container-fluid">
        <ul class="navbar-nav mx-auto">
            <?php foreach ($sections as $sectionId => $category): ?>
                <li class="nav-item">
                    <a class="nav-link" href="#<?= $sectionId ?>"><?= ucfirst(str_replace('-', ' ', $sectionId)) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>

<!-- News Sections -->
<div class="container mt-5">
    <?php foreach ($sections as $sectionId => $category): ?>
        <?php
        try {
            $data = fetchNews($category);
            $articles = $data['results'] ?? [];
        } catch (Exception $e) {
            echo "<p class='text-danger'>Unable to load news for " . ucfirst(str_replace('-', ' ', $sectionId)) . ": " . $e->getMessage() . "</p>";
            $articles = [];
        }
        ?>
        <div id="<?= $sectionId ?>">
            <h2 class="section-title"><?= ucfirst(str_replace('-', ' ', $sectionId)) ?></h2>
            <div class="row" id="<?= $sectionId ?>-section">
                <?= renderArticles($articles) ?>
            </div>
            <?= renderLoadMoreButton($sectionId, $articles) ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- Bootstrap and required JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function loadMore(sectionId) {
        fetch('load_more.php?section=' + sectionId)
            .then(response => response.text())
            .then(html => {
                const section = document.getElementById(sectionId + '-section');
                section.innerHTML += html;
                const button = document.querySelector(`#${sectionId} .btn-secondary`);
                if (button) button.style.display = 'none'; // Hide the button
            })
            .catch(error => console.error('Error loading more articles:', error));
    }
</script>
</body>
</html>
