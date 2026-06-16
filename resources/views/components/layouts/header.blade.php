<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Cleann Organics</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon/favicon-16x16.png') }}" />
    <link rel="stylesheet" href="{{ asset('lib/css/swiper-bundle.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('lib/css/bvselect.css') }}" />
    <link rel="stylesheet" href="{{ asset('lib/css/venobox.css') }}" />
    <link rel="stylesheet" href="{{ asset('lib/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
</head>

<body>
<div class="loader">
<div class="loader-icon">
<img src="{{ asset('images/loader.gif')}}" alt="loader" />
</div>
</div>
<!-- Shopping Cart sidebar  start  -->
<div class="shopping-cart">
<div class="shopping-cart-top">
<div class="shopping-cart-header">
<h5 class="font-body--xxl-500">Shopping Cart (<span class="count">2</span>)</h5>
<button class="close">
    <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="22.5" cy="22.5" r="22.5" fill="white" />
        <path d="M28.75 16.25L16.25 28.75" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M16.25 16.25L28.75 28.75" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
</button>
</div>

<div class="shopping-cart__product-content">
<div class="shopping-cart__product-content-item">
    <div class="img-wrapper">
        <img src="{{ asset('images/products/img-01.png')}}" alt="product" />
    </div>
    <div class="text-content">
        <h5 class="font-body--md-400">Fresh Indian Orange</h5>
        <p class="font-body--md-400">1kg x <span class="font-body--md-500">12.00</span></p>
    </div>
</div>
<button class="delete-item">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 23C18.0748 23 23 18.0748 23 12C23 5.92525 18.0748 1 12 1C5.92525 1 1 5.92525 1 12C1 18.0748 5.92525 23 12 23Z" stroke="#CCCCCC" stroke-miterlimit="10" />
        <path d="M16 8L8 16" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M16 16L8 8" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
</button>
</div>

<div class="shopping-cart__product-content">
<div class="shopping-cart__product-content-item">
    <div class="img-wrapper">
        <img src="{{ asset('images/products/img-01.png')}}" alt="product" />
    </div>
    <div class="text-content">
        <h5 class="font-body--md-400">Fresh Indian Orange</h5>
        <p class="font-body--md-400">1kg x <span class="font-body--md-500">12.00</span></p>
    </div>
</div>
<button class="delete-item">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 23C18.0748 23 23 18.0748 23 12C23 5.92525 18.0748 1 12 1C5.92525 1 1 5.92525 1 12C1 18.0748 5.92525 23 12 23Z" stroke="#CCCCCC" stroke-miterlimit="10" />
        <path d="M16 8L8 16" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M16 16L8 8" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
</button>
</div>
</div>
<div class="shopping-cart-bottom">
<div class="shopping-cart-product-info">
<p class="product-count font-body--lg-400">2 Product</p>
<span class="product-price font-body--lg-500">$26.00</span>
</div>

<form action="#">
<button class="button button--lg w-100">Checkout</button>
<button class="button button--lg button--disable w-100">
    go to cart
</button>
</form>
</div>
</div>
<!-- Shopping Cart sidebar  end -->

