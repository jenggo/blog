<?php
	class blog extends main {

		// Tampilkan halaman utama + pagination
		public function utama() {
			$this->f3->set('title', $this->title_blog);
			$option_utama = array('order' => 'id DESC');

			$this->templatePagination(
				false,
				3,
				'artikel',
				'kategori',
				"aktif = '1'",
				$option_utama,
				null,
				false
			);
			$this->loadTemplate('main.html');
		}

		// Cari mencari
		public function pencarian() {
			$cari = $this->f3->get('POST.cari');

			$artikel = $this->tabelDB('artikel');
			// $list_artikel = $artikel->find(array('judul LIKE ? OR isi LIKE ?', '"%'.$cari.'%"', '"%'.$cari.'%"'));
			$list_artikel = $artikel->find('judul LIKE "%'.$cari.'%" OR isi LIKE "%'.$cari.'%"');
			$this->f3->set('title', 'Hasil pencarian untuk : ' . $cari . ' | ' . $this->title_blog);

			if (!$artikel->dry())
				$this->f3->set('error', $cari);
			else {
				$this->f3->set('list_artikel', $list_artikel);
				$this->f3->set('cari', $cari);
				$this->f3->set('jumlah', count($list_artikel));
			}

			$this->loadTemplate('search_result.html');
		}

		// Tampilkan artikel
		public function artikel() {
			$slug = $this->f3->get('PARAMS.slug');

			$artikel = $this->tabelDB('artikel');
			$artikel->load(array('slug=?', $slug)); // Ambil artikel berdasarkan slug-nya

			// Cek apakah artikel tersebut ada ato ada yang iseng
			if ($artikel->dry()) {
				$this->f3->set('title', 'Tidak ada artikel! | ' . $this->title_blog);
				$this->f3->set('error', '404 not found!');
			}
			else {
				$this->f3->set('title', $artikel->judul . ' | ' . $this->title_blog);
				$artikel->copyTO('artikel'); // Salin ke token 'artikel'

				// Ambil kategori sesuai artikel
				$kategori = $this->tabelDB('kategori');
				$kategori->load(array('id=?', $artikel->id_kategori));
				$kategori->copyTO('kategori'); // Salin ke token 'kategori'

				// Ambil komentar
				$komentar = $this->tabelDB('komentar');
				$list_komentar = $komentar->find(array('id_artikel=?', $artikel->id));
				$this->f3->set('list_komentar', $list_komentar);
				$this->f3->set('jumlah_komentar', $komentar->count(array('id_artikel=?', $artikel->id)));

				// Ambil user
				$users = $this->tabelDB('user');
				$this->f3->set('list_user', $list_user = $users->find());

				// Cek apakah database masih ada tetangga 'kiri-kanan'
				if (!$artikel->dry()) {
					$id = $artikel->id;

					$artikel->load("id < '$id' ORDER BY id DESC LIMIT 1");
					if (!$artikel->dry()) $artikel->copyTO('SEBELUM');

					$artikel->load("id > '$id' ORDER BY id ASC LIMIT 1");
					if (!$artikel->dry()) $artikel->copyTO('SESUDAH');
				}
			}

			$this->loadTemplate('post.html');
		}

		public function simpanKomentar() {
			$artikel_id = $this->f3->get('POST.id_artikel');
			$nama = $this->f3->get('POST.nama');
			$email = $this->f3->get('POST.email');
			$user_id = $this->f3->get('SESSION.id');

			$komentar = $this->tabelDB('komentar');
			$komentar->copyFROM('POST');
			if (!empty($user_id)) $komentar->id_user = $user_id;
			$komentar->tanggal = date('Y-m-d');
			$komentar->aktif = '1';
			$komentar->kontak = '0';
			$komentar->id_artikel = $artikel_id;
			$komentar->save();

			$this->artikel();
		}

		// Tampilkan artikel2 sesuai kategori
		public function kategori() {
			$slug = $this->f3->get('PARAMS.slug');

			$kategori = $this->tabelDB('kategori');
			$kategori->load(array('slug=?', $slug)); // Ambil sesuai slug

			// Cek apakah benar ada ato iseng
			if ($kategori->dry()) {
				$this->f3->set('title', 'Tidak ada kategori! | ' . $this->title_blog);
				$this->f3->set('error', '404 not found!');
			}
			else {
				$kategori->copyTO('kategori'); // Salin ke token 'kategori'
				$this->f3->set('title', 'Kategori : ' . $kategori->nama . ' | ' . $this->title_blog);

				// Ambil artikel berdasarkan kategori
				$artikel = $this->tabelDB('artikel');
				$artikel->load(array('id_kategori=?', $kategori->id));

				if ($artikel->dry()) {
					$this->f3->set('error', 'Belum ada artikel di kategori ' . $kategori->nama);
				}
				else {
					$list_artikel = $artikel->find(array('id_kategori=?', $kategori->id));
					$this->f3->set('list_artikel', $list_artikel);
				}
			}

			$this->loadTemplate('list_category.html');
		}
	}
?>