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

function fetchNews($category) {
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
            <button class='btn btn-secondary mt-3' onclick='loadMore(\"$sectionId\")'>Load More</button>";
    }
    return '';
}

// Handle load more functionality
if (isset($_GET['section'])) {
    $sectionId = $_GET['section'];
    $category = $sections[$sectionId] ?? '';
    if ($category) {
        $data = fetchNews($category);
        $articles = $data['results'] ?? [];
        echo renderArticles(array_slice($articles, 3)); // Load the next set of articles
    } else {
        echo 'Invalid section';
    }
}
?>
