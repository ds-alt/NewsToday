# News Today

Stay updated with the latest news across various categories! This web application fetches news articles using the NewsData API and displays them dynamically.

## Technologies Used
- HTML5
- CSS3 (Bootstrap 5)
- PHP
- JavaScript (ES6)

## Features
- Displays news articles in categories such as top news, world news, technology, politics, business, environment, health, and sports.
- Caches news data locally to improve performance and handle API rate limits.
- Allows users to load more articles dynamically without refreshing the page.

## Setup
1. Clone the repository.
2. Replace `YOUR_API_KEY` in `index.php` with your actual NewsData API key.
3. Ensure the `cache` directory is writable (`chmod 755 cache`).
4. Open `index.php` in your web browser.

## How It Works
- The application fetches news data from the NewsData API based on predefined categories.
- It uses PHP for server-side caching and handling API requests.
- Bootstrap and custom CSS styles are used for a responsive and visually appealing layout.
- JavaScript is utilized for dynamic content loading without page reloads.

## Credits
- NewsData API for providing the news data.
- Bootstrap for CSS framework.
- Google Fonts for 'Playfair Display' font.

Feel free to explore different categories and stay informed with News Today!
