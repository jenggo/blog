<?php
	$f3 = require_once('./app/base.php');
	$f3->config('config.ini');

	$main = new main();
	// blog dan admin extends main jadi harus main duluan!
	$blog = new blog();
	$admin = new blog_admin();

	// Lihat konten
	$f3->route('GET /@slug', 'blog->artikel');

	// Post komentar
	$f3->route('POST /@slug', 'blog->simpanKomentar');

	// Halaman utama
	$f3->route(array(
    	'GET /',
    	'GET /halaman/@page'
    ), 'blog->utama');

    // Pencarian
    $f3->route('POST /pencarian', 'blog->pencarian');

	// List Kategori
	$f3->route('GET /kategori/@slug', 'blog->kategori');




	/* Bagian admin : */
	$admin_url = $f3->get('hal_admin');

	// Utama
	$f3->route('GET /' . $admin_url, 'blog_admin->utama');

	// Post form login
	$f3->route('POST /' . $admin_url, 'blog_admin->login');

	// Logout
	$f3->route('GET /' . $admin_url . '/logout', 'blog_admin->logout');

	// Utama (versi ajax)
	$f3->route(array(
		'GET /' . $admin_url . ' [ajax]',
		'GET /' . $admin_url . '/halaman/@page [ajax]'
	), 'blog_admin->utamaAJAX');

	// Artikel & Kategori (bikin baru / pengeditan)
	$f3->route(array(
		'GET /' . $admin_url . '/buat/@tipe [ajax]',
		'GET /' . $admin_url . '/edit/@tipe/@id [ajax]'
	), 'blog_admin->artikel');

	// Preview tampilan artikel sebelum diposting..
	$f3->route('POST /'. $admin_url . '/preview/artikel [ajax]', 'blog_admin->preview');

	// Proses penyimpanan data (baru / editan)
	$f3->route(array(
		'POST /' . $admin_url . '/buat/@tipe [ajax]',
		'POST /' . $admin_url . '/edit/@tipe [ajax]'
	), 'blog_admin->simpanData');

	// Proses penghapusan data
	$f3->route('GET /' . $admin_url . '/hapus/@tipe/@id [ajax]', 'blog_admin->hapusData');

	// Komentar
	$f3->route(array(
		'GET /' . $admin_url . '/komentar [ajax]',
		'GET /' . $admin_url . '/komentar/halaman/@page [ajax]'
	), 'blog_admin->komentar');

	// Users
	$f3->route('GET /' . $admin_url . '/user [ajax]', 'blog_admin->user');
	$f3->route('GET /' . $admin_url . '/user/tabel [ajax]', 'blog_admin->userTabel');
	$f3->route(array(
		'GET /' . $admin_url . '/user/edit/@id [ajax]',
		'GET /' . $admin_url . '/user/edit/@id'
	), 'blog_admin->userEdit');

	// Settings
	$f3->route('GET /' . $admin_url . '/setting [ajax]', 'blog_admin->setting');

	// Pencarian
	$f3->route('POST /' . $admin_url . ' [ajax]', 'blog_admin->pencarian');

	$f3->run();
?>