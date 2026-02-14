<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfume POS | Card-2 Design</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 20px;
        }

        /* Main container for demo - you can remove this */
        .demo-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* ===== CARD-2 PROFESSIONAL PERFUME POS DESIGN ===== */
        .card-2 {
            position: relative;
            width: 360px;
            height: 520px;
            background: linear-gradient(145deg, #ffffff 0%, #fafafa 100%);
            border-radius: 32px;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(230, 180, 140, 0.2) inset,
                0 0 30px rgba(255, 215, 175, 0.3);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        /* Glass morphism overlay */
        .card-2::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 30%, rgba(255, 245, 235, 0.8), transparent 70%);
            pointer-events: none;
            z-index: 1;
        }

        /* Perfume image container */
        .card-2-image {
            position: relative;
            height: 320px;
            background: linear-gradient(135deg, #f8e6d9, #ecd9c8);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Floating perfume bottle */
        .perfume-bottle {
            width: 160px;
            height: 240px;
            background: linear-gradient(145deg, #fff9f0, #f0e4d8);
            border-radius: 80px 80px 40px 40px;
            position: relative;
            box-shadow: 
                0 20px 30px -10px rgba(160, 100, 60, 0.3),
                0 0 0 2px rgba(255, 255, 255, 0.8) inset;
            transform: rotate(-5deg);
            transition: transform 0.3s ease;
            z-index: 5;
        }

        /* Bottle neck */
        .perfume-bottle::before {
            content: '';
            position: absolute;
            top: -35px;
            left: 50%;
            transform: translateX(-50%);
            width: 45px;
            height: 50px;
            background: linear-gradient(145deg, #e8d5c0, #d4bc9f);
            border-radius: 30px 30px 10px 10px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }

        /* Bottle cap */
        .perfume-bottle::after {
            content: '';
            position: absolute;
            top: -55px;
            left: 50%;
            transform: translateX(-50%);
            width: 55px;
            height: 25px;
            background: linear-gradient(145deg, #c9a87c, #b38b5a);
            border-radius: 15px 15px 5px 5px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.15);
        }

        /* Liquid inside bottle */
        .perfume-liquid {
            position: absolute;
            bottom: 15px;
            left: 15px;
            right: 15px;
            height: 140px;
            background: linear-gradient(145deg, #f5d0b0, #e6b87e);
            border-radius: 40px 40px 20px 20px;
            box-shadow: 0 5px 15px rgba(220, 140, 60, 0.3) inset;
        }

        /* Decorative bubbles */
        .bubble {
            position: absolute;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            pointer-events: none;
        }

        .bubble-1 {
            width: 15px;
            height: 15px;
            top: 40%;
            left: 30%;
        }

        .bubble-2 {
            width: 10px;
            height: 10px;
            top: 60%;
            right: 35%;
        }

        .bubble-3 {
            width: 8px;
            height: 8px;
            bottom: 30%;
            left: 40%;
        }

        /* Floating particles (fragrance mist) */
        .mist-particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            filter: blur(4px);
            animation: float 6s infinite ease-in-out;
        }

        .particle-1 {
            width: 60px;
            height: 60px;
            top: 20%;
            right: 15%;
            background: radial-gradient(circle, rgba(255,235,215,0.4), transparent);
            animation-delay: 0s;
        }

        .particle-2 {
            width: 80px;
            height: 80px;
            bottom: 10%;
            left: 5%;
            background: radial-gradient(circle, rgba(255,245,225,0.3), transparent);
            animation-delay: 2s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); opacity: 0.3; }
            50% { transform: translateY(-20px) scale(1.1); opacity: 0.5; }
        }

        /* Card content */
        .card-2-content {
            position: relative;
            padding: 24px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            z-index: 10;
        }

        /* Brand and category */
        .perfume-category {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .brand {
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
            color: #b38b5a;
            text-transform: uppercase;
        }

        .rating {
            color: #ffb800;
            font-size: 13px;
        }

        .rating i {
            margin-right: 2px;
        }

        /* Perfume name */
        .perfume-name {
            font-size: 24px;
            font-weight: 700;
            color: #2c1810;
            margin-bottom: 6px;
            line-height: 1.2;
        }

        .perfume-subtitle {
            font-size: 14px;
            color: #7a5a44;
            margin-bottom: 16px;
            font-style: italic;
        }

        /* Notes/fragrance profile */
        .fragrance-notes {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .note {
            padding: 6px 14px;
            background: #f8f0e8;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
            color: #6b4f3a;
            border: 1px solid #e8d5c0;
            letter-spacing: 0.5px;
        }

        /* Price and actions */
        .card-2-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
        }

        .price {
            display: flex;
            flex-direction: column;
        }

        .current-price {
            font-size: 28px;
            font-weight: 800;
            color: #2c1810;
            line-height: 1;
        }

        .old-price {
            font-size: 14px;
            color: #a0a0a0;
            text-decoration: line-through;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn-cart, .btn-wishlist {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 18px;
        }

        .btn-cart {
            background: linear-gradient(145deg, #2c1810, #3d291f);
            color: white;
            box-shadow: 0 8px 15px rgba(44, 24, 16, 0.2);
        }

        .btn-wishlist {
            background: white;
            color: #2c1810;
            border: 1px solid #e8d5c0;
        }

        .btn-cart:hover {
            transform: scale(1.1);
            background: linear-gradient(145deg, #3d291f, #2c1810);
        }

        .btn-wishlist:hover {
            background: #fff0e8;
            transform: scale(1.1);
        }

        /* Hover effects */
        .card-2:hover {
            transform: translateY(-8px);
            box-shadow: 
                0 30px 60px -15px rgba(0, 0, 0, 0.3),
                0 0 0 1px #d4bc9f inset,
                0 0 40px rgba(230, 180, 140, 0.4);
        }

        .card-2:hover .perfume-bottle {
            transform: rotate(-5deg) scale(1.05);
        }

        /* Stock status */
        .stock-status {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
            z-index: 20;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .stock-status i {
            font-size: 10px;
            color: #4caf50;
        }

        /* Sample usage in POS grid */
        .pos-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .pos-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .pos-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- POS Grid Demo - Three cards in a row -->
    <div class="pos-grid">
        <!-- Card-2 - First Perfume -->
        <div class="card-2">
            <div class="stock-status">
                <i class="fas fa-circle"></i> In Stock
            </div>
            <div class="card-2-image">
                <!-- Floating mist particles -->
                <div class="mist-particle particle-1"></div>
                <div class="mist-particle particle-2"></div>
                
                <!-- Perfume bottle illustration -->
                <div class="perfume-bottle">
                    <div class="perfume-liquid">
                        <div class="bubble bubble-1"></div>
                        <div class="bubble bubble-2"></div>
                        <div class="bubble bubble-3"></div>
                    </div>
                </div>
            </div>
            <div class="card-2-content">
                <div class="perfume-category">
                    <span class="brand">CHANEL</span>
                    <span class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </span>
                </div>
                <h3 class="perfume-name">N°5 L'EAU</h3>
                <div class="perfume-subtitle">Eau de Parfum</div>
                <div class="fragrance-notes">
                    <span class="note">Citrus</span>
                    <span class="note">Jasmine</span>
                    <span class="note">Vanilla</span>
                </div>
                <div class="card-2-footer">
                    <div class="price">
                        <span class="current-price">$138</span>
                        <span class="old-price">$165</span>
                    </div>
                    <div class="actions">
                        <button class="btn-wishlist"><i class="far fa-heart"></i></button>
                        <button class="btn-cart"><i class="fas fa-shopping-bag"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card-2 - Second Perfume -->
        <div class="card-2">
            <div class="stock-status" style="color: #b85c00;">
                <i class="fas fa-circle" style="color: #ff9800;"></i> Low Stock
            </div>
            <div class="card-2-image" style="background: linear-gradient(135deg, #e6d9cc, #dacbbe);">
                <div class="mist-particle particle-1"></div>
                <div class="mist-particle particle-2"></div>
                <div class="perfume-bottle" style="background: linear-gradient(145deg, #f7ede4, #eee1d4);">
                    <div class="perfume-liquid" style="background: linear-gradient(145deg, #f0c4a0, #e6b07a);"></div>
                </div>
            </div>
            <div class="card-2-content">
                <div class="perfume-category">
                    <span class="brand">DIOR</span>
                    <span class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </span>
                </div>
                <h3 class="perfume-name">J'adore</h3>
                <div class="perfume-subtitle">Floral Bouquet</div>
                <div class="fragrance-notes">
                    <span class="note">Rose</span>
                    <span class="note">Ylang</span>
                    <span class="note">Musk</span>
                </div>
                <div class="card-2-footer">
                    <div class="price">
                        <span class="current-price">$152</span>
                    </div>
                    <div class="actions">
                        <button class="btn-wishlist"><i class="far fa-heart"></i></button>
                        <button class="btn-cart"><i class="fas fa-shopping-bag"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card-2 - Third Perfume -->
        <div class="card-2">
            <div class="stock-status" style="color: #b85c00;">
                <i class="fas fa-circle" style="color: #ff9800;"></i> Low Stock
            </div>
            <div class="card-2-image" style="background: linear-gradient(135deg, #d9e6f0, #c9d9e8);">
                <div class="mist-particle particle-1"></div>
                <div class="mist-particle particle-2"></div>
                <div class="perfume-bottle" style="background: linear-gradient(145deg, #edf5ff, #dde9f5);">
                    <div class="perfume-liquid" style="background: linear-gradient(145deg, #b8d4e7, #9bb9d4);"></div>
                </div>
            </div>
            <div class="card-2-content">
                <div class="perfume-category">
                    <span class="brand">TOM FORD</span>
                    <span class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </span>
                </div>
                <h3 class="perfume-name">Ombré Leather</h3>
                <div class="perfume-subtitle">Eau de Parfum</div>
                <div class="fragrance-notes">
                    <span class="note">Leather</span>
                    <span class="note">Amber</span>
                    <span class="note">Spices</span>
                </div>
                <div class="card-2-footer">
                    <div class="price">
                        <span class="current-price">$245</span>
                    </div>
                    <div class="actions">
                        <button class="btn-wishlist"><i class="far fa-heart"></i></button>
                        <button class="btn-cart"><i class="fas fa-shopping-bag"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>