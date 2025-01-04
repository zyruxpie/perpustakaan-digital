<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireLogin();

// Ambil data untuk slider
$slider_result = $conn->query("SELECT id, judul, gambar FROM buku ORDER BY RAND() LIMIT 6");

// Ambil 5 buku terbaru
$buku_terbaru_result = $conn->query("SELECT id, judul, pengarang, gambar FROM buku ORDER BY id DESC LIMIT 5");

// Ambil 5 buku terpopuler
$buku_terpopuler_result = $conn->query("
    SELECT b.id, b.judul, b.pengarang, b.gambar, COUNT(p.id) as total_peminjaman 
    FROM buku b 
    LEFT JOIN peminjaman p ON b.id = p.buku_id 
    GROUP BY b.id 
    ORDER BY total_peminjaman DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Perpustakaan Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f5f6fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Navbar */
        .navbar {
            background: white;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .nav-brand {
            color: #4a90e2;
            font-size: 1.5rem;
            text-decoration: none;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-link {
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: #4a90e2;
        }

        /* Main Content */
        .main-content {
            margin-top: 80px;
            padding: 2rem 0;
        }

        /* Carousel */
        .carousel-container {
            width: 100%;
            height: 500px;
            position: relative;
            overflow: hidden;
            margin: 2rem 0;
        }

        .carousel-container h1 {
    text-align: center;
}

        .carousel {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .carousel-inner {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .carousel-item {
            position: absolute;
            width: 300px;
            height: 400px;
            transition: all 0.5s ease;
            opacity: 0;
            transform: translateX(-50%) scale(0.8);
        }

        .carousel-item.active {
            opacity: 1;
            z-index: 2;
            left: 50%;
            transform: translateX(-50%) scale(1);
        }

        .carousel-item.prev {
            opacity: 0.7;
            z-index: 1;
            left: 30%;
            transform: translateX(-50%) scale(0.9);
        }

        .carousel-item.next {
            opacity: 0.7;
            z-index: 1;
            left: 70%;
            transform: translateX(-50%) scale(0.9);
        }

        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: white;
            border: none;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            cursor: pointer;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .carousel-arrow.prev {
            left: 20px;
        }

        .carousel-arrow.next {
            right: 20px;
        }

        /* Book Sections */
        .section-title {
            font-size: 1.5rem;
            margin: 2rem 0 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .book-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .book-card:hover {
            transform: translateY(-5px);
        }

        .book-cover {
            width: 100%;
            height: 280px;
            object-fit: cover;
        }

        .book-info {
            padding: 1rem;
        }

        .book-title {
            font-size: 1rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .book-author {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .book-link {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #4a90e2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .book-link:hover {
            background: #357abd;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <a href="beranda.php" class="nav-brand">
                <i class="fas fa-book-reader"></i> Perpustakaan Digital
            </a>
            <div class="nav-links">
                <a href="beranda.php" class="nav-link">
                    <i class="fas fa-home"></i> Beranda
                </a>
                <a href="buku.php" class="nav-link">
                    <i class="fas fa-book"></i> Daftar Buku
                </a>
                <a href="status-buku.php" class="nav-link">
                    <i class="fas fa-info-circle"></i> Status Buku
                </a>
                <a href="peminjaman.php" class="nav-link">
                    <i class="fas fa-bookmark"></i> Peminjaman
                </a>
                <a href="../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <div class="carousel-container">
                <h1>Buku</h1>
                <div class="carousel">
                    <button class="carousel-arrow prev">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <div class="carousel-inner">
                        <?php while ($row = $slider_result->fetch_assoc()): ?>
                            <div class="carousel-item">
                                <img src="<?php echo $row['gambar']; ?>" alt="<?php echo $row['judul']; ?>">
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <button class="carousel-arrow next">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <section>
                <h2 class="section-title">
                    <i class="fas fa-star"></i> Buku Terbaru
                </h2>
                <div class="book-grid">
                    <?php while ($row = $buku_terbaru_result->fetch_assoc()): ?>
                        <div class="book-card">
                            <img src="<?php echo $row['gambar']; ?>" 
                                 alt="<?php echo $row['judul']; ?>" 
                                 class="book-cover">
                            <div class="book-info">
                                <h3 class="book-title"><?php echo $row['judul']; ?></h3>
                                <p class="book-author"><?php echo $row['pengarang']; ?></p>
                                <a href="detail-buku.php?id=<?php echo $row['id']; ?>" 
                                   class="book-link">Lihat Detail</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <section>
                <h2 class="section-title">
                    <i class="fas fa-fire"></i> Buku Terpopuler
                </h2>
                <div class="book-grid">
                    <?php while ($row = $buku_terpopuler_result->fetch_assoc()): ?>
                        <div class="book-card">
                            <img src="<?php echo $row['gambar']; ?>" 
                                 alt="<?php echo $row['judul']; ?>" 
                                 class="book-cover">
                            <div class="book-info">
                                <h3 class="book-title"><?php echo $row['judul']; ?></h3>
                                <p class="book-author"><?php echo $row['pengarang']; ?></p>
                                <a href="detail-buku.php?id=<?php echo $row['id']; ?>" 
                                   class="book-link">Lihat Detail</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const items = document.querySelectorAll('.carousel-item');
            const totalItems = items.length;
            let currentIndex = 0;

            function updateCarousel() {
                items.forEach((item, index) => {
                    item.classList.remove('active', 'prev', 'next');
                    if (index === currentIndex) {
                        item.classList.add('active');
                    } else if (index === (currentIndex - 1 + totalItems) % totalItems) {
                        item.classList.add('prev');
                    } else if (index === (currentIndex + 1) % totalItems) {
                        item.classList.add('next');
                    }
                });
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % totalItems;
                updateCarousel();
            }

            function prevSlide() {
                currentIndex = (currentIndex - 1 + totalItems) % totalItems;
                updateCarousel();
            }

            document.querySelector('.carousel-arrow.next').addEventListener('click', nextSlide);
            document.querySelector('.carousel-arrow.prev').addEventListener('click', prevSlide);

            // Initialize
            updateCarousel();
            
            // Auto slide
            const autoSlide = setInterval(nextSlide, 5000);

            // Stop auto slide on hover
            document.querySelector('.carousel').addEventListener('mouseenter', () => {
                clearInterval(autoSlide);
            });
        });
    </script>
</body>
</html>