<!-- Header Section start -->
<header class="header header--one">
<div class="header__top">
<div class="container">
<div class="header__top-content">
    <div class="header__top-left">
        <p class="font-body--sm">
            <span>
                <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M16 8.36364C16 14.0909 8.5 19 8.5 19C8.5 19 1 14.0909 1 8.36364C1 6.41068 1.79018 4.53771 3.1967 3.15676C4.60322 1.77581 6.51088 1 8.5 1C10.4891 1 12.3968 1.77581 13.8033 3.15676C15.2098 4.53771 16 6.41068 16 8.36364Z"
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M8.5 10.8182C9.88071 10.8182 11 9.71925 11 8.36364C11 7.00803 9.88071 5.90909 8.5 5.90909C7.11929 5.90909 6 7.00803 6 8.36364C6 9.71925 7.11929 10.8182 8.5 10.8182Z"
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </span>
            Store Location: Lincoln- 344, Illinois, Chicago, USA
        </p>
    </div>
    <div class="header__top-right">
        <!-- <div class="header__dropdown">
            <select id="selectbox1" class="header__dropdown-menu">
                <option value="pt_1">Eng</option>
                <option value="en_2">Bng</option>

                <option value="ch_4">cn</option>
                <option value="5">Urdu</option>
            </select>
            <select id="selectbox2" class="header__dropdown-menu">
                <option value="pt_1">USD</option>
                <option value="en_2">Tk</option>

                <option value="ch_4">yan</option>
                <option value="5">rup</option>
            </select>
        </div> -->
        <div class="header__in">
            <a href="sign-in.html">Sign in </a>
            <span>/</span>
            <a href="create-account.html">Sign up</a>
        </div>
    </div>
</div>
</div>
</div>
<div class="header__center">
<div class="container">
<div class="header__center-content">
    <div class="header__brand">
        <button class="header__sidebar-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 12H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M3 6H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M3 18H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <a href="index.html">
            <img src="{{ asset('images/logo.png')}}" alt="brand-logo" />
        </a>
    </div>
    <form action="#">
        <div class="header__input-form">
            <input type="text" placeholder="Search" />
            <span class="search-icon">
                <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9.16667 16.3333C12.8486 16.3333 15.8333 13.3486 15.8333 9.66667C15.8333 5.98477 12.8486 3 9.16667 3C5.48477 3 2.5 5.98477 2.5 9.66667C2.5 13.3486 5.48477 16.3333 9.16667 16.3333Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path d="M17.4999 18L13.8749 14.375" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
            <button type="submit" class="search-btn button button--md">
                Search
            </button>
        </div>
    </form>
    <div class="header__cart">
        <div class="header__cart-item">
            <a class="fav" href="wishlist.html">
                <svg width="25" height="23" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.9996 16.5451C-6.66672 7.3333 4.99993 -2.6667 9.9996 3.65668C14.9999 -2.6667 26.6666 7.3333 9.9996 16.5451Z" stroke="#1A1A1A" stroke-width="1.5" />
                </svg>
            </a>
        </div>
        <div class="header__cart-item">
            <div class="header__cart-item-content" id="cart-bag">
                <button class="cart-bag">
                    <svg width="34" height="35" viewBox="0 0 34 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M11.3333 14.6667H7.08333L4.25 30.25H29.75L26.9167 14.6667H22.6667M11.3333 14.6667V10.4167C11.3333 7.28705 13.8704 4.75 17 4.75V4.75C20.1296 4.75 22.6667 7.28705 22.6667 10.4167V14.6667M11.3333 14.6667H22.6667M11.3333 14.6667V18.9167M22.6667 14.6667V18.9167"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <span class="item-number">2</span>
                </button>
                <div class="header__cart-item-content-info">
                    <h5>Shopping cart:</h5>
                    <span class="price">$57.00</span>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<div class="header__bottom">
<div class="container">
<div class="header__bottom-content">
    <ul class="header__navigation-menu">
        <!-- Homepages -->
        <li class="header__navigation-menu-link active">
            <a href="#">
                Home
                <span class="drop-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33332 5.66667L7.99999 10.3333L12.6667 5.66667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
            <ul class="header__navigation-drop-menu">
                <li class="header__navigation-drop-menu-link active">
                    <a href="{{ route('home') }}">Home</a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="home-02.html">Homepage 02</a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="home-03.html">Homepage 03</a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="home-04.html">Homepage 04</a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="home-05.html">Homepage 05</a>
                </li>
            </ul>
        </li>
        <!-- Shopepages -->
        <li class="header__navigation-menu-link">
            <a href="#">
                Shop
                <span class="drop-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33332 5.66667L7.99999 10.3333L12.6667 5.66667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
            <ul class="header__navigation-drop-menu">
                <li class="header__navigation-drop-menu-link">
                    <a href="shop-01.html">Shop 01</a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="shop-02.html">Shop 02</a>
                </li>
            </ul>
        </li>
        <li class="header__navigation-menu-link">
            <a href="#">
                Pages
                <span class="drop-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33332 5.66667L7.99999 10.3333L12.6667 5.66667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
            <ul class="header__navigation-drop-menu">
                <li class="header__navigation-drop-menu-link">
                    <a href="{{route('user-dashboard') }}">User Dashboard </a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="{{route('order-history')}}">Order History </a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="{{route('order-details')}}">Order Details </a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="{{route('account-setting')}}">Account Settings </a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="{{route('product-details')}}">Product Details</a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="{{route('wishlist')}}"> Wishlist</a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="{{route('shopping-cart')}}"> Shopping Cart</a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="{{route('sign-in')}}"> Sign in</a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="{{route('create-account')}}"> Create Account</a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="{{route('faq')}}"> faq</a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="{{route('404')}}"> Error 404</a>
                </li>
            </ul>
        </li>
        <li class="header__navigation-menu-link">
            <a href="#">
                Blog
                <span class="drop-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33332 5.66667L7.99999 10.3333L12.6667 5.66667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
            <ul class="header__navigation-drop-menu">
                <li class="header__navigation-drop-menu-link">
                    <a href="{{ route('bloglist')}}">Blog list</a>
                </li>
                <li class="header__navigation-drop-menu-link">
                    <a href="{{ route('singleblog')}}">Single Blog</a>
                </li>
            </ul>
        </li>
        <li class="header__navigation-menu-link">
            <a href="{{ route('aboutus') }}">About us </a>
        </li>
        <li class="header__navigation-menu-link">
            <a href="{{ route('contact') }}">Contact Us</a>
        </li>
    </ul>

    <a href="#" class="header__telephone-number">
        <span>
            <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M14.4359 2.375C15.9193 2.77396 17.2718 3.55567 18.358 4.64184C19.4441 5.72801 20.2258 7.08051 20.6248 8.56388" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path
                    d="M13.5306 5.75687C14.4205 5.99625 15.2318 6.46521 15.8833 7.11678C16.5349 7.76835 17.0039 8.57967 17.2433 9.46949"
                    stroke="white"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                <path
                    d="M7.115 11.6517C8.02238 13.5074 9.5263 15.0049 11.3859 15.9042C11.522 15.9688 11.6727 15.9966 11.8229 15.9851C11.9731 15.9736 12.1178 15.9231 12.2425 15.8386L14.9812 14.0134C15.1022 13.9326 15.2414 13.8833 15.3862 13.8698C15.5311 13.8564 15.677 13.8793 15.8107 13.9364L20.9339 16.1326C21.1079 16.2065 21.2532 16.335 21.3479 16.4987C21.4426 16.6623 21.4815 16.8523 21.4589 17.04C21.2967 18.307 20.6784 19.4714 19.7196 20.3154C18.7608 21.1593 17.5273 21.6249 16.25 21.625C12.3049 21.625 8.52139 20.0578 5.73179 17.2682C2.94218 14.4786 1.375 10.6951 1.375 6.75C1.37512 5.47279 1.84074 4.23941 2.68471 3.28077C3.52867 2.32213 4.6931 1.70396 5.96 1.542C6.14771 1.51936 6.33769 1.55832 6.50134 1.653C6.66499 1.74769 6.79345 1.89298 6.86738 2.067L9.06537 7.1945C9.1219 7.32698 9.14485 7.47137 9.13218 7.61485C9.11951 7.75833 9.07162 7.89647 8.99275 8.017L7.17275 10.7977C7.09015 10.923 7.04141 11.0675 7.03129 11.2171C7.02117 11.3668 7.05001 11.5165 7.115 11.6517V11.6517Z"
                    stroke="white"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>

            (219) 555-0114
        </span>
    </a>
</div>
</div>
</div>
<div class="header__sidebar">
<button class="header__cross">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    <path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
</svg>
</button>
<div class="header__mobile-sidebar">
<div class="header__mobile-top">
    <form action="#">
        <div class="header__mobile-input">
            <input type="text" placeholder="Search" />
            <button class="search-btn">
                <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9.16667 16.3333C12.8486 16.3333 15.8333 13.3486 15.8333 9.66667C15.8333 5.98477 12.8486 3 9.16667 3C5.48477 3 2.5 5.98477 2.5 9.66667C2.5 13.3486 5.48477 16.3333 9.16667 16.3333Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path d="M17.4999 18L13.8749 14.375" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
    </form>
    <ul class="header__mobile-menu">
        <li class="header__mobile-menu-item active">
            <a href="#" class="header__mobile-menu-item-link">
                Home
                <span class="drop-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33332 5.66667L7.99999 10.3333L12.6667 5.66667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
            <ul class="header__mobile-dropdown-menu">
                <li class="header__mobile-dropdown-menu-link active">
                    <a href="index.html">Home 01</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="home-02.html">Home 02</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="home-03.html">Home 03</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="home-04.html">Home 04</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="home-05.html">Home 05</a>
                </li>
            </ul>
        </li>
        <li class="header__mobile-menu-item">
            <a href="#" class="header__mobile-menu-item-link">
                Shop
                <span class="drop-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33332 5.66667L7.99999 10.3333L12.6667 5.66667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
            <ul class="header__mobile-dropdown-menu">
                <li class="header__mobile-dropdown-menu-link">
                    <a href="shop-01.html">Shop 01</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="shop-02.html">Shop 02</a>
                </li>
            </ul>
        </li>
        <li class="header__mobile-menu-item">
            <a href="#" class="header__mobile-menu-item-link">
                Pages
                <span class="drop-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33332 5.66667L7.99999 10.3333L12.6667 5.66667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
            <ul class="header__mobile-dropdown-menu">
                <li class="header__mobile-dropdown-menu-link">
                    <a href="product-details.html">Product Details</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="wishlist.html">Wishlist</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="shopping-cart.html">Shopping Cart</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="checkout.html">Checkout</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="sign-in.html">Sign in</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="create-account.html">Create Account</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="user-dashboard.html">User Dashboard</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="order-history.html">order history</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="order-details.html">Order Details</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="account-setting.html">Account Settings</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="faq.html">faq</a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="404.html">404</a>
                </li>
            </ul>
        </li>
        <li class="header__mobile-menu-item">
            <a href="#" class="header__mobile-menu-item-link">
                Blog
                <span class="drop-icon">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33332 5.66667L7.99999 10.3333L12.6667 5.66667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
            <ul class="header__mobile-dropdown-menu">
                <li class="header__mobile-dropdown-menu-link">
                    <a href="single-blog.html">Single Blog </a>
                </li>
                <li class="header__mobile-dropdown-menu-link">
                    <a href="blog-list.html">Blog list</a>
                </li>
            </ul>
        </li>
        <li class="header__mobile-menu-item">
            <a href="about.html" class="header__mobile-menu-item-link">About</a>
        </li>
        <li class="header__mobile-menu-item">
            <a href="contact.html" class="header__mobile-menu-item-link">Contacts</a>
        </li>
    </ul>
</div>
<div class="header__mobile-bottom">
    <div class="header__mobile-user">
        <div class="header__mobile-user--img">
            <img src="{{ asset('images/user/img-03.png') }}" alt="user">
        </div>
        <div class="header__mobile-user--info">
            <h2 class="font-body--lg-500">Dianne Russell</h2>
            <p class="font-body--md-400">dianne.russell@gmail.com</p>
        </div>
    </div>
    <div class="header__mobile-action d-none">
        <a href="#" class="button button--md">Sign in</a>
        <a href="#" class="button button--md button--disable">Sign up</a>
    </div>
</div>
</div>
</div>
</header